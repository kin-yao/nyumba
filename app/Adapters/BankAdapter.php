<?php

namespace App\Adapters;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * One implementation per directly-integrated bank (KCB now, others as they
 * get built). Each bank registers ONE account-wide URL with Nyumba and
 * identifies which property a payload is for via its own account-reference
 * field. This does NOT cover Pesalink (IpslController) — that's a payment
 * rail with its own, structurally different per-property and
 * central-collection routing, not a single "bank_code".
 */
interface BankAdapter
{
    /** Matches properties.bank_code. */
    public function code(): string;

    /** Human-readable name for logs/audit. */
    public function label(): string;

    /** Verifies the request really came from this bank. */
    public function verifySignature(Request $request): bool;

    /** Finds which property this payload is for, using this bank's own account-reference field. */
    public function resolveProperty(array $payload): ?Property;

    /** This bank's own account-reference value from the raw payload. */
    public function accountReferenceFromPayload(array $payload): string;

    /** The unit/bill reference from a Bill-Validation payload. */
    public function billRefFromValidationPayload(array $payload): string;

    /** This bank's own request identifier from a Bill-Validation payload. */
    public function requestIdFromPayload(array $payload): string;

    /** Builds this bank's Bill-Validation response, in its own required shape. */
    public function validationResponse(
        string $requestId, int $statusCode, string $message,
        ?string $customerName, ?string $billAmount, ?string $accountReference, int $httpStatus = 200
    ): JsonResponse;

    /** Normalizes a notification payload into the shape processTransaction() expects. */
    public function notificationFields(array $payload, string $accountReference): array;

    /** This bank's own transaction identifier from a notification payload. */
    public function transactionIdFromPayload(array $payload): string;

    /** Builds this bank's notification-acknowledgment response, in its own required shape. */
    public function notificationResponse(string $transactionId, int $statusCode, string $message, int $httpStatus = 200): JsonResponse;
}