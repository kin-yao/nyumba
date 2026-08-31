<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/**
 * Verifies the AuditService fix: three webhook-context call sites
 * (MpesaC2BController's unmatched and reconciled branches, and
 * SubscriptionController's applyPlanUpgrade) now write real AuditLog rows
 * instead of silently failing on the audit_logs.account_id NOT NULL
 * constraint. Runs fully unauthenticated throughout, on purpose, this is
 * exactly the context that was broken.
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

echo "\n=== AuditService fix verification (unauthenticated context throughout) ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account = \App\Models\Account::create(['name' => 'Tinker Audit Fix', 'phone' => '0700000009']);
    $property = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'Audit Fix Property', 'type' => 'residential']);
    $unit = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'X1', 'type' => 'bedsitter', 'rent_amount' => '10000.00', 'deposit_amount' => '10000.00']);
    $tenant = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'Audit', 'last_name' => 'Test', 'phone' => '0733445566']);
    \App\Models\Lease::create(['unit_id' => $unit->id, 'tenant_id' => $tenant->id, 'move_in_date' => now()->subMonth()->toDateString(), 'monthly_rent' => '10000.00', 'deposit_required' => '10000.00', 'status' => 'active']);

    $c2b = app(\App\Http\Controllers\MpesaC2BController::class);

    // --- Unmatched payment: no unit matches this BillRefNumber ---
    $c2b->processTransaction($property, [
        'TransID'           => 'AUDITFIX-UNMATCHED-1',
        'TransAmount'       => '5000.00',
        'BillRefNumber'     => 'NOSUCHUNIT',
        'MSISDN'            => '254712345678',
        'BusinessShortCode' => '000000',
    ]);

    $unmatchedAudit = \App\Models\AuditLog::where('event', 'payment.mpesa_unmatched')
        ->where('subject_type', \App\Models\Property::class)
        ->where('subject_id', $property->id)
        ->latest()->first();

    $check('Unmatched-payment audit row now exists', $unmatchedAudit !== null);
    $check('Unmatched-payment audit row has the correct account_id', $unmatchedAudit && $unmatchedAudit->account_id === $account->id);

    // --- Reconciled payment: matches the unit ---
    $c2b->processTransaction($property, [
        'TransID'           => 'AUDITFIX-MATCHED-1',
        'TransAmount'       => '10000.00',
        'BillRefNumber'     => 'X1',
        'MSISDN'            => '254712345678',
        'BusinessShortCode' => '000000',
    ]);

    $reconciledAudit = \App\Models\AuditLog::where('event', 'payment.mpesa_reconciled')
        ->where('subject_type', \App\Models\Payment::class)
        ->latest()->first();

    $check('Reconciled-payment audit row now exists', $reconciledAudit !== null);
    $check('Reconciled-payment audit row has the correct account_id', $reconciledAudit && $reconciledAudit->account_id === $account->id);
    $check('Reconciled-payment audit row has the correct property_id', $reconciledAudit && $reconciledAudit->property_id === $property->id);

    // --- Subscription upgrade ---
    \App\Models\MpesaTransaction::create([
        'account_id' => $account->id, 'type' => 'subscription', 'plan' => 'starter',
        'billing_cycle' => 'monthly', 'amount' => '3750.00', 'phone' => '254712345678',
        'checkout_request_id' => 'AUDITFIX-SUB-1', 'status' => 'pending',
    ]);

    $subCtrl = app(\App\Http\Controllers\SubscriptionController::class);
    $request = \Illuminate\Http\Request::create('/mpesa/stk/callback', 'POST', [
        'Body' => [
            'stkCallback' => [
                'CheckoutRequestID' => 'AUDITFIX-SUB-1',
                'ResultCode'        => 0,
                'ResultDesc'        => 'The service request is processed successfully.',
                'CallbackMetadata'  => [
                    'Item' => [
                        ['Name' => 'Amount', 'Value' => 3750.0],
                        ['Name' => 'MpesaReceiptNumber', 'Value' => 'AUDITFIXREC1'],
                    ],
                ],
            ],
        ],
    ]);
    $subCtrl->callback($request);

    $subAudit = \App\Models\AuditLog::where('event', 'subscription.upgraded')
        ->where('subject_type', \App\Models\Account::class)
        ->where('subject_id', $account->id)
        ->latest()->first();

    $check('Subscription-upgrade audit row now exists', $subAudit !== null);
    $check('Subscription-upgrade audit row has the correct account_id', $subAudit && $subAudit->account_id === $account->id);
    $check('Subscription-upgrade audit row metadata has months_covered = 1', $subAudit && $subAudit->metadata['months_covered'] === 1);

    echo "\n=== Result: {$checks} checks, " . ($checks - $failures) . " passed, {$failures} failed ===\n";

} catch (\Throwable $e) {
    $failures++;
    echo "\n!! EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} finally {
    \Illuminate\Support\Facades\DB::rollBack();
    echo "\nTransaction rolled back, no data left behind.\n";
    if ($failures > 0) {
        echo "\n{$failures} FAILURE(S).\n";
    } else {
        echo "\nAll checks passed.\n";
    }
}