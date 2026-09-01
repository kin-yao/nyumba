<?php

namespace App\Adapters;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class KcbAdapter implements BankAdapter
{
    public function code(): string
    {
        return 'kcb';
    }

    public function label(): string
    {
        return 'KCB';
    }

    public function verifySignature(Request $request): bool
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
            if (app()->environment('production')) {
                Log::critical('KCB IPN rejected: KCB_IPN_PUBLIC_KEY is not configured in production. Real KCB payments are being rejected right now.');
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

    public function resolveProperty(array $payload): ?Property
    {
        $accountNumber = $this->accountReferenceFromPayload($payload);

        if ($accountNumber === '') {
            return null;
        }

        return Property::withoutGlobalScopes()
            ->where('bank_code', 'kcb')
            ->where('bank_account_number', $accountNumber)
            ->first();
    }

    public function accountReferenceFromPayload(array $payload): string
    {
        return (string) ($payload['organizationReference'] ?? $payload['creditAccountIdentifier'] ?? '');
    }

    public function billRefFromValidationPayload(array $payload): string
    {
        return (string) ($payload['customerReference'] ?? '');
    }

    public function requestIdFromPayload(array $payload): string
    {
        return (string) ($payload['requestId'] ?? 'unknown');
    }

    public function validationResponse(
        string $requestId, int $statusCode, string $message,
        ?string $customerName, ?string $billAmount, ?string $accountReference, int $httpStatus = 200
    ): JsonResponse {
        return response()->json([
            'transactionID'           => $requestId,
            'statusCode'              => (string) $statusCode,
            'statusMessage'           => $message,
            'CustomerName'            => $customerName ?? '',
            'billAmount'              => $billAmount ?? '0.00',
            'currency'                => 'KES',
            'billType'                => 'PARTIAL',
            'creditAccountIdentifier' => $accountReference ?? '',
        ], $httpStatus);
    }

    public function notificationFields(array $payload, string $accountReference): array
    {
        return [
            'TransID'           => $payload['transactionReference'] ?? null,
            'TransAmount'       => $payload['transactionAmount'] ?? null,
            'BillRefNumber'     => $payload['customerReference'] ?? null,
            'MSISDN'            => $payload['customerMobileNumber'] ?? null,
            'TransTime'         => $this->normalizeTimestamp($payload['timestamp'] ?? null),
            'BusinessShortCode' => $accountReference,
        ];
    }

    public function transactionIdFromPayload(array $payload): string
    {
        return (string) ($payload['transactionReference'] ?? 'unknown');
    }

    public function notificationResponse(string $transactionId, int $statusCode, string $message, int $httpStatus = 200): JsonResponse
    {
        return response()->json([
            'transactionID' => $transactionId,
            'statusCode'    => (string) $statusCode,
            'statusMessage' => $message,
        ], $httpStatus);
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
}