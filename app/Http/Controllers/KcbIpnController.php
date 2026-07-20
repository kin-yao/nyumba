<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class KcbIpnController extends Controller
{
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
            ->where('kcb_account_number', $accountNumber)
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
        ], method: 'bank', providerLabel: 'KCB');

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
            'statusCode'    => $statusCode,
            'statusMessage' => $message,
        ], $httpStatus);
    }
}