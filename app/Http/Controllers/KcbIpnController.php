<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Services\UnitMatcher;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class KcbIpnController extends Controller
{
    /**
     * Bill-Validation — POST /payments/kcb/validation
     * KCB calls this BEFORE the customer's payment is allowed through, to
     * check the bill reference is real. Read-only, no DB writes here.
     */
    public function validate(Request $request): JsonResponse
    {
        $payload   = $request->all();
        $requestId = (string) ($payload['requestId'] ?? 'unknown');

        Log::info('KCB bill-validation received', ['payload' => $payload]);

        if (!$this->verifySignature($request)) {
            return $this->validationResponse($requestId, 1, 'Signature verification failed', null, null, null, null, null, 401);
        }

        $organizationReference = (string) ($payload['organizationReference'] ?? '');
        $customerReference     = (string) ($payload['customerReference'] ?? '');

        $property = Property::withoutGlobalScopes()
            ->where('bank_code', 'kcb')
            ->where('bank_account_number', $organizationReference)
            ->first();

        if (!$property) {
            Log::warning('KCB bill-validation: no property matches organizationReference', [
                'organizationReference' => $organizationReference,
            ]);

            return $this->validationResponse($requestId, 1, 'Bill reference not found', null, null, null, null, $organizationReference);
        }

        $unit = UnitMatcher::match($property, $customerReference);

        if (!$unit) {
            Log::warning('KCB bill-validation: no unit matches customerReference', [
                'property_id'        => $property->id,
                'customerReference'  => $customerReference,
            ]);

            return $this->validationResponse($requestId, 1, 'Bill reference not found', null, null, null, null, $organizationReference);
        }

        $lease  = $unit->leases()->where('status', 'active')->latest()->first();
        $tenant = $lease?->tenant;

        // Balance is a helpful reference figure for the payer, not a fixed
        // requirement — billType PARTIAL means KCB will accept any amount,
        // matching how this app already accepts partial/over payments
        // everywhere else.
        $billAmount = '0.00';
        if ($lease) {
            $totalCharged = $lease->invoices->reduce(
                fn($carry, $invoice) => Money::add($carry, $invoice->total_amount), '0.00'
            );
            $totalPaid = $lease->payments->where('payment_type', '!=', 'deposit')->reduce(
                fn($carry, $payment) => Money::add($carry, $payment->amount), '0.00'
            );
            $billAmount = Money::max('0.00', Money::sub($totalCharged, $totalPaid));
        }

        return $this->validationResponse(
            $requestId,
            0,
            'Success',
            $tenant?->full_name ?? $unit->name,
            $billAmount,
            'KES',
            'PARTIAL',
            $property->bank_account_number
        );
    }

    /**
     * Account Instant Payment Notification — POST /payments/kcb/account-notification
     * KCB pushes here after a KCB bank account is credited. Every property has
     * its own account number, so we resolve the property from
     * `creditAccountIdentifier` rather than from the URL.
     */
    public function accountNotification(Request $request, MpesaC2BController $reconciler): JsonResponse
    {
        $payload = $request->all();

        Log::info('KCB account-notification received', ['payload' => $payload]);

        if (!$this->verifySignature($request)) {
            return $this->ack($payload['transactionReference'] ?? 'unknown', 1, 'Signature verification failed', 401);
        }

        $accountNumber = (string) ($payload['creditAccountIdentifier'] ?? '');

        $property = Property::withoutGlobalScopes()
            ->where('bank_code', 'kcb')
            ->where('bank_account_number', $accountNumber)
            ->first();

        if (!$property) {
            Log::warning('KCB account-notification: no property matches creditAccountIdentifier', [
                'creditAccountIdentifier' => $accountNumber,
            ]);

            return $this->ack($payload['transactionReference'] ?? 'unknown', 0, 'Notification received');
        }

        $status = $reconciler->processTransaction($property, [
            'TransID'           => $payload['transactionReference'] ?? null,
            'TransAmount'       => $payload['transactionAmount'] ?? null,
            'BillRefNumber'     => $payload['customerReference'] ?? null,
            'MSISDN'            => $payload['customerMobileNumber'] ?? null,
            'TransTime'         => $this->normalizeTimestamp($payload['timestamp'] ?? null),
            'BusinessShortCode' => $accountNumber,
        ], method: 'bank', providerLabel: 'KCB', provider: 'kcb', rawPayload: $request->getContent());

        Log::info('KCB account-notification processed', [
            'property_id' => $property->id,
            'status'      => $status,
        ]);

        return $this->ack($payload['transactionReference'] ?? 'unknown', 0, 'Notification received successfully');
    }

    private function verifySignature(Request $request): bool
    {
        // TEMPORARY — signature verification disabled at Eddy's (KCB) request
        // so they can test the endpoint before we have the real public key.
        // Re-enable by setting KCB_IPN_VERIFY_SIGNATURE=true in Railway (or
        // just removing this block) once KCB confirms the key/sandbox is ready.
        if (!config('services.kcb.verify_signature', true)) {
            Log::warning('KCB IPN: signature verification is DISABLED — accepting unsigned requests');
            return true;
        }

        $publicKeyPem = config('services.kcb.ipn_public_key');

        if (empty($publicKeyPem)) {
            // No key configured yet — fail closed in production, pass in
            // lower environments so you can build/test before you have it.
            if (app()->environment('production')) {
                // Log::critical hits the 'slack' channel (if LOG_SLACK_WEBHOOK_URL
                // is set) so this can't silently fail for days unnoticed — every
                // rejected KCB notification pings it, not just the first one.
                Log::critical('KCB IPN rejected: KCB_IPN_PUBLIC_KEY is not configured in production. Real KCB payments are being rejected right now.', [
                    'endpoint' => 'account-notification',
                ]);
                return false;
            }
            return true;
        }

        $signatureHeader = $request->header('Signature');
        if (empty($signatureHeader)) {
            return false;
        }

        $publicKey = openssl_pkey_get_public($publicKeyPem);
        if ($publicKey === false) {
            Log::error('KCB IPN: KCB_IPN_PUBLIC_KEY is not a valid PEM public key');
            return false;
        }

        $signature = base64_decode($signatureHeader, true);
        if ($signature === false) {
            return false;
        }

        return openssl_verify($request->getContent(), $signature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }

    private function normalizeTimestamp(?string $timestamp): ?string
    {
        if (!$timestamp) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($timestamp)->format('YmdHis');
        } catch (\Throwable $e) {
            return now()->format('YmdHis');
        }
    }

    private function ack(string $transactionId, int $statusCode, string $message, int $httpStatus = 200): JsonResponse
    {
        return response()->json([
            'transactionID' => $transactionId,
            'statusCode'    => (string) $statusCode,
            'statusMessage' => $message,
        ], $httpStatus);
    }

    /**
     * KCB's docs mark every one of these fields "mandatory" in the response,
     * even though only a success sample is shown. On failure this still
     * sends them, with safe placeholder values, rather than omitting fields
     * KCB's own spec says are required.
     */
    private function validationResponse(
        string $requestId,
        int $statusCode,
        string $message,
        ?string $customerName,
        ?string $billAmount,
        ?string $currency,
        ?string $billType,
        ?string $creditAccountIdentifier,
        int $httpStatus = 200
    ): JsonResponse {
        return response()->json([
            'transactionID'           => $requestId,
            'statusCode'              => (string) $statusCode,
            'statusMessage'           => $message,
            'CustomerName'            => $customerName ?? '',
            'billAmount'              => $billAmount ?? '0.00',
            'currency'                => $currency ?? 'KES',
            'billType'                => $billType ?? 'PARTIAL',
            'creditAccountIdentifier' => $creditAccountIdentifier ?? '',
        ], $httpStatus);
    }
}