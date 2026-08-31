<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/**
 * Manual validation for Chunk 2B: decimal-safe money in SubscriptionController.
 * Covers Money::div() and applyPlanUpgrade()'s months-covered calculation
 * across the starter and growth pricing bands, a yearly cycle, and the two
 * boundary cases that would have broken under naive float division: paying
 * exactly N months (must credit exactly N, not N-1) and paying one cent
 * short of N months (must credit N-1, not N).
 *
 * Verified via Account::plan_expires_at and sms_credits directly, NOT via
 * AuditLog — AuditService::log() sets account_id from the authenticated
 * user, and audit_logs.account_id is NOT NULL, so calling it from an
 * unauthenticated context (this callback, same as production) throws and
 * gets silently swallowed. That's a separate real bug, tracked, not fixed
 * here, and this script routes around it rather than depending on it.
 *
 * initiate() itself is not tested here since it calls Safaricom's live STK
 * push API — this only exercises callback() -> applyPlanUpgrade(), which is
 * pure DB logic with no external calls.
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

echo "\n=== Part 1: Money::div() (no DB) ===\n";

$check('Money::div("7500.00", "3750.00") truncates to "2." range (>=2, <3)', (float) \App\Support\Money::div('7500.00', '3750.00') >= 2.0 && (float) \App\Support\Money::div('7500.00', '3750.00') < 3.0);
$check('(int) Money::div("7500.00", "3750.00") === 2 exactly', (int) \App\Support\Money::div('7500.00', '3750.00') === 2);
$check('(int) Money::div("7499.99", "3750.00") === 1 (one cent short must NOT round up)', (int) \App\Support\Money::div('7499.99', '3750.00') === 1);

echo "\n=== Part 2: SubscriptionController::callback() -> applyPlanUpgrade() ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $subCtrl = app(\App\Http\Controllers\SubscriptionController::class);

    $runCallback = function (string $checkoutId, string $mpesaReceipt, string $amount) use ($subCtrl) {
        $request = \Illuminate\Http\Request::create('/mpesa/stk/callback', 'POST', [
            'Body' => [
                'stkCallback' => [
                    'CheckoutRequestID' => $checkoutId,
                    'ResultCode'        => 0,
                    'ResultDesc'        => 'The service request is processed successfully.',
                    'CallbackMetadata'  => [
                        'Item' => [
                            ['Name' => 'Amount', 'Value' => (float) $amount],
                            ['Name' => 'MpesaReceiptNumber', 'Value' => $mpesaReceipt],
                        ],
                    ],
                ],
            ],
        ]);
        return $subCtrl->callback($request);
    };

    $expiresOn = function (int $daysFromNow) {
        return now()->addDays($daysFromNow)->toDateString();
    };

    // --- Scenario A: starter band (1 unit), exact 1-month payment ---
    $accountA = \App\Models\Account::create(['name' => 'Tinker Sub A', 'phone' => '0700000001']);
    $propA    = \App\Models\Property::create(['account_id' => $accountA->id, 'name' => 'Prop A', 'type' => 'residential']);
    \App\Models\Unit::create(['property_id' => $propA->id, 'name' => 'A1', 'type' => 'bedsitter', 'rent_amount' => '10000.00', 'deposit_amount' => '10000.00']);

    $check('Account A has 1 unit (starter band)', $accountA->currentUnitCount() === 1);
    $pricingA = \App\Models\Account::priceForUnitCount(1);
    $check('Starter monthly price is 3750 (whole shilling, as expected)', $pricingA['monthly'] === 3750);

    \App\Models\MpesaTransaction::create([
        'account_id' => $accountA->id, 'type' => 'subscription', 'plan' => 'starter',
        'billing_cycle' => 'monthly', 'amount' => '3750.00', 'phone' => '254712345678',
        'checkout_request_id' => 'TINKER-CO-A1', 'status' => 'pending',
    ]);
    $runCallback('TINKER-CO-A1', 'TINKREC0001', '3750.00');

    $check('Scenario A: exact 1-month payment -> plan_expires_at is 30 days out', $accountA->fresh()->plan_expires_at->toDateString() === $expiresOn(30));
    $check('Scenario A: plan set to "starter"', $accountA->fresh()->plan === 'starter');
    $check('Scenario A: 300 SMS credits added (starter allowance x 1 month)', $accountA->fresh()->sms_credits === 300);

    // --- Scenario B: same account, exact 2-month payment (the classic
    // float-epsilon boundary: must land on exactly 2, never 1) ---
    \App\Models\MpesaTransaction::create([
        'account_id' => $accountA->id, 'type' => 'subscription', 'plan' => 'starter',
        'billing_cycle' => 'monthly', 'amount' => '7500.00', 'phone' => '254712345678',
        'checkout_request_id' => 'TINKER-CO-A2', 'status' => 'pending',
    ]);
    $runCallback('TINKER-CO-A2', 'TINKREC0002', '7500.00');

    $check('Scenario B: exact 2x monthly payment -> plan_expires_at is 60 days out (not 30)', $accountA->fresh()->plan_expires_at->toDateString() === $expiresOn(60));
    $check('Scenario B: 600 more SMS credits added (300 + 600 = 900 running total)', $accountA->fresh()->sms_credits === 900);

    // --- Scenario C: same account, one cent short of 2 months (must credit
    // only 1, never round up) ---
    \App\Models\MpesaTransaction::create([
        'account_id' => $accountA->id, 'type' => 'subscription', 'plan' => 'starter',
        'billing_cycle' => 'monthly', 'amount' => '7499.99', 'phone' => '254712345678',
        'checkout_request_id' => 'TINKER-CO-A3', 'status' => 'pending',
    ]);
    $runCallback('TINKER-CO-A3', 'TINKREC0003', '7499.99');

    $check('Scenario C: one cent short of 2 months -> plan_expires_at is 30 days out (not 60)', $accountA->fresh()->plan_expires_at->toDateString() === $expiresOn(30));

    // --- Scenario D: growth band (100 units), exact 3-month payment ---
    $accountD = \App\Models\Account::create(['name' => 'Tinker Sub D', 'phone' => '0700000002']);
    $propD    = \App\Models\Property::create(['account_id' => $accountD->id, 'name' => 'Prop D', 'type' => 'residential']);

    $unitsData = [];
    for ($i = 1; $i <= 100; $i++) {
        $unitsData[] = [
            'property_id' => $propD->id, 'name' => "D{$i}", 'type' => 'bedsitter',
            'rent_amount' => '10000.00', 'deposit_amount' => '10000.00', 'status' => 'vacant',
            'created_at' => now(), 'updated_at' => now(),
        ];
    }
    \Illuminate\Support\Facades\DB::table('units')->insert($unitsData);

    $check('Account D has 100 units (growth band)', $accountD->currentUnitCount() === 100);
    $pricingD = \App\Models\Account::priceForUnitCount(100);
    $check('Growth monthly price is 100 x 50 = 5000', $pricingD['monthly'] === 5000);

    \App\Models\MpesaTransaction::create([
        'account_id' => $accountD->id, 'type' => 'subscription', 'plan' => 'growth',
        'billing_cycle' => 'monthly', 'amount' => '15000.00', 'phone' => '254712345679',
        'checkout_request_id' => 'TINKER-CO-D1', 'status' => 'pending',
    ]);
    $runCallback('TINKER-CO-D1', 'TINKREC0004', '15000.00');

    $check('Scenario D: 15000 at 5000/month -> plan_expires_at is 90 days out', $accountD->fresh()->plan_expires_at->toDateString() === $expiresOn(90));
    $check('Scenario D: plan set to "growth"', $accountD->fresh()->plan === 'growth');
    $check('Scenario D: 1200 SMS credits added (100 units x 4/unit x 3 months)', $accountD->fresh()->sms_credits === 1200);

    // --- Scenario E: yearly cycle always 12 months / 365 days, regardless
    // of the amount actually paid ---
    $accountE = \App\Models\Account::create(['name' => 'Tinker Sub E', 'phone' => '0700000003']);
    $propE    = \App\Models\Property::create(['account_id' => $accountE->id, 'name' => 'Prop E', 'type' => 'residential']);
    \App\Models\Unit::create(['property_id' => $propE->id, 'name' => 'E1', 'type' => 'bedsitter', 'rent_amount' => '10000.00', 'deposit_amount' => '10000.00']);

    \App\Models\MpesaTransaction::create([
        'account_id' => $accountE->id, 'type' => 'subscription', 'plan' => 'starter',
        'billing_cycle' => 'yearly', 'amount' => '41250.00', 'phone' => '254712345680',
        'checkout_request_id' => 'TINKER-CO-E1', 'status' => 'pending',
    ]);
    $runCallback('TINKER-CO-E1', 'TINKREC0005', '41250.00');

    $check('Scenario E: yearly cycle -> plan_expires_at is 365 days out', $accountE->fresh()->plan_expires_at->toDateString() === $expiresOn(365));

    echo "\n=== Result: {$checks} checks, " . ($checks - $failures) . " passed, {$failures} failed ===\n";

} catch (\Throwable $e) {
    $failures++;
    echo "\n!! EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} finally {
    \Illuminate\Support\Facades\DB::rollBack();
    echo "\nTransaction rolled back, no data left behind.\n";
    if ($failures > 0) {
        echo "\n{$failures} FAILURE(S). Do not consider Chunk 2B verified until this is clean.\n";
    } else {
        echo "\nAll checks passed.\n";
    }
}