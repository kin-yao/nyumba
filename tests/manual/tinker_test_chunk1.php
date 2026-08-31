<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/**
 * Manual validation for Chunk 1: the payment_events table, the PaymentEvent
 * model, its state machine, the unique(provider, provider_transaction_id)
 * constraint, the payment_event_id FK on payments, and the dropped
 * mpesa_transaction_id/mpesa_phone columns.
 *
 * Nothing in the app actually creates a PaymentEvent yet (that's chunk 5),
 * so this exercises the model directly rather than through a controller.
 *
 * Every DB write happens inside a transaction that is rolled back at the
 * end, so this leaves no data behind whether it passes or fails.
 */

$failures = 0;
$checks   = 0;

$check = function (string $label, bool $condition) use (&$failures, &$checks) {
    $checks++;
    if ($condition) {
        echo "  PASS  {$label}\n";
    } else {
        $failures++;
        echo "  FAIL  {$label}\n";
    }
};

echo "\n=== Chunk 1: payment_events table, PaymentEvent model, state machine ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    // --- Schema checks ---
    $check('payments table no longer has mpesa_transaction_id', !\Illuminate\Support\Facades\Schema::hasColumn('payments', 'mpesa_transaction_id'));
    $check('payments table no longer has mpesa_phone', !\Illuminate\Support\Facades\Schema::hasColumn('payments', 'mpesa_phone'));
    $check('payments table has payment_event_id', \Illuminate\Support\Facades\Schema::hasColumn('payments', 'payment_event_id'));
    $check('payment_events table exists', \Illuminate\Support\Facades\Schema::hasTable('payment_events'));

    // --- Basic creation and casts ---
    $event = \App\Models\PaymentEvent::create([
        'provider'                 => 'mpesa',
        'channel'                  => 'mpesa',
        'provider_transaction_id'  => 'TEST-PE-001',
        'amount'                   => '1500.00',
        'currency'                 => 'KES',
        'raw_payload'              => '{"TransID":"TEST-PE-001"}',
        'signature_valid'          => true,
        'received_at'              => now(),
    ]);

    $check('PaymentEvent created', $event !== null && $event->exists);
    $check('status defaults to RECEIVED', $event->status === \App\Models\PaymentEvent::STATUS_RECEIVED);
    $check('amount cast to decimal:2 string', $event->amount === '1500.00');
    $check('signature_valid cast to bool true', $event->signature_valid === true);
    $check('received_at cast to a Carbon instance', $event->received_at instanceof \Carbon\Carbon);

    // --- Unique constraint ---
    $threwOnDuplicate = false;
    try {
        \App\Models\PaymentEvent::create([
            'provider'                => 'mpesa',
            'channel'                 => 'mpesa',
            'provider_transaction_id' => 'TEST-PE-001', // same provider + id as above
            'amount'                  => '999.00',
            'raw_payload'             => '{}',
            'received_at'             => now(),
        ]);
    } catch (\Illuminate\Database\QueryException $e) {
        $threwOnDuplicate = true;
    }
    $check('Duplicate (provider, provider_transaction_id) throws a DB exception', $threwOnDuplicate);

    // A different provider with the same transaction id must NOT collide
    $differentProviderEvent = null;
    try {
        $differentProviderEvent = \App\Models\PaymentEvent::create([
            'provider'                => 'kcb',
            'channel'                 => 'bank',
            'provider_transaction_id' => 'TEST-PE-001', // same id, different provider
            'amount'                  => '2000.00',
            'raw_payload'             => '{}',
            'received_at'             => now(),
        ]);
    } catch (\Throwable $e) {
        // leave $differentProviderEvent null, check below will fail with detail
    }
    $check('Same transaction id under a DIFFERENT provider does not collide', $differentProviderEvent !== null);

    // --- State machine: full happy path ---
    $event->transitionTo(\App\Models\PaymentEvent::STATUS_VERIFIED);
    $check('RECEIVED -> VERIFIED succeeds', $event->fresh()->status === 'VERIFIED');

    $event->transitionTo(\App\Models\PaymentEvent::STATUS_QUEUED);
    $check('VERIFIED -> QUEUED succeeds', $event->fresh()->status === 'QUEUED');

    $event->transitionTo(\App\Models\PaymentEvent::STATUS_PROCESSING);
    $check('QUEUED -> PROCESSING succeeds', $event->fresh()->status === 'PROCESSING');

    $event->transitionTo(\App\Models\PaymentEvent::STATUS_RECONCILED);
    $check('PROCESSING -> RECONCILED succeeds', $event->fresh()->status === 'RECONCILED');

    $event->transitionTo(\App\Models\PaymentEvent::STATUS_REVERSED);
    $check('RECONCILED -> REVERSED succeeds', $event->fresh()->status === 'REVERSED');

    // --- Terminal state rejects further transitions ---
    $threwOnTerminal = false;
    try {
        $event->transitionTo(\App\Models\PaymentEvent::STATUS_RECONCILED);
    } catch (\LogicException $e) {
        $threwOnTerminal = true;
    }
    $check('REVERSED (terminal) rejects any further transition', $threwOnTerminal);

    // --- Invalid transition (skipping states) ---
    $eventB = \App\Models\PaymentEvent::create([
        'provider' => 'mpesa', 'channel' => 'mpesa', 'provider_transaction_id' => 'TEST-PE-002',
        'amount' => '500.00', 'raw_payload' => '{}', 'received_at' => now(),
    ]);
    $threwOnSkip = false;
    try {
        $eventB->transitionTo(\App\Models\PaymentEvent::STATUS_RECONCILED); // straight from RECEIVED
    } catch (\LogicException $e) {
        $threwOnSkip = true;
    }
    $check('RECEIVED -> RECONCILED directly (skipping states) throws', $threwOnSkip);

    // --- Verification failure path (terminal) ---
    $eventC = \App\Models\PaymentEvent::create([
        'provider' => 'mpesa', 'channel' => 'mpesa', 'provider_transaction_id' => 'TEST-PE-003',
        'amount' => '750.00', 'raw_payload' => '{}', 'received_at' => now(),
    ]);
    $eventC->transitionTo(\App\Models\PaymentEvent::STATUS_VERIFICATION_FAILED);
    $check('RECEIVED -> VERIFICATION_FAILED succeeds', $eventC->fresh()->status === 'VERIFICATION_FAILED');

    $threwAfterVerificationFailed = false;
    try {
        $eventC->transitionTo(\App\Models\PaymentEvent::STATUS_VERIFIED);
    } catch (\LogicException $e) {
        $threwAfterVerificationFailed = true;
    }
    $check('VERIFICATION_FAILED (terminal) rejects any further transition', $threwAfterVerificationFailed);

    // --- Unmatched -> review -> reconciled path ---
    $eventD = \App\Models\PaymentEvent::create([
        'provider' => 'mpesa', 'channel' => 'mpesa', 'provider_transaction_id' => 'TEST-PE-004',
        'amount' => '1000.00', 'raw_payload' => '{}', 'received_at' => now(),
    ]);
    $eventD->transitionTo(\App\Models\PaymentEvent::STATUS_VERIFIED);
    $eventD->transitionTo(\App\Models\PaymentEvent::STATUS_QUEUED);
    $eventD->transitionTo(\App\Models\PaymentEvent::STATUS_PROCESSING);
    $eventD->transitionTo(\App\Models\PaymentEvent::STATUS_UNMATCHED);
    $check('PROCESSING -> UNMATCHED succeeds', $eventD->fresh()->status === 'UNMATCHED');
    $eventD->transitionTo(\App\Models\PaymentEvent::STATUS_REQUIRES_REVIEW);
    $check('UNMATCHED -> REQUIRES_REVIEW succeeds', $eventD->fresh()->status === 'REQUIRES_REVIEW');
    $eventD->transitionTo(\App\Models\PaymentEvent::STATUS_RECONCILED);
    $check('REQUIRES_REVIEW -> RECONCILED succeeds', $eventD->fresh()->status === 'RECONCILED');

    // --- Failed -> retry (PROCESSING) and Failed -> review ---
    $eventE = \App\Models\PaymentEvent::create([
        'provider' => 'mpesa', 'channel' => 'mpesa', 'provider_transaction_id' => 'TEST-PE-005',
        'amount' => '250.00', 'raw_payload' => '{}', 'received_at' => now(),
    ]);
    $eventE->transitionTo(\App\Models\PaymentEvent::STATUS_VERIFIED);
    $eventE->transitionTo(\App\Models\PaymentEvent::STATUS_QUEUED);
    $eventE->transitionTo(\App\Models\PaymentEvent::STATUS_PROCESSING);
    $eventE->transitionTo(\App\Models\PaymentEvent::STATUS_FAILED);
    $check('PROCESSING -> FAILED succeeds', $eventE->fresh()->status === 'FAILED');
    $eventE->transitionTo(\App\Models\PaymentEvent::STATUS_PROCESSING);
    $check('FAILED -> PROCESSING (retry) succeeds', $eventE->fresh()->status === 'PROCESSING');
    $eventE->transitionTo(\App\Models\PaymentEvent::STATUS_FAILED);
    $eventE->transitionTo(\App\Models\PaymentEvent::STATUS_REQUIRES_REVIEW);
    $check('FAILED -> REQUIRES_REVIEW (giving up after retries) succeeds', $eventE->fresh()->status === 'REQUIRES_REVIEW');

    // --- Payment relationship via payment_event_id FK ---
    $account  = \App\Models\Account::create(['name' => 'Tinker PE Co', 'phone' => '0700000080']);
    $property = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'PE Property', 'type' => 'residential']);
    $unit     = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'PE1', 'type' => 'bedsitter', 'rent_amount' => '1500.00', 'deposit_amount' => '1500.00']);
    $tenant   = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'PE', 'last_name' => 'Test', 'phone' => '0711119999']);
    $lease    = \App\Models\Lease::create(['unit_id' => $unit->id, 'tenant_id' => $tenant->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '1500.00', 'deposit_required' => '1500.00', 'status' => 'active']);

    $payment = \App\Models\Payment::create([
        'account_id'       => $account->id,
        'payment_event_id' => $event->id,
        'lease_id'         => $lease->id,
        'tenant_id'        => $tenant->id,
        'amount'           => '1500.00',
        'payment_date'     => now()->toDateString(),
        'payment_type'     => 'rent',
        'method'           => 'mpesa',
        'reference'        => 'TEST-PE-001',
        'is_allocated'     => true,
    ]);

    $check('Payment created with payment_event_id set', $payment->payment_event_id === $event->id);
    $check('Payment::paymentEvent() relationship resolves', $payment->paymentEvent && $payment->paymentEvent->id === $event->id);
    $check('PaymentEvent::payment() relationship resolves back', $event->fresh()->payment && $event->fresh()->payment->id === $payment->id);

    echo "\n=== Result: {$checks} checks, " . ($checks - $failures) . " passed, {$failures} failed ===\n";

} catch (\Throwable $e) {
    $failures++;
    echo "\n!! EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} finally {
    \Illuminate\Support\Facades\DB::rollBack();
    echo "\nTransaction rolled back, no data left behind.\n";
    if ($failures > 0) {
        echo "\n{$failures} FAILURE(S). Do not consider Chunk 1 verified until this is clean.\n";
    } else {
        echo "\nAll checks passed.\n";
    }
}