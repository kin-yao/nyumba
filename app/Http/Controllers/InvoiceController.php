<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Unit;
use App\Models\UtilityReading;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
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
                    ' already exists for this tenant. Reference: ' . $exists->reference
            ]);
        }

        $totalAmount = array_sum($validated['amounts']);

        $invoice = Invoice::create([
            'account_id'   => auth()->user()->account_id,
            'lease_id'     => $validated['lease_id'],
            'reference'    => 'TEMP-' . uniqid(),
            'period_month' => $validated['period_month'],
            'period_year'  => $validated['period_year'],
            'invoice_date' => $validated['invoice_date'],
            'due_date'     => $validated['due_date'],
            'total_amount' => $totalAmount,
            'status'       => 'sent',
        ]);

        $invoice->update([
            'reference' => 'INV-' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT)
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

        $invoice->load('lease.tenant');

        AuditService::log(
            'invoice.created',
            'Invoice ' . $invoice->reference . ' created for ' . $invoice->lease->tenant->full_name,
            $invoice,
            ['amount' => $totalAmount, 'period' => $validated['period_month'] . '/' . $validated['period_year']]
        );

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice ' . $invoice->reference . ' created successfully.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load([
            'lease.tenant',
            'lease.unit.property',
            'lineItems',
            'allocations.payment'
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

        // ── Pre-load to eliminate N+1 inside the loop ─────────────────────────

        // Collect all active lease IDs and unit IDs up front
        $allLeaseIds = $properties->flatMap(fn($p) =>
            $p->units
                ->filter(fn($u) => $u->activeLease)
                ->map(fn($u) => $u->activeLease->id)
        );

        $allUnitIds = $properties->flatMap(fn($p) =>
            $p->units
                ->filter(fn($u) => $u->activeLease)
                ->map(fn($u) => $u->id)
        );

        // 1 query: all existing invoices for this period
        $existingInvoices = Invoice::whereIn('lease_id', $allLeaseIds)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->get()
            ->keyBy('lease_id');

        // 1 query: all utility readings for this period
        $allReadings = UtilityReading::whereIn('unit_id', $allUnitIds)
            ->where('reading_month', $month)
            ->where('reading_year', $year)
            ->get()
            ->groupBy('unit_id');

        // ─────────────────────────────────────────────────────────────────────

        $previews = [];

        foreach ($properties as $property) {
            $meterRates = $property->utilityRates->whereIn('billing_type', ['per_unit', 'per_meter_reading']);
            $flatRates  = $property->utilityRates->where('billing_type', 'flat_fee');

            foreach ($property->units as $unit) {
                if (!$unit->activeLease) continue;

                $lease  = $unit->activeLease;
                $tenant = $lease->tenant;

                // From pre-loaded collections — no extra queries
                $existingInvoice = $existingInvoices->get($lease->id);
                $readings        = $allReadings->get($unit->id, collect())->keyBy('utility_type');

                $lineItems = [];
                $warnings  = [];
                $total     = 0;

                $lineItems[] = [
                    'description' => $monthName . ' rent',
                    'amount'      => floatval($lease->monthly_rent),
                    'type'        => 'rent',
                ];
                $total += floatval($lease->monthly_rent);

                foreach ($meterRates as $rate) {
                    $reading = $readings->get($rate->type);
                    if ($reading) {
                        $lineItems[] = [
                            'description' => $rate->name . ' charges',
                            'amount'      => floatval($reading->charge_amount),
                            'type'        => $rate->type,
                        ];
                        $total += floatval($reading->charge_amount);
                    } else {
                        $warnings[] = $rate->name . ' reading not entered for ' . $monthName;
                    }
                }

                foreach ($flatRates as $rate) {
                    $lineItems[] = [
                        'description' => $rate->name,
                        'amount'      => floatval($rate->amount),
                        'type'        => $rate->type,
                    ];
                    $total += floatval($rate->amount);
                }

                $previews[] = [
                    'lease_id'         => $lease->id,
                    'tenant_name'      => $tenant->full_name,
                    'tenant_initials'  => strtoupper(
                        substr($tenant->first_name, 0, 1) .
                        substr($tenant->last_name,  0, 1)
                    ),
                    'unit_name'        => $unit->name,
                    'property_name'    => $property->name,
                    'line_items'       => $lineItems,
                    'warnings'         => $warnings,
                    'total'            => $total,
                    'existing_ref'     => $existingInvoice?->reference,
                    'existing_status'  => $existingInvoice?->status,
                    'already_invoiced' => $existingInvoice !== null,
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

            $invoice = Invoice::create([
                'account_id'   => auth()->user()->account_id,
                'lease_id'     => $leaseId,
                'reference'    => 'TEMP-' . uniqid(),
                'period_month' => $month,
                'period_year'  => $year,
                'invoice_date' => $validated['invoice_date'],
                'due_date'     => $validated['due_date'],
                'total_amount' => $totalAmount,
                'status'       => 'sent',
            ]);

            $invoice->update(['reference' => 'INV-' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT)]);

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

        AuditService::log(
            'invoice.bulk_created',
            $count . ' invoices bulk generated for ' . $monthName,
            null,
            ['count' => $count, 'skipped' => $skipped, 'period' => $monthName]
        );

        $message = $count . ' ' . Str::plural('invoice', $count) . ' generated successfully.';
        if ($skipped > 0) {
            $message .= ' ' . $skipped . ' skipped (already invoiced).';
        }

        return redirect()->route('invoices.index')->with('success', $message);
    }

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