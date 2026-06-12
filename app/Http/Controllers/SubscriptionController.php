<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\MpesaTransaction;
use App\Services\AuditService;
use App\Services\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * Initiate an STK push for a plan upgrade.
     */
    public function initiate(Request $request, MpesaService $mpesa)
    {
        $validated = $request->validate([
            'plan'          => ['required', 'in:starter,growth,pro'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'phone'         => ['required', 'string'],
        ]);

        $account = auth()->user()->account;
        $planDef = Account::PLANS[$validated['plan']];

        $amount = $validated['billing_cycle'] === 'yearly'
            ? $planDef['price_yearly']
            : $planDef['price_monthly'];

        if ($amount <= 0) {
            return back()->with('error', 'This plan cannot be purchased online. Please contact support.');
        }

        $phone = $mpesa->formatPhone($validated['phone']);

        if (!preg_match('/^254[71][0-9]{8}$/', $phone)) {
            return back()->with('error', 'Please enter a valid M-Pesa phone number (e.g. 0712345678).');
        }

        $callbackUrl = 'https://nyumba-production.up.railway.app/mpesa/stk/callback';

        $result = $mpesa->stkPush(
            phone: $phone,
            amount: (float) $amount,
            accountRef: 'NYUMBA-' . $account->id,
            description: ucfirst($validated['plan']) . ' plan',
            callbackUrl: $callbackUrl
        );

        if (!$result['success']) {
            return back()->with('error', $result['error']);
        }

        $transaction = MpesaTransaction::create([
            'account_id'          => $account->id,
            'type'                => 'subscription',
            'plan'                => $validated['plan'],
            'billing_cycle'       => $validated['billing_cycle'],
            'amount'              => $amount,
            'phone'               => $phone,
            'checkout_request_id' => $result['checkout_request_id'],
            'merchant_request_id' => $result['merchant_request_id'],
            'status'              => 'pending',
        ]);

        return response()->json([
            'success'             => true,
            'checkout_request_id' => $transaction->checkout_request_id,
            'message'             => 'Check your phone and enter your M-Pesa PIN to complete payment.',
        ]);
    }

    /**
     * Frontend polls this to check if the STK push has completed.
     */
    public function status(string $checkoutRequestId)
    {
        $transaction = MpesaTransaction::where('checkout_request_id', $checkoutRequestId)
            ->where('account_id', auth()->user()->account_id)
            ->first();

        if (!$transaction) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json([
            'status' => $transaction->status,
            'plan'   => $transaction->plan,
            'desc'   => $transaction->result_desc,
        ]);
    }

    /**
     * Safaricom calls this URL with the STK push result. Public, unauthenticated.
     */
    public function callback(Request $request)
    {
        $payload = $request->all();
        Log::info('M-Pesa STK callback received', $payload);

        $stkCallback = $payload['Body']['stkCallback'] ?? null;

        if (!$stkCallback) {
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? null;
        $resultCode        = $stkCallback['ResultCode'] ?? null;
        $resultDesc        = $stkCallback['ResultDesc'] ?? null;

        $transaction = MpesaTransaction::where('checkout_request_id', $checkoutRequestId)->first();

        if (!$transaction) {
            Log::warning('M-Pesa callback: no matching transaction', ['checkout_request_id' => $checkoutRequestId]);
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        // Already processed — Safaricom may retry callbacks
        if ($transaction->status !== 'pending') {
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        if ((int) $resultCode === 0) {
            // Success — extract receipt number from CallbackMetadata
            $items = $stkCallback['CallbackMetadata']['Item'] ?? [];
            $meta  = [];
            foreach ($items as $item) {
                $meta[$item['Name']] = $item['Value'] ?? null;
            }

            $transaction->update([
                'status'        => 'success',
                'result_code'   => $resultCode,
                'result_desc'   => $resultDesc,
                'mpesa_receipt' => $meta['MpesaReceiptNumber'] ?? null,
                'completed_at'  => now(),
            ]);

            $this->applyPlanUpgrade($transaction);
        } else {
            // Failed or cancelled by user
            $status = (int) $resultCode === 1032 ? 'cancelled' : 'failed';

            $transaction->update([
                'status'       => $status,
                'result_code'  => $resultCode,
                'result_desc'  => $resultDesc,
                'completed_at' => now(),
            ]);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /**
     * On successful payment, upgrade the account's plan and top up SMS credits.
     */
    private function applyPlanUpgrade(MpesaTransaction $transaction): void
    {
        $account = Account::find($transaction->account_id);
        if (!$account) return;

        $planDef = Account::PLANS[$transaction->plan] ?? null;
        if (!$planDef) return;

        $duration = $transaction->billing_cycle === 'yearly' ? 12 : 1;

        // Extend from current expiry if still active, otherwise from now
        $base = ($account->plan_expires_at && $account->plan_expires_at->isFuture())
            ? $account->plan_expires_at
            : now();

        $account->update([
            'plan'            => $transaction->plan,
            'plan_expires_at' => $base->copy()->addMonths($duration),
            'subscribed_at'   => $account->subscribed_at ?? now(),
        ]);

        // Top up SMS credits for this billing period
        $creditsToAdd = $planDef['sms_credits_monthly'] * $duration;
        $account->increment('sms_credits', $creditsToAdd);

        \App\Models\Notification::create([
            'account_id' => $account->id,
            'type'       => 'subscription_activated',
            'title'      => 'Plan upgraded to ' . $planDef['name'],
            'body'       => 'Your payment of ' . currency($transaction->amount) . ' was received via M-Pesa ('
                . $transaction->mpesa_receipt . '). Your account is now on the ' . $planDef['name']
                . ' plan with ' . $creditsToAdd . ' SMS credits added.',
        ]);

        AuditService::log(
            'subscription.upgraded',
            'Account upgraded to ' . $planDef['name'] . ' plan via M-Pesa (' . $transaction->mpesa_receipt . ')',
            $account,
            [
                'plan'          => $transaction->plan,
                'billing_cycle' => $transaction->billing_cycle,
                'amount'        => $transaction->amount,
                'receipt'       => $transaction->mpesa_receipt,
                'credits_added' => $creditsToAdd,
            ]
        );
    }
}