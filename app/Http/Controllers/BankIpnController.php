<?php

namespace App\Http\Controllers;

use App\Adapters\BankAdapter;
use App\Adapters\KcbAdapter;
use App\Services\UnitMatcher;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Routes to the right BankAdapter based on which bank a property is
 * configured for. Adding a new direct-bank integration means writing one
 * new adapter class and adding it to $adapters below — not a new
 * controller. Pesalink (IpslController) is intentionally separate — see
 * BankAdapter's docblock.
 */
class BankIpnController extends Controller
{
    /** @var array<string, class-string<BankAdapter>> */
    private array $adapters = [
        'kcb' => KcbAdapter::class,
    ];

    public function validate(Request $request, string $bankCode): JsonResponse
    {
        $adapter   = $this->resolveAdapter($bankCode);
        $payload   = $request->all();
        $requestId = $adapter->requestIdFromPayload($payload);

        Log::info($adapter->label() . ' bill-validation received', ['payload' => $payload]);

        if (!$adapter->verifySignature($request)) {
            return $adapter->validationResponse($requestId, 1, 'Signature verification failed', null, null, null, 401);
        }

        $accountReference = $adapter->accountReferenceFromPayload($payload);
        $billRef          = $adapter->billRefFromValidationPayload($payload);

        $property = $adapter->resolveProperty($payload);

        if (!$property) {
            Log::warning($adapter->label() . ' bill-validation: no property matches account reference', [
                'accountReference' => $accountReference,
            ]);
            return $adapter->validationResponse($requestId, 1, 'Bill reference not found', null, null, $accountReference);
        }

        $unit = UnitMatcher::match($property, $billRef);

        if (!$unit) {
            Log::warning($adapter->label() . ' bill-validation: no unit matches bill reference', [
                'property_id' => $property->id,
                'billRef'     => $billRef,
            ]);
            return $adapter->validationResponse($requestId, 1, 'Bill reference not found', null, null, $accountReference);
        }

        $lease  = $unit->leases()->where('status', 'active')->latest()->first();
        $tenant = $lease?->tenant;

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

        return $adapter->validationResponse(
            $requestId, 0, 'Success',
            $tenant?->full_name ?? $unit->name,
            $billAmount,
            $accountReference
        );
    }

    public function notification(Request $request, string $bankCode, MpesaC2BController $reconciler): JsonResponse
    {
        $adapter       = $this->resolveAdapter($bankCode);
        $payload       = $request->all();
        $transactionId = $adapter->transactionIdFromPayload($payload);

        Log::info($adapter->label() . ' notification received', ['payload' => $payload]);

        if (!$adapter->verifySignature($request)) {
            return $adapter->notificationResponse($transactionId, 1, 'Signature verification failed', 401);
        }

        $accountReference = $adapter->accountReferenceFromPayload($payload);
        $property         = $adapter->resolveProperty($payload);

        if (!$property) {
            Log::warning($adapter->label() . ' notification: no property matches account reference', [
                'accountReference' => $accountReference,
            ]);
            return $adapter->notificationResponse($transactionId, 0, 'Notification received');
        }

        $status = $reconciler->processTransaction(
            $property,
            $adapter->notificationFields($payload, $accountReference),
            method: 'bank',
            providerLabel: $adapter->label(),
            provider: $adapter->code(),
            rawPayload: $request->getContent()
        );

        Log::info($adapter->label() . ' notification processed', [
            'property_id' => $property->id,
            'status'      => $status,
        ]);

        return $adapter->notificationResponse($transactionId, 0, 'Notification received successfully');
    }

    private function resolveAdapter(string $bankCode): BankAdapter
    {
        $adapterClass = $this->adapters[$bankCode] ?? null;

        abort_unless($adapterClass, 404, "No bank adapter registered for '{$bankCode}'.");

        return app($adapterClass);
    }
}