<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Unit;
use App\Models\UtilityReading;
use App\Services\AuditService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Generate a globally unique invoice reference per account.
     * Format: INV-{accountId}-{microsecond timestamp}
     * e.g. INV-7-20260618143052123456
     * No locking or transactions needed — timestamp makes it unique.
     */
    private function nextReference(int $accountId): string
    {
        return 'INV-' . $accountId . '-' . now()->format('YmdHisu');
    }

    // ── Index / Create ────────────────────────────────────────────────────

    public function index()
    {
        $unitIds  = $this->filteredUnitIds();
        $leaseIds = Lease::whereIn('unit_id', $unitIds)->pluck('id')->toArray();

        $invoices = Invoice::with(['lease.tenant', 'lease.unit.property'])
            ->whereIn('lease_id', $leaseIds)
            ->latest()
            ->get();

        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $unitIds  = $this->filteredUnitIds();
        $leaseIds = Lease::whereIn('unit_id', $unitIds)
            ->where('status', 'active')
            ->pluck('id')->toArray();

        $leases = Lease::with(['tenant', 'unit.property', 'invoices'])
            ->whereIn('id', $leaseIds)
            ->get();

        return view('invoices.create', compact('leases'));
    }

    // ── Store (manual) ────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lease_id'       => ['required', 'exists:leases,id'],
            'invoice_date'   => ['required', 'date'],
            'due_date'       => ['required', 'date', 'after_or_equal:invoice_date'],
            'period_month'   => ['required', 'integer', 'min:1', 'max:12'],
            'period_year'    => ['required', 'integer', 'min:2020'],
            'descriptions'   => ['required', 'array', 'min:1'],
            'descriptions.*' => ['required', 'string'],
            'amounts'        => ['required', 'array', 'min:1'],
            'amounts.*'      => ['required', 'numeric', 'min:0'],
            'types'          => ['required', 'array', 'min:1'],
            'types.*'        => ['required', 'string'],
        ]);

        // Duplicate period check — one invoice per tenant per month
        $exists = Invoice::where('lease_id', $validated['lease_id'])
            ->where('period_month', $validated['period_month'])
            ->where('period_year', $validated['period_year'])
            ->first();

        if ($exists) {
            $monthName = \Carbon\Carbon::createFromDate(
                $validated['period_year'], $validated['period_month'], 1
            )->format('F Y');

            return back()->withInput()->withErrors([
                'period_month' => 'An invoice for ' . $monthName .
                    ' already exists for this tenant. Reference: ' . $exists->reference,
            ]);
        }

        $accountId   = auth()->user()->account_id;
        $totalAmount = array_sum($validated['amounts']);
        $reference   = $this->nextReference($accountId);

        $invoice = Invoice::create([
            'account_id'   => $accountId,
            'lease_id'     => $validated['lease_id'],
            'reference'    => $reference,
            'period_month' => $validated['period_month'],
            'period_year'  => $validated['period_year'],
            'invoice_date' => $validated['invoice_date'],
            'due_date'     => $validated['due_date'],
            'total_amount' => $totalAmount,
            'status'       => 'draft',
        ]);

        foreach ($validated['descriptions'] as $index => $description) {
            InvoiceLineItem::create([
                'invoice_id'  => $invoice->id,
                'description' => $description,
                'quantity'    => 1,
                'unit_price'  => $validated['amounts'][$index],
                'amount'      => $validated['amounts'][$index],
                'type'        => $validated['types'][$index],
            ]);
        }

        try {
            AuditService::log(
                'invoice.created',
                'Invoice ' . $invoice->reference . ' created (draft)',
                $invoice,
                ['amount' => $totalAmount, 'period' => $validated['period_month'] . '/' . $validated['period_year']]
            );
        } catch (\Exception $e) {
            \Log::warning('Audit log failed: ' . $e->getMessage());
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice ' . $invoice->reference . ' created as draft. Send it when ready.');
    }

    // ── Send invoice to tenant ────────────────────────────────────────────

    public function send(Invoice $invoice)
    {
        $result = $this->attemptSend($invoice);

        if (!$result['success']) {
            return back()->with('error', $result['reason']);
        }

        return back()->with('success', 'Invoice sent to ' . $result['tenant_name'] . ' via SMS.');
    }

    /**
     * Send every not-yet-sent invoice (drafts) for this account to their
     * tenants. System-generated invoices are already sent automatically at
     * creation time (see SendMonthlyInvoices) — this is for manually
     * created / bulk-generated invoices still sitting as drafts.
     */
    public function sendAll()
    {
        $unitIds  = $this->filteredUnitIds();
        $leaseIds = Lease::whereIn('unit_id', $unitIds)->pluck('id')->toArray();

        $invoices = Invoice::with(['lease.tenant', 'lease.unit.property'])
            ->whereIn('lease_id', $leaseIds)
            ->where('status', 'draft')
            ->get();

        if ($invoices->isEmpty()) {
            return back()->with('error', 'No draft invoices to send — everything has already been sent.');
        }

        $sent   = 0;
        $failed = [];

        foreach ($invoices as $invoice) {
            $result = $this->attemptSend($invoice);

            if ($result['success']) {
                $sent++;
            } else {
                $failed[] = $result['tenant_name'] . ' (' . $result['reason'] . ')';
            }
        }

        try {
            AuditService::log(
                'invoice.bulk_sent',
                $sent . ' ' . Str::plural('invoice', $sent) . ' sent to tenants via SMS',
                null,
                ['sent' => $sent, 'failed' => count($failed)]
            );
        } catch (\Exception $e) {
            \Log::warning('Audit log failed: ' . $e->getMessage());
        }

        $message = $sent . ' ' . Str::plural('invoice', $sent) . ' sent successfully.';
        if (!empty($failed)) {
            $message .= ' ' . count($failed) . ' could not be sent: ' . implode(', ', $failed) . '.';
            return back()->with('error', $message);
        }

        return back()->with('success', $message);
    }

    /**
     * Shared send logic used by both the single "Send" action and "Send all".
     * Never throws — always returns a structured result so bulk sending can
     * keep going through failures.
     */
    private function attemptSend(Invoice $invoice): array
    {
        $invoice->loadMissing(['lease.tenant', 'lease.unit.property']);

        $tenant  = $invoice->lease->tenant;
        $account = auth()->user()->account;

        if (!$tenant->phone) {
            return ['success' => false, 'tenant_name' => $tenant->full_name, 'reason' => 'no phone number on file'];
        }

        $sms = new SmsService($account);

        if (!$sms->hasCredits()) {
            return ['success' => false, 'tenant_name' => $tenant->full_name, 'reason' => 'no SMS credits remaining'];
        }

        $pdfUrl = URL::signedRoute(
            'invoices.pdf.public',
            ['invoice' => $invoice->id],
            now()->addDays(30)
        );

        $period  = \Carbon\Carbon::createFromDate($invoice->period_year, $invoice->period_month, 1)->format('F Y');
        $message = 'Dear ' . $tenant->first_name . ', your invoice ' . $invoice->reference
            . ' for ' . $period
            . ' is ready. Amount due: KES ' . number_format($invoice->total_amount)
            . '. Due: ' . $invoice->due_date->format('d M Y')
            . '. Download: ' . $pdfUrl;

        $result = $sms->send($tenant->phone, $message, $tenant->id);

        if (!$result['success']) {
            return ['success' => false, 'tenant_name' => $tenant->full_name, 'reason' => 'SMS failed to send'];
        }

        $invoice->update([
            'status'  => 'sent',
            'sent_at' => now(),
        ]);

        AuditService::log(
            'invoice.sent',
            'Invoice ' . $invoice->reference . ' sent to ' . $tenant->full_name . ' via SMS',
            $invoice,
            ['phone' => $tenant->phone, 'period' => $period]
        );

        return ['success' => true, 'tenant_name' => $tenant->full_name, 'reason' => null];
    }

    // ── Show / Destroy ────────────────────────────────────────────────────

    public function show(Invoice $invoice)
    {
        $invoice->load([
            'lease.tenant',
            'lease.unit.property',
            'lineItems',
            'allocations.payment',
        ]);

        return view('invoices.show', compact('invoice'));
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->allocations()->count() > 0) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'This invoice cannot be deleted because payments have been recorded against it.');
        }

        $reference  = $invoice->reference;
        $tenantName = $invoice->lease->tenant->full_name ?? 'Unknown';

        $invoice->lineItems()->delete();
        $invoice->delete();

        AuditService::log(
            'invoice.deleted',
            'Invoice ' . $reference . ' deleted (was for ' . $tenantName . ')',
            null,
            ['reference' => $reference]
        );

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice ' . $reference . ' deleted.');
    }

    // ── Bulk ──────────────────────────────────────────────────────────────

    public function bulkCreate()
    {
        session()->forget('bulk_previews');
        return view('invoices.bulk');
    }

    public function bulkPreview(Request $request)
    {
        $request->validate([
            'period_month' => ['required', 'integer', 'min:1', 'max:12'],
            'period_year'  => ['required', 'integer'],
            'invoice_date' => ['required', 'date'],
            'due_date'     => ['required', 'date'],
        ]);

        $month       = (int) $request->period_month;
        $year        = (int) $request->period_year;
        $monthName   = \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y');
        $propertyIds = $this->filteredPropertyIds();

        $properties = Property::whereIn('id', $propertyIds)
            ->with([
                'units.activeLease.tenant',
                'utilityRates' => fn($q) => $q->where('active', true),
            ])->get();

        $allLeaseIds = $properties->flatMap(fn($p) =>
            $p->units->filter(fn($u) => $u->activeLease)->map(fn($u) => $u->activeLease->id)
        );

        $allUnitIds = $properties->flatMap(fn($p) =>
            $p->units->filter(fn($u) => $u->activeLease)->map(fn($u) => $u->id)
        );

        $existingInvoices = Invoice::whereIn('lease_id', $allLeaseIds)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->get()
            ->keyBy('lease_id');

        $allReadings = UtilityReading::whereIn('unit_id', $allUnitIds)
            ->where('reading_month', $month)
            ->where('reading_year', $year)
            ->get()
            ->groupBy('unit_id');

        $previews = [];

        foreach ($properties as $property) {
            $meterRates = $property->utilityRates->whereIn('billing_type', ['per_unit', 'per_meter_reading']);
            $flatRates  = $property->utilityRates->where('billing_type', 'flat_fee');

            foreach ($property->units as $unit) {
                if (!$unit->activeLease) continue;

                $lease    = $unit->activeLease;
                $tenant   = $lease->tenant;
                $existing = $existingInvoices->get($lease->id);
                $readings = $allReadings->get($unit->id, collect())->keyBy('utility_type');

                $lineItems = [];
                $warnings  = [];
                $total     = 0;

                $lineItems[] = ['description' => $monthName . ' rent', 'amount' => floatval($lease->monthly_rent), 'type' => 'rent'];
                $total += floatval($lease->monthly_rent);

                foreach ($meterRates as $rate) {
                    $reading = $readings->get($rate->type);
                    if ($reading) {
                        $lineItems[] = ['description' => $rate->name . ' charges', 'amount' => floatval($reading->charge_amount), 'type' => $rate->type];
                        $total += floatval($reading->charge_amount);
                    } else {
                        $warnings[] = $rate->name . ' reading not entered for ' . $monthName;
                    }
                }

                foreach ($flatRates as $rate) {
                    $lineItems[] = ['description' => $rate->name, 'amount' => floatval($rate->amount), 'type' => $rate->type];
                    $total += floatval($rate->amount);
                }

                $previews[] = [
                    'lease_id'         => $lease->id,
                    'tenant_name'      => $tenant->full_name,
                    'tenant_initials'  => strtoupper(substr($tenant->first_name, 0, 1) . substr($tenant->last_name, 0, 1)),
                    'unit_name'        => $unit->name,
                    'property_name'    => $property->name,
                    'line_items'       => $lineItems,
                    'warnings'         => $warnings,
                    'total'            => $total,
                    'existing_ref'     => $existing?->reference,
                    'existing_status'  => $existing?->status,
                    'already_invoiced' => $existing !== null,
                ];
            }
        }

        session([
            'bulk_previews'     => $previews,
            'bulk_month'        => $month,
            'bulk_year'         => $year,
            'bulk_month_name'   => $monthName,
            'bulk_invoice_date' => $request->invoice_date,
            'bulk_due_date'     => $request->due_date,
        ]);

        return redirect()->route('invoices.bulk.preview.show');
    }

    public function bulkPreviewShow()
    {
        if (!session('bulk_previews')) {
            return redirect()->route('invoices.bulk');
        }

        $previews    = session('bulk_previews');
        $month       = session('bulk_month');
        $year        = session('bulk_year');
        $monthName   = session('bulk_month_name');
        $invoiceDate = session('bulk_invoice_date');
        $dueDate     = session('bulk_due_date');

        return view('invoices.bulk', compact('previews', 'month', 'year', 'monthName', 'invoiceDate', 'dueDate'));
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'invoice_date' => ['required', 'date'],
            'due_date'     => ['required', 'date'],
            'period_month' => ['required', 'integer', 'min:1', 'max:12'],
            'period_year'  => ['required', 'integer'],
            'lease_ids'    => ['required', 'array', 'min:1'],
            'lease_ids.*'  => ['exists:leases,id'],
        ]);

        $month     = (int) $validated['period_month'];
        $year      = (int) $validated['period_year'];
        $monthName = \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y');
        $accountId = auth()->user()->account_id;
        $count     = 0;
        $skipped   = 0;

        foreach ($validated['lease_ids'] as $leaseId) {
            $lease = Lease::with('unit.property.utilityRates')->find($leaseId);
            if (!$lease) continue;

            $exists = Invoice::where('lease_id', $leaseId)
                ->where('period_month', $month)
                ->where('period_year', $year)
                ->exists();

            if ($exists) { $skipped++; continue; }

            $lineItemsJson = $request->input('line_items_' . $leaseId);
            $lineItems     = $lineItemsJson ? json_decode($lineItemsJson, true) : [];

            if (empty($lineItems)) {
                $lineItems = [['description' => $monthName . ' rent', 'amount' => floatval($lease->monthly_rent), 'type' => 'rent']];
            }

            $totalAmount = array_sum(array_column($lineItems, 'amount'));
            $reference   = $this->nextReference($accountId);

            $invoice = Invoice::create([
                'account_id'   => $accountId,
                'lease_id'     => $leaseId,
                'reference'    => $reference,
                'period_month' => $month,
                'period_year'  => $year,
                'invoice_date' => $validated['invoice_date'],
                'due_date'     => $validated['due_date'],
                'total_amount' => $totalAmount,
                'status'       => 'draft',
            ]);

            foreach ($lineItems as $item) {
                InvoiceLineItem::create([
                    'invoice_id'  => $invoice->id,
                    'description' => $item['description'],
                    'quantity'    => 1,
                    'unit_price'  => $item['amount'],
                    'amount'      => $item['amount'],
                    'type'        => $item['type'],
                ]);
            }

            $count++;
        }

        try {
            AuditService::log(
                'invoice.bulk_created',
                $count . ' invoices bulk generated for ' . $monthName,
                null,
                ['count' => $count, 'skipped' => $skipped, 'period' => $monthName]
            );
        } catch (\Exception $e) {
            \Log::warning('Audit log failed: ' . $e->getMessage());
        }

        $message = $count . ' ' . Str::plural('invoice', $count) . ' created as drafts.';
        if ($skipped > 0) {
            $message .= ' ' . $skipped . ' skipped (already invoiced).';
        }

        return redirect()->route('invoices.index')->with('success', $message);
    }

    // ── PDF ───────────────────────────────────────────────────────────────

    public function pdf(Invoice $invoice)
    {
        $invoice->load(['lease.tenant', 'lease.unit.property', 'lineItems', 'allocations.payment']);

        $amountPaid = $invoice->allocations->sum('amount');
        $amountDue  = $invoice->total_amount - $amountPaid;
        $account    = auth()->user()->account;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.pdf', compact('invoice', 'amountPaid', 'amountDue', 'account'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('Invoice-' . $invoice->reference . '.pdf');
    }

    public function publicPdf(Invoice $invoice)
    {
        $invoice->load(['lease.tenant', 'lease.unit.property', 'lineItems', 'allocations.payment']);

        $amountPaid = $invoice->allocations->sum('amount');
        $amountDue  = $invoice->total_amount - $amountPaid;
        $account    = $invoice->lease->unit->property->account
            ?? \App\Models\Account::find(\App\Models\User::where('account_id', '!=', null)->value('account_id'));

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.pdf', compact('invoice', 'amountPaid', 'amountDue', 'account'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('Invoice-' . $invoice->reference . '.pdf');
    }
}