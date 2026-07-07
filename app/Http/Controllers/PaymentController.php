<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Tenant;
use App\Models\Unit;
use App\Services\AuditService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index()
    {
        $unitIds  = $this->filteredUnitIds();
        $leaseIds = Lease::whereIn('unit_id', $unitIds)->pluck('id')->toArray();

        $payments = Payment::with(['tenant', 'lease.unit.property'])
            ->whereIn('lease_id', $leaseIds)
            ->latest()
            ->get();

        $unallocated = $payments->where('is_allocated', false)
            ->where('payment_type', '!=', 'deposit');

        // Unmatched M-Pesa C2B payments — no tenant/lease, recorded by auto-reconciliation
        $unmatched = Payment::with(['lease.unit.property'])
            ->where('account_id', auth()->user()->account_id)
            ->whereNull('tenant_id')
            ->whereNull('lease_id')
            ->where('method', 'mpesa')
            ->where('is_allocated', false)
            ->latest()
            ->get();

        // Active tenants for the assign dropdown
        $tenants = Tenant::with('activeLease.unit.property')
            ->whereHas('activeLease', fn($q) => $q->whereIn('unit_id', $unitIds))
            ->get();

        return view('payments.index', compact('payments', 'unallocated', 'unmatched', 'tenants'));
    }

    public function create()
    {
        $unitIds = $this->filteredUnitIds();

        $tenants = Tenant::with([
                'activeLease.unit.property',
                'activeLease.invoices' => function ($q) {
                    $q->whereIn('status', ['sent', 'partial', 'overdue']);
                },
            ])
            ->whereHas('activeLease', fn($q) => $q->whereIn('unit_id', $unitIds))
            ->get();

        return view('payments.create', compact('tenants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id'    => ['required', 'exists:tenants,id'],
            'payment_type' => ['required', 'in:rent,deposit,other'],
            'amount'       => ['required', 'numeric', 'min:1'],
            'payment_date' => ['required', 'date'],
            'method'       => ['required', 'in:mpesa,cash,bank,cheque'],
            'reference'    => ['nullable', 'string', 'max:100'],
            'notes'        => ['nullable', 'string'],
        ]);

        $tenant      = Tenant::with('activeLease.unit.property')->find($validated['tenant_id']);
        $activeLease = $tenant->activeLease;
        $isDeposit   = $validated['payment_type'] === 'deposit';

        $fullyPaidInvoices = collect();
        $newBalance        = 0;

        $payment = DB::transaction(function () use (
            $validated, $tenant, $activeLease, $isDeposit,
            &$fullyPaidInvoices, &$newBalance
        ) {
            $payment = Payment::create([
                'account_id'   => auth()->user()->account_id,
                'tenant_id'    => $validated['tenant_id'],
                'lease_id'     => $activeLease?->id,
                'amount'       => $validated['amount'],
                'payment_type' => $validated['payment_type'],
                'payment_date' => $validated['payment_date'],
                'method'       => $validated['method'],
                'reference'    => $validated['reference'] ?? null,
                'notes'        => $validated['notes'] ?? null,
                'is_allocated' => $isDeposit,
            ]);

            if (!$isDeposit && $activeLease) {
                $outstanding = $activeLease->invoices()
                    ->whereIn('status', ['sent', 'partial', 'overdue'])
                    ->orderBy('invoice_date')
                    ->get();

                $remaining = floatval($validated['amount']);

                foreach ($outstanding as $invoice) {
                    if ($remaining <= 0) break;

                    $invoiceBalance = floatval($invoice->total_amount)
                                   - floatval($invoice->amount_paid);

                    if ($invoiceBalance <= 0) continue;

                    $allocate = min($remaining, $invoiceBalance);

                    PaymentAllocation::create([
                        'payment_id' => $payment->id,
                        'invoice_id' => $invoice->id,
                        'amount'     => $allocate,
                    ]);

                    $newAmountPaid = floatval($invoice->amount_paid) + $allocate;
                    $newStatus     = $newAmountPaid >= floatval($invoice->total_amount)
                        ? 'paid'
                        : 'partial';

                    $invoice->update([
                        'amount_paid' => $newAmountPaid,
                        'status'      => $newStatus,
                    ]);

                    if ($newStatus === 'paid') {
                        $fullyPaidInvoices->push($invoice->fresh());
                    }

                    $remaining -= $allocate;
                }

                $payment->update(['is_allocated' => true]);

                $activeLease->load(['invoices', 'payments']);
                $newBalance = floatval($activeLease->invoices->sum('total_amount'))
                            - floatval($activeLease->payments->where('payment_type', '!=', 'deposit')->sum('amount'));
            }

            return $payment;
        });

        AuditService::log(
            'payment.recorded',
            ucfirst($validated['payment_type']) . ' payment of ' . currency($validated['amount']) . ' recorded for ' . $tenant->full_name,
            $payment,
            [
                'amount'       => $validated['amount'],
                'payment_type' => $validated['payment_type'],
                'method'       => $validated['method'],
                'reference'    => $validated['reference'] ?? null,
                'balance'      => $newBalance,
            ]
        );

        if (!$isDeposit) {
            $this->sendPaymentConfirmationSms(
                $tenant, $payment, $fullyPaidInvoices, $newBalance,
                auth()->user()->account
            );
        } else {
            $this->sendDepositConfirmationSms($tenant, $payment, auth()->user()->account);
        }

        $label = $isDeposit ? 'Deposit' : 'Payment';

        return redirect()->route('payments.index')
            ->with('success', $label . ' of ' . currency($validated['amount']) . ' recorded successfully.');
    }

    // ── Assign an unmatched M-Pesa payment to a tenant ────────────────────
    public function assign(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
        ]);

        if ($payment->account_id !== auth()->user()->account_id) {
            abort(403);
        }

        $tenant      = Tenant::with('activeLease.unit.property')->find($validated['tenant_id']);
        $activeLease = $tenant->activeLease;

        $fullyPaidInvoices = collect();
        $newBalance        = 0;

        DB::transaction(function () use ($payment, $tenant, $activeLease, &$fullyPaidInvoices, &$newBalance) {
            $payment->update([
                'tenant_id'    => $tenant->id,
                'lease_id'     => $activeLease?->id,
                'payment_type' => 'rent',
                'notes'        => ($payment->notes ?? '') . ' [Manually assigned to ' . $tenant->full_name . ' on ' . now()->format('d M Y') . ']',
            ]);

            if ($activeLease) {
                $outstanding = $activeLease->invoices()
                    ->whereIn('status', ['sent', 'partial', 'overdue'])
                    ->orderBy('invoice_date')
                    ->get();

                $remaining = floatval($payment->amount);

                foreach ($outstanding as $invoice) {
                    if ($remaining <= 0) break;

                    $invoiceBalance = floatval($invoice->total_amount) - floatval($invoice->amount_paid);
                    if ($invoiceBalance <= 0) continue;

                    $allocate = min($remaining, $invoiceBalance);

                    PaymentAllocation::create([
                        'payment_id' => $payment->id,
                        'invoice_id' => $invoice->id,
                        'amount'     => $allocate,
                    ]);

                    $newAmountPaid = floatval($invoice->amount_paid) + $allocate;
                    $newStatus     = $newAmountPaid >= floatval($invoice->total_amount) ? 'paid' : 'partial';

                    $invoice->update([
                        'amount_paid' => $newAmountPaid,
                        'status'      => $newStatus,
                    ]);

                    if ($newStatus === 'paid') {
                        $fullyPaidInvoices->push($invoice->fresh());
                    }

                    $remaining -= $allocate;
                }

                $payment->update(['is_allocated' => true]);

                $activeLease->load(['invoices', 'payments']);
                $newBalance = floatval($activeLease->invoices->sum('total_amount'))
                            - floatval($activeLease->payments->where('payment_type', '!=', 'deposit')->sum('amount'));
            }
        });

        AuditService::log(
            'payment.assigned',
            'Unmatched M-Pesa payment of ' . currency($payment->amount) . ' manually assigned to ' . $tenant->full_name,
            $payment,
            ['tenant_id' => $tenant->id, 'amount' => $payment->amount, 'reference' => $payment->reference]
        );

        $this->sendPaymentConfirmationSms(
            $tenant, $payment, $fullyPaidInvoices, $newBalance,
            auth()->user()->account
        );

        return redirect()->route('payments.index')
            ->with('success', 'Payment of ' . currency($payment->amount) . ' assigned to ' . $tenant->full_name . ' and allocated.');
    }

    private function sendDepositConfirmationSms(Tenant $tenant, Payment $payment, $account): void
    {
        if (!$tenant->phone) return;

        $sms = new SmsService($account);
        if (!$sms->hasCredits()) return;

        $activeLease = $tenant->activeLease;
        $unit        = $activeLease?->unit;
        $property    = $unit?->property;

        $message =
            'DEPOSIT RECEIVED - ' . strtoupper($property?->name ?? '') . "\n" .
            'Tenant: ' . $tenant->full_name . "\n" .
            'Unit: ' . ($unit?->name ?? '') . "\n" .
            'Amount: KES ' . number_format($payment->amount) . ' (' . strtoupper($payment->method) . ')' . "\n" .
            ($payment->reference ? 'Ref: ' . $payment->reference . "\n" : '') .
            'Date: ' . \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') . "\n" .
            'Your deposit has been received and is held securely.' . "\n" .
            'Powered by Nyumba.';

        $sms->send($tenant->phone, $message, $tenant->id);
    }

    private function sendPaymentConfirmationSms(
        Tenant $tenant,
        Payment $payment,
        $fullyPaidInvoices,
        float $newBalance,
        $account
    ): void {
        if (!$tenant->phone) return;

        $sms = new SmsService($account);
        if (!$sms->hasCredits()) return;

        $activeLease = $tenant->activeLease;
        $unit        = $activeLease?->unit;
        $property    = $unit?->property;
        $amount      = number_format($payment->amount);
        $method      = strtoupper($payment->method);
        $datePaid    = \Carbon\Carbon::parse($payment->payment_date)->format('d M Y');
        $unitName    = $unit?->name ?? '';
        $propName    = $property?->name ?? '';

        if ($fullyPaidInvoices->isNotEmpty()) {
            $invoice = $fullyPaidInvoices->first();
            $period  = \Carbon\Carbon::createFromDate(
                $invoice->period_year, $invoice->period_month, 1
            )->format('F Y');

            $balanceLine = $newBalance <= 0
                ? 'Balance: KES 0 - Fully paid.' . "\n" . 'Thank you for paying on time.'
                : 'Outstanding balance: KES ' . number_format($newBalance) . "\n" . 'Thank you.';

            $message =
                'PAYMENT RECEIVED - ' . strtoupper($propName) . "\n" .
                'Tenant: ' . $tenant->full_name . "\n" .
                'Unit: ' . $unitName . "\n" .
                'Period: ' . $period . "\n" .
                'Amount: KES ' . $amount . ' (' . $method . ')' . "\n" .
                ($payment->reference ? 'Ref: ' . $payment->reference . "\n" : '') .
                'Date: ' . $datePaid . "\n" .
                $balanceLine . "\n" .
                'Powered by Nyumba.';
        } else {
            $message =
                'PAYMENT RECEIVED - ' . strtoupper($propName) . "\n" .
                'Tenant: ' . $tenant->full_name . "\n" .
                'Unit: ' . $unitName . "\n" .
                'Amount: KES ' . $amount . ' (' . $method . ')' . "\n" .
                ($payment->reference ? 'Ref: ' . $payment->reference . "\n" : '') .
                'Date: ' . $datePaid . "\n" .
                'Remaining balance: KES ' . number_format(max(0, $newBalance)) . "\n" .
                'Powered by Nyumba.';
        }

        $sms->send($tenant->phone, $message, $tenant->id);
    }
}