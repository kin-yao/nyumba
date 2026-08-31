<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Services\AuditService;
use App\Services\MpesaService;
use App\Services\SmsService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MpesaC2BController extends Controller
{
    // ── Admin: save credentials + register C2B + Pull for a property ───────
    public function register(Request $request, \App\Models\Account $account, int $property, MpesaService $mpesa)
    {
        $property = Property::withoutGlobalScopes()->findOrFail($property);

        $validated = $request->validate([
            'mpesa_shortcode'        => ['required', 'string', 'max:20'],
            'mpesa_consumer_key'     => ['required', 'string', 'max:255'],
            'mpesa_consumer_secret'  => ['required', 'string', 'max:255'],
            'mpesa_nominated_number' => ['required', 'string', 'max:20'],
        ]);

        $property->update($validated);

        $confirmationUrl = url("/mpesa/c2b/{$property->id}/confirmation");
        $validationUrl   = url("/mpesa/c2b/{$property->id}/validation");
        $pullCallbackUrl = route('payments.pull.callback', $property->id);

        $errors = [];

        // C2B registration
        $c2b = $mpesa->registerC2B(
            shortcode: $validated['mpesa_shortcode'],
            consumerKey: $validated['mpesa_consumer_key'],
            consumerSecret: $validated['mpesa_consumer_secret'],
            confirmationUrl: $confirmationUrl,
            validationUrl: $validationUrl,
        );

        if ($c2b['success']) {
            $property->update(['mpesa_c2b_registered_at' => now()]);
        } else {
            $errors[] = 'C2B: ' . $c2b['error'];
        }

        // Pull registration
        $pull = $mpesa->registerPull(
            shortcode: $validated['mpesa_shortcode'],
            consumerKey: $validated['mpesa_consumer_key'],
            consumerSecret: $validated['mpesa_consumer_secret'],
            nominatedNumber: $validated['mpesa_nominated_number'],
            callbackUrl: $pullCallbackUrl,
        );

        if ($pull['success']) {
            $property->update(['mpesa_pull_registered_at' => now()]);
        } else {
            $errors[] = 'Pull: ' . $pull['error'];
        }

        try {
            AuditService::log(
                'property.mpesa_registered',
                'M-Pesa C2B/Pull registration attempted for "' . $property->name . '"'
                    . (empty($errors) ? ' — both succeeded' : ' — ' . implode('; ', $errors)),
                $property,
                ['shortcode' => $validated['mpesa_shortcode'], 'errors' => $errors]
            );
        } catch (\Exception $e) {
            Log::error('AuditService::log failed: ' . $e->getMessage());
        }

        if (!empty($errors)) {
            return back()->with('error', 'Some registrations failed: ' . implode(' | ', $errors));
        }

        return back()->with('success', 'M-Pesa C2B and Pull registered successfully for "' . $property->name . '".');
    }

    // ── Public: C2B validation ─────────────────────────────────────────────
    public function validation(Request $request, int $property)
    {
        $property = Property::withoutGlobalScopes()->findOrFail($property);

        Log::info('M-Pesa C2B validation', [
            'property_id' => $property->id,
            'payload'     => $request->all(),
        ]);

        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted',
        ]);
    }

    // ── Public: Pull API callback — Safaricom requires this URL to be
    // registered, but actual reconciliation goes through the scheduled
    // pullTransactions() query instead, not this callback. Log and
    // acknowledge only, since we don't have a confirmed payload shape
    // for this from Safaricom's docs. ────────────────────────────────────
    public function pullCallback(Request $request, int $property)
    {
        $property = Property::withoutGlobalScopes()->findOrFail($property);

        Log::info('M-Pesa Pull API callback', [
            'property_id' => $property->id,
            'payload'     => $request->all(),
        ]);

        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted',
        ]);
    }

    // ── Public: C2B confirmation — actual payment notification ─────────────
    public function confirmation(Request $request, int $property)
    {
        $property = Property::withoutGlobalScopes()->findOrFail($property);

        $payload = $request->all();

        Log::info('M-Pesa C2B confirmation', [
            'property_id' => $property->id,
            'payload'     => $payload,
        ]);

        $this->processTransaction($property, [
            'TransID'           => $payload['TransID'] ?? null,
            'TransAmount'       => $payload['TransAmount'] ?? null,
            'BillRefNumber'     => $payload['BillRefNumber'] ?? null,
            'MSISDN'            => $payload['MSISDN'] ?? null,
            'TransTime'         => $payload['TransTime'] ?? null,
            'BusinessShortCode' => $payload['BusinessShortCode'] ?? null,
        ]);

        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Accepted',
        ]);
    }

    /**
     * Shared transaction processor — used by both the live C2B confirmation
     * webhook and the Pull reconciliation command.
     *
     * @return string 'matched' | 'unmatched' | 'duplicate'
     */
    public function processTransaction(Property $property, array $txn, string $method = 'mpesa', string $providerLabel = 'M-Pesa', ?Unit $preMatchedUnit = null): string
    {
        $transId   = $txn['TransID'] ?? null;
        $rawAmount = $txn['TransAmount'] ?? null;
        $billRef   = trim((string) ($txn['BillRefNumber'] ?? ''));
        $msisdn    = (string) ($txn['MSISDN'] ?? '');
        $transTime = $txn['TransTime'] ?? null;

        // Numeric-ness is checked before Money::normalize() ever runs, so a
        // malformed amount degrades to the same graceful 'unmatched' path
        // as a missing one, instead of throwing out of a webhook handler.
        if (!$transId || $rawAmount === null || !is_numeric($rawAmount)) {
            Log::warning($providerLabel . ' C2B: incomplete or non-numeric transaction payload', [
                'property_id' => $property->id,
                'txn'         => $txn,
            ]);
            return 'unmatched';
        }

        $amount = Money::normalize($rawAmount);

        if (!Money::isPositive($amount)) {
            Log::warning($providerLabel . ' C2B: non-positive transaction amount', [
                'property_id' => $property->id,
                'txn'         => $txn,
            ]);
            return 'unmatched';
        }

        // Idempotency — don't double-record the same receipt
        if (Payment::withoutGlobalScopes()->where('reference', $transId)->exists()) {
            return 'duplicate';
        }

        if ($preMatchedUnit) {
            $unit = $preMatchedUnit;
        } else {
            $accountFormat = $property->account_format ?? 'unit_number';
            $unit          = $this->matchUnit($property, $accountFormat, $billRef, $msisdn);
        }

        $paymentDate = $transTime
            ? \Carbon\Carbon::createFromFormat('YmdHis', $transTime)->toDateString()
            : now()->toDateString();

        if (!$unit) {
            // Unmatched — record for manual assignment
            Payment::create([
                'account_id'   => $property->account_id,
                'tenant_id'    => null,
                'lease_id'     => null,
                'amount'       => $amount,
                'payment_type' => 'rent',
                'payment_date' => $paymentDate,
                'method'       => $method,
                'reference'    => $transId,
                'notes'        => 'Unmatched ' . $providerLabel . ' C2B payment. BillRef: "' . $billRef
                    . '", Phone: ' . $msisdn
                    . ', Property: ' . $property->name
                    . '. Needs manual assignment.',
                'is_allocated' => false,
            ]);

            try {
                AuditService::system(
                    $property->account_id,
                    'payment.' . $method . '_unmatched',
                    'Unmatched ' . $providerLabel . ' payment of ' . currency($amount) . ' (ref: ' . $transId . ') for "'
                        . $property->name . '" — needs manual assignment',
                    $property,
                    [
                        'trans_id' => $transId,
                        'bill_ref' => $billRef,
                        'msisdn'   => $msisdn,
                        'amount'   => $amount,
                    ]
                );
            } catch (\Exception $e) {
                Log::error('AuditService::system failed: ' . $e->getMessage());
            }

            return 'unmatched';
        }

        $lease  = $unit->leases()->where('status', 'active')->latest()->first();
        $tenant = $lease?->tenant;

        $fullyPaidInvoices = collect();
        $newBalance        = '0.00';
        $creditCarried     = '0.00';

        $payment = DB::transaction(function () use (
            $property, $amount, $paymentDate, $transId, $billRef, $msisdn,
            $tenant, $lease, $method, $providerLabel, &$fullyPaidInvoices, &$newBalance, &$creditCarried
        ) {
            $payment = Payment::create([
                'account_id'   => $property->account_id,
                'tenant_id'    => $tenant?->id,
                'lease_id'     => $lease?->id,
                'amount'       => $amount,
                'payment_type' => 'rent',
                'payment_date' => $paymentDate,
                'method'       => $method,
                'reference'    => $transId,
                'notes'        => 'Auto-reconciled ' . $providerLabel . ' C2B payment. Phone: ' . $msisdn . ', Account: ' . $billRef,
                'is_allocated' => false,
            ]);

            if ($lease) {
                $outstanding = $lease->invoices()
                    ->whereIn('status', ['sent', 'partial', 'overdue'])
                    ->orderBy('invoice_date')
                    ->lockForUpdate()
                    ->get();

                // Include any unallocated rent credits from previous overpayments.
                // Locked (fetched, not summed) so a concurrent payment against the
                // same lease can't also read and consume this same credit before
                // this transaction commits.
                $existingCreditPayments = $lease->payments()
                    ->where('payment_type', 'rent')
                    ->where('is_allocated', false)
                    ->where('reference', 'like', '%-CR')
                    ->lockForUpdate()
                    ->get();

                $existingCredit = $existingCreditPayments->reduce(
                    fn($carry, $creditPayment) => Money::add($carry, $creditPayment->amount), '0.00'
                );

                $remaining = Money::add($amount, $existingCredit);

                // Mark those credit payments as allocated since we're absorbing them now
                if (Money::isPositive($existingCredit)) {
                    foreach ($existingCreditPayments as $creditPayment) {
                        $creditPayment->update(['is_allocated' => true]);
                    }
                }

                foreach ($outstanding as $invoice) {
                    if (!Money::isPositive($remaining)) break;

                    $invoiceBalance = Money::sub($invoice->total_amount, $invoice->amount_paid);
                    if (!Money::isPositive($invoiceBalance)) continue;

                    $allocate = Money::min($remaining, $invoiceBalance);

                    PaymentAllocation::create([
                        'payment_id' => $payment->id,
                        'invoice_id' => $invoice->id,
                        'amount'     => $allocate,
                    ]);

                    $newAmountPaid = Money::add($invoice->amount_paid, $allocate);
                    $newStatus     = Money::gte($newAmountPaid, $invoice->total_amount) ? 'paid' : 'partial';

                    $invoice->update([
                        'amount_paid' => $newAmountPaid,
                        'status'      => $newStatus,
                    ]);

                    if ($newStatus === 'paid') {
                        $fullyPaidInvoices->push($invoice->fresh());
                    }

                    $remaining = Money::sub($remaining, $allocate);
                }

                $payment->update(['is_allocated' => true]);

                // Store any excess as a rent credit to apply against future invoices
                if (Money::isPositive($remaining)) {
                    $creditCarried = $remaining;
                    Payment::create([
                        'account_id'   => $property->account_id,
                        'tenant_id'    => $tenant?->id,
                        'lease_id'     => $lease->id,
                        'amount'       => $remaining,
                        'payment_type' => 'rent',
                        'payment_date' => $paymentDate,
                        'method'       => $method,
                        'reference'    => $transId . '-CR',
                        'notes'        => 'Rent credit carried forward from ' . $providerLabel . ' payment ' . $transId . '. To be applied to next invoice.',
                        'is_allocated' => false,
                    ]);
                }

                $newBalance = Money::sub(
                    $lease->invoices()->sum('total_amount'),
                    $lease->payments()
                        ->where('payment_type', '!=', 'deposit')
                        ->where(function ($q) {
                            $q->where('is_allocated', true)
                              ->orWhere('reference', 'not like', '%-CR');
                        })
                        ->sum('amount')
                );
            }

            return $payment;
        });

        try {
            AuditService::system(
                $property->account_id,
                'payment.' . $method . '_reconciled',
                $providerLabel . ' payment of ' . currency($amount) . ' auto-reconciled for '
                    . ($tenant?->full_name ?? 'unit ' . $unit->name)
                    . ' (ref: ' . $transId . ')',
                $payment,
                [
                    'trans_id'       => $transId,
                    'bill_ref'       => $billRef,
                    'unit_id'        => $unit->id,
                    'amount'         => $amount,
                    'credit_carried' => $creditCarried,
                ]
            );
        } catch (\Exception $e) {
            Log::error('AuditService::system failed: ' . $e->getMessage());
        }

        if ($tenant && $tenant->phone) {
            $this->sendConfirmationSms($property, $tenant, $payment, $fullyPaidInvoices, $newBalance, $creditCarried, $providerLabel);
        }

        return 'matched';
    }

    /**
     * Match BillRefNumber to a unit. Only unit_number matching is supported —
     * a property still configured with the old phone_number/tenant_name
     * modes will simply fall through to unmatched/review instead of erroring.
     */
    private function matchUnit(Property $property, string $accountFormat, string $billRef, string $msisdn): ?Unit
    {
        if ($accountFormat === 'unit_number') {
            return \App\Services\UnitMatcher::match($property, $billRef);
        }

        return null;
    }

    /**
     * Normalize phone to 07XXXXXXXX for matching against tenants.phone.
     */
    private function normalizePhoneForMatch(string $phone): ?string
    {
        $phone = trim($phone);
        if ($phone === '') return null;

        if (preg_match('/^254[71][0-9]{8}$/', $phone)) {
            return '0' . substr($phone, 3);
        }

        if (preg_match('/^0[71][0-9]{8}$/', $phone)) {
            return $phone;
        }

        if (preg_match('/^[71][0-9]{8}$/', $phone)) {
            return '0' . $phone;
        }

        return null;
    }

    private function sendConfirmationSms(
        Property $property,
        Tenant $tenant,
        Payment $payment,
        $fullyPaidInvoices,
        string $newBalance,
        string $creditCarried = '0.00',
        string $providerLabel = 'MPESA'
    ): void {
        $providerLabel = strtoupper($providerLabel);
        $account = $property->account;
        $sms     = new SmsService($account);
        if (!$sms->hasCredits()) return;

        $unit     = $payment->lease?->unit;
        $amount   = number_format($payment->amount);
        $datePaid = \Carbon\Carbon::parse($payment->payment_date)->format('d M Y');

        if ($fullyPaidInvoices->isNotEmpty()) {
            $invoice = $fullyPaidInvoices->first();
            $period  = \Carbon\Carbon::createFromDate(
                $invoice->period_year, $invoice->period_month, 1
            )->format('F Y');

            $creditLine = $creditCarried > 0
                ? "\n" . 'Credit carried forward: KES ' . number_format($creditCarried) . '.'
                : '';

            $balanceLine = $newBalance <= 0
                ? 'Balance: KES 0 - Fully paid.' . $creditLine . "\n" . 'Thank you for paying on time.'
                : 'Outstanding balance: KES ' . number_format($newBalance) . $creditLine . "\n" . 'Thank you.';

            $message =
                'PAYMENT RECEIVED - ' . strtoupper($property->name) . "\n" .
                'Tenant: ' . $tenant->full_name . "\n" .
                'Unit: ' . ($unit?->name ?? '') . "\n" .
                'Period: ' . $period . "\n" .
                'Amount: KES ' . $amount . ' (' . $providerLabel . ')' . "\n" .
                'Ref: ' . $payment->reference . "\n" .
                'Date: ' . $datePaid . "\n" .
                $balanceLine . "\n" .
                'Powered by Nyumba.';
        } else {
            $creditLine = $creditCarried > 0
                ? "\n" . 'Credit carried forward: KES ' . number_format($creditCarried) . '.'
                : '';

            $message =
                'PAYMENT RECEIVED - ' . strtoupper($property->name) . "\n" .
                'Tenant: ' . $tenant->full_name . "\n" .
                'Unit: ' . ($unit?->name ?? '') . "\n" .
                'Amount: KES ' . $amount . ' (MPESA)' . "\n" .
                'Ref: ' . $payment->reference . "\n" .
                'Date: ' . $datePaid . "\n" .
                'Remaining balance: KES ' . number_format(max(0, $newBalance)) . $creditLine . "\n" .
                'Powered by Nyumba.';
        }

        $sms->send($tenant->phone, $message, $tenant->id);
    }
}