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
        $pullCallbackUrl = url("/mpesa/pull/{$property->id}/callback");

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
    public function processTransaction(Property $property, array $txn): string
    {
        $transId   = $txn['TransID'] ?? null;
        $amount    = (float) ($txn['TransAmount'] ?? 0);
        $billRef   = trim((string) ($txn['BillRefNumber'] ?? ''));
        $msisdn    = (string) ($txn['MSISDN'] ?? '');
        $transTime = $txn['TransTime'] ?? null;

        if (!$transId || $amount <= 0) {
            Log::warning('M-Pesa C2B: incomplete transaction payload', [
                'property_id' => $property->id,
                'txn'         => $txn,
            ]);
            return 'unmatched';
        }

        // Idempotency — don't double-record the same M-Pesa receipt
        if (Payment::withoutGlobalScopes()->where('reference', $transId)->exists()) {
            return 'duplicate';
        }

        $accountFormat = $property->account_format ?? 'unit_number';
        $unit          = $this->matchUnit($property, $accountFormat, $billRef, $msisdn);

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
                'method'       => 'mpesa',
                'reference'    => $transId,
                'notes'        => 'Unmatched M-Pesa C2B payment. BillRef: "' . $billRef
                    . '", Phone: ' . $msisdn
                    . ', Property: ' . $property->name
                    . '. Needs manual assignment.',
                'is_allocated' => false,
            ]);

            try {
                AuditService::log(
                    'payment.mpesa_unmatched',
                    'Unmatched M-Pesa payment of ' . currency($amount) . ' (ref: ' . $transId . ') for "'
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
                Log::error('AuditService::log failed: ' . $e->getMessage());
            }

            return 'unmatched';
        }

        $lease  = $unit->leases()->where('status', 'active')->latest()->first();
        $tenant = $lease?->tenant;

        $fullyPaidInvoices = collect();
        $newBalance        = 0;
        $creditCarried     = 0;

        $payment = DB::transaction(function () use (
            $property, $amount, $paymentDate, $transId, $billRef, $msisdn,
            $tenant, $lease, &$fullyPaidInvoices, &$newBalance, &$creditCarried
        ) {
            $payment = Payment::create([
                'account_id'   => $property->account_id,
                'tenant_id'    => $tenant?->id,
                'lease_id'     => $lease?->id,
                'amount'       => $amount,
                'payment_type' => 'rent',
                'payment_date' => $paymentDate,
                'method'       => 'mpesa',
                'reference'    => $transId,
                'notes'        => 'Auto-reconciled M-Pesa C2B payment. Phone: ' . $msisdn . ', Account: ' . $billRef,
                'is_allocated' => false,
            ]);

            if ($lease) {
                $outstanding = $lease->invoices()
                    ->whereIn('status', ['sent', 'partial', 'overdue'])
                    ->orderBy('invoice_date')
                    ->get();

                // Include any unallocated rent credits from previous overpayments
                $existingCredit = floatval(
                    $lease->payments()
                        ->where('payment_type', 'rent')
                        ->where('is_allocated', false)
                        ->where('reference', 'like', '%-CR')
                        ->sum('amount')
                );

                $remaining = $amount + $existingCredit;

                // Mark those credit payments as allocated since we're absorbing them now
                if ($existingCredit > 0) {
                    $lease->payments()
                        ->where('payment_type', 'rent')
                        ->where('is_allocated', false)
                        ->where('reference', 'like', '%-CR')
                        ->update(['is_allocated' => true]);
                }

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

                // Store any excess as a rent credit to apply against future invoices
                if ($remaining > 0) {
                    $creditCarried = $remaining;
                    Payment::create([
                        'account_id'   => $property->account_id,
                        'tenant_id'    => $tenant?->id,
                        'lease_id'     => $lease->id,
                        'amount'       => $remaining,
                        'payment_type' => 'rent',
                        'payment_date' => $paymentDate,
                        'method'       => 'mpesa',
                        'reference'    => $transId . '-CR',
                        'notes'        => 'Rent credit carried forward from M-Pesa payment ' . $transId . '. To be applied to next invoice.',
                        'is_allocated' => false,
                    ]);
                }

                $newBalance = floatval($lease->invoices()->sum('total_amount'))
                            - floatval($lease->payments()
                                ->where('payment_type', '!=', 'deposit')
                                ->where(function ($q) {
                                    $q->where('is_allocated', true)
                                      ->orWhere('reference', 'not like', '%-CR');
                                })
                                ->sum('amount'));
            }

            return $payment;
        });

        try {
            AuditService::log(
                'payment.mpesa_reconciled',
                'M-Pesa payment of ' . currency($amount) . ' auto-reconciled for '
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
            Log::error('AuditService::log failed: ' . $e->getMessage());
        }

        if ($tenant && $tenant->phone) {
            $this->sendConfirmationSms($property, $tenant, $payment, $fullyPaidInvoices, $newBalance, $creditCarried);
        }

        return 'matched';
    }

    /**
     * Match BillRefNumber/MSISDN to a unit based on the property's account_format.
     */
    private function matchUnit(Property $property, string $accountFormat, string $billRef, string $msisdn): ?Unit
    {
        if ($accountFormat === 'unit_number') {
            if ($billRef === '') return null;

            return Unit::withoutGlobalScopes()
                ->where('property_id', $property->id)
                ->whereRaw('LOWER(name) = ?', [strtolower($billRef)])
                ->first();
        }

        if ($accountFormat === 'phone_number') {
            $candidates = array_filter([$msisdn, $billRef]);

            foreach ($candidates as $phone) {
                $normalized = $this->normalizePhoneForMatch($phone);
                if (!$normalized) continue;

                $lease = Lease::withoutGlobalScopes()
                    ->where('status', 'active')
                    ->whereHas('unit', fn($q) => $q->withoutGlobalScopes()->where('property_id', $property->id))
                    ->whereHas('tenant', fn($q) => $q->withoutGlobalScopes()->where('phone', $normalized))
                    ->with('unit')
                    ->first();

                if ($lease) return $lease->unit;
            }

            return null;
        }

        if ($accountFormat === 'tenant_name') {
            if ($billRef === '') return null;

            $lease = Lease::withoutGlobalScopes()
                ->where('status', 'active')
                ->whereHas('unit', fn($q) => $q->withoutGlobalScopes()->where('property_id', $property->id))
                ->whereHas('tenant', function ($q) use ($billRef) {
                    $q->withoutGlobalScopes()
                      ->whereRaw('LOWER(CONCAT(first_name, " ", last_name)) = ?', [strtolower(trim($billRef))]);
                })
                ->with('unit')
                ->first();

            return $lease?->unit;
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
        float $newBalance,
        float $creditCarried = 0
    ): void {
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
                'Amount: KES ' . $amount . ' (MPESA)' . "\n" .
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