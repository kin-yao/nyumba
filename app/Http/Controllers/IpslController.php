<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Services\UnitMatcher;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * IPSL (Pesalink) Biller PGW — covers two modes:
 * - Per-landlord: one bank account per property, identified by the URL
 *   itself (validate/notification).
 * - Central collection: one shared pooled account across every opted-in
 *   property (bank_code = 'pesalink_central'), identified purely by the
 *   billRef since there's no per-property URL for it
 *   (validateCentral/notificationCentral).
 */
class IpslController extends Controller
{
    /**
     * Bill-Validation — POST /payments/ipsl/{property}/validate
     * IPSL calls this before the sender's transfer is allowed through.
     *
     * NOTE: the request has a body-level "signature" field, but IPSL's
     * spec never documents how to verify it (only the separate IPN
     * signature, in an X-Signature header, is documented). Logged for
     * now, not verified — do not silently invent an algorithm for this.
     */
    public function validate(Request $request, int $property): JsonResponse
    {
        $property = Property::withoutGlobalScopes()->findOrFail($property);
        $payload  = $request->all();

        Log::info('IPSL validate received', [
            'property_id'    => $property->id,
            'payload'        => $payload,
            'body_signature' => $payload['signature'] ?? null, // not verified — see note above
        ]);

        $billRef = (string) ($payload['billRef'] ?? '');
        $amount  = Money::normalize($payload['amount'] ?? 0);

        $unit = UnitMatcher::matchNormalized($property, $billRef);

        if (!$unit) {
            Log::warning('IPSL validate: no unit matches billRef', [
                'property_id' => $property->id,
                'billRef'     => $billRef,
            ]);

            return response()->json([
                'billRef'           => $billRef,
                'amount'            => $amount,
                'status'            => 'error',
                'statusDescription' => 'Bill Ref does not exist',
            ]);
        }

        return response()->json([
            'billRef'           => $billRef,
            'amount'            => $amount,
            'billId'            => (string) $unit->id,
            'status'            => 'valid',
            'statusDescription' => '',
        ]);
    }

    /**
     * IPN — POST /payments/ipsl/{property}/notification
     * Sent after a successful transfer into the landlord's own account.
     */
    public function notification(Request $request, int $property, MpesaC2BController $reconciler): JsonResponse
    {
        $property = Property::withoutGlobalScopes()->findOrFail($property);
        $payload  = $request->all();

        Log::info('IPSL IPN received', ['property_id' => $property->id, 'payload' => $payload]);

        $rrn = (string) ($payload['rrn'] ?? 'unknown');

        if (!$this->verifyHmacSignature($request, $property->ipsl_password, "property #{$property->id}")) {
            return response()->json(['rrn' => $rrn, 'status' => 'INSECURE'], 401);
        }

        // Only success/ACCP is a completed transfer — anything else (future
        // RJCT/ACWP failed-transaction notifications) must not be reconciled.
        $status = strtolower((string) ($payload['status'] ?? ''));
        if (!in_array($status, ['success', 'accp'], true)) {
            Log::info('IPSL IPN: ignoring non-success status', ['status' => $payload['status'] ?? null]);
            return response()->json(['rrn' => $rrn, 'status' => 'SUCCESS']);
        }

        $billRef = (string) ($payload['Bill_reference'] ?? $payload['paymentReason'] ?? '');
        $unit    = UnitMatcher::matchNormalized($property, $billRef);

        $result = $reconciler->processTransaction($property, [
            'TransID'           => $rrn,
            'TransAmount'       => $payload['amount'] ?? null,
            'BillRefNumber'     => $billRef,
            'MSISDN'            => $payload['phoneSrc'] ?? null,
            'TransTime'         => $this->normalizeTimestamp($payload['date'] ?? null),
            'BusinessShortCode' => null,
        ], method: 'bank', providerLabel: 'Pesalink', preMatchedUnit: $unit, provider: 'ipsl', rawPayload: $request->getContent());

        Log::info('IPSL IPN processed', ['property_id' => $property->id, 'status' => $result]);

        return response()->json(['rrn' => $rrn, 'status' => 'SUCCESS']);
    }

