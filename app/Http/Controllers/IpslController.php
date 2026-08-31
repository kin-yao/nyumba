<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Services\UnitMatcher;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * IPSL (Pesalink) Biller PGW — one landlord's own bank account per
 * property, so the property is identified by the URL itself, not by a
 * field in the payload (IPSL's own request bodies never say which
 * account they're for).
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
     * Sent after a successful transfer into the landlord's account.
     */
    public function notification(Request $request, int $property, MpesaC2BController $reconciler): JsonResponse
    {
        $property = Property::withoutGlobalScopes()->findOrFail($property);
        $payload  = $request->all();

        Log::info('IPSL IPN received', ['property_id' => $property->id, 'payload' => $payload]);

        $rrn = (string) ($payload['rrn'] ?? 'unknown');

        if (!$this->verifySignature($request, $property)) {
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
        ], method: 'bank', providerLabel: 'Pesalink', preMatchedUnit: $unit);

        Log::info('IPSL IPN processed', ['property_id' => $property->id, 'status' => $result]);

        return response()->json(['rrn' => $rrn, 'status' => 'SUCCESS']);
    }

    private function verifySignature(Request $request, Property $property): bool
    {
        $password = $property->ipsl_password;

        if (empty($password)) {
            if (app()->environment('production')) {
                Log::critical('IPSL IPN rejected: ipsl_password not configured for this property in production.', [
                    'property_id' => $property->id,
                ]);
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