    /**
     * Bill-Validation for the shared central-collection account —
     * POST /payments/ipsl-central/validate
     * No {property} in the URL — every opted-in landlord shares this one
     * account, so the unit is found purely from billRef, searched across
     * every property with bank_code = 'pesalink_central'.
     */
    public function validateCentral(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('IPSL central validate received', [
            'payload'        => $payload,
            'body_signature' => $payload['signature'] ?? null, // not verified — see note on validate()
        ]);

        $billRef = (string) ($payload['billRef'] ?? '');
        $amount  = Money::normalize($payload['amount'] ?? 0);

        $unit = UnitMatcher::matchCentralCollection($billRef);

        if (!$unit) {
            Log::warning('IPSL central validate: no unit matches billRef', ['billRef' => $billRef]);

            return response()->json([
                'billRef'           => $billRef,
                'amount'            => $amount,
                'status'            => 'error',
                'statusDescription' => 'Bill Ref does not exist',
            ]);
        }

        return response()->json([
            'billRef'           => $billRef,
            'amount'            => $amount,
            'billId'            => (string) $unit->id,
            'status'            => 'valid',
            'statusDescription' => '',
        ]);
    }

    /**
     * IPN for the shared central-collection account —
     * POST /payments/ipsl-central/notification
     * The property is resolved from the matched unit, not from a URL
     * parameter — there isn't one for this shared account.
     */
    public function notificationCentral(Request $request, MpesaC2BController $reconciler): JsonResponse
    {
        $payload = $request->all();

        Log::info('IPSL central IPN received', ['payload' => $payload]);

        $rrn = (string) ($payload['rrn'] ?? 'unknown');

        if (!$this->verifyHmacSignature($request, config('services.ipsl_central.password'), 'central collection')) {
            return response()->json(['rrn' => $rrn, 'status' => 'INSECURE'], 401);
        }

        $status = strtolower((string) ($payload['status'] ?? ''));
        if (!in_array($status, ['success', 'accp'], true)) {
            Log::info('IPSL central IPN: ignoring non-success status', ['status' => $payload['status'] ?? null]);
            return response()->json(['rrn' => $rrn, 'status' => 'SUCCESS']);
        }

        $billRef = (string) ($payload['Bill_reference'] ?? $payload['paymentReason'] ?? '');
        $unit    = UnitMatcher::matchCentralCollection($billRef);

        if (!$unit) {
            Log::warning('IPSL central IPN: no unit matches billRef, cannot reconcile', [
                'billRef' => $billRef,
                'rrn'     => $rrn,
            ]);

            // Real money arrived but we don't know whose it is yet —
            // record it rather than letting it vanish into a log line.
            // account_id/property_id stay null until an admin manually
            // resolves which landlord this belongs to — payment_events was
            // deliberately built nullable for exactly this case.
            try {
                $orphanEvent = \App\Models\PaymentEvent::create([
                    'account_id'              => null,
                    'property_id'             => null,
                    'provider'                => 'pesalink_collection',
                    'channel'                 => 'bank',
                    'provider_transaction_id' => $rrn,
                    'amount'                  => Money::normalize($payload['amount'] ?? 0),
                    'currency'                => 'KES',
                    'raw_payload'             => $request->getContent(),
                    'signature_valid'         => true, // verifyHmacSignature() already passed to reach this point
                    'received_at'             => now(),
                ]);

                $orphanEvent->transitionTo(\App\Models\PaymentEvent::STATUS_VERIFIED);
                $orphanEvent->transitionTo(\App\Models\PaymentEvent::STATUS_QUEUED);
                $orphanEvent->transitionTo(\App\Models\PaymentEvent::STATUS_PROCESSING);
                $orphanEvent->transitionTo(\App\Models\PaymentEvent::STATUS_UNMATCHED);
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                // Already recorded from a previous delivery attempt of the same rrn — fine, not an error
            }

            return response()->json(['rrn' => $rrn, 'status' => 'SUCCESS']);
        }

        $property = $unit->property;

        $result = $reconciler->processTransaction($property, [
            'TransID'           => $rrn,
            'TransAmount'       => $payload['amount'] ?? null,
            'BillRefNumber'     => $billRef,
            'MSISDN'            => $payload['phoneSrc'] ?? null,
            'TransTime'         => $this->normalizeTimestamp($payload['date'] ?? null),
            'BusinessShortCode' => null,
        ], method: 'bank', providerLabel: 'Pesalink Central', preMatchedUnit: $unit, provider: 'pesalink_collection', rawPayload: $request->getContent());

        Log::info('IPSL central IPN processed', ['property_id' => $property->id, 'status' => $result]);

        return response()->json(['rrn' => $rrn, 'status' => 'SUCCESS']);
    }

    private function verifyHmacSignature(Request $request, ?string $password, string $context): bool
    {
        if (empty($password)) {
            if (app()->environment('production')) {
                Log::critical("IPSL IPN rejected: no password configured for {$context} in production.");
                return false;
            }
            return true;
        }

        $signatureHeader = $request->header('X-Signature');
        if (empty($signatureHeader)) {
            return false;
        }

        $computed = hash_hmac('sha1', $request->getContent(), $password);

        return hash_equals($computed, strtolower($signatureHeader));
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