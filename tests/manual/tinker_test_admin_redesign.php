<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$failures = 0;
$checks   = 0;
$check = function (string $label, bool $condition) use (&$failures, &$checks) {
    $checks++;
    echo ($condition ? "  PASS  " : "  FAIL  ") . $label . "\n";
    if (!$condition) $failures++;
};

echo "\n=== Admin page redesign: reconciliation health metrics ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account = \App\Models\Account::create(['name' => 'Admin Redesign Co', 'phone' => '0700000180']);
    $adminUser = \App\Models\User::factory()->create(['account_id' => $account->id, 'role' => 'owner', 'is_admin' => true]);
    \Illuminate\Support\Facades\Auth::login($adminUser);

    $p1 = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'Channel Property', 'type' => 'residential', 'kcb_account_number' => '111']);
    $p2 = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'No Channel Property', 'type' => 'residential']);
    $unit1 = \App\Models\Unit::create(['property_id' => $p1->id, 'name' => 'U1', 'type' => 'bedsitter', 'rent_amount' => '3000.00', 'deposit_amount' => '3000.00']);
    $tenant1 = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'T', 'last_name' => 'One', 'phone' => '0700070001']);
    $lease1 = \App\Models\Lease::create(['unit_id' => $unit1->id, 'tenant_id' => $tenant1->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '3000.00', 'deposit_required' => '3000.00', 'status' => 'active']);

    // 3 auto-matched mpesa payments this month
    for ($i = 1; $i <= 3; $i++) {
        \App\Models\Payment::create(['account_id' => $account->id, 'lease_id' => $lease1->id, 'tenant_id' => $tenant1->id, 'amount' => '1000.00', 'payment_date' => now()->subDays($i)->toDateString(), 'payment_type' => 'rent', 'method' => 'mpesa', 'reference' => 'MATCHED' . $i, 'is_allocated' => true]);
    }
    // 1 recent unmatched mpesa payment
    \App\Models\Payment::create(['account_id' => $account->id, 'amount' => '500.00', 'payment_date' => now()->subDays(1)->toDateString(), 'payment_type' => 'rent', 'method' => 'mpesa', 'reference' => 'UNMATCHED-RECENT', 'is_allocated' => false]);
    // 1 old unmatched mpesa payment (outside the 30-day match-rate window)
    \App\Models\Payment::create(['account_id' => $account->id, 'amount' => '700.00', 'payment_date' => now()->subDays(60)->toDateString(), 'payment_type' => 'rent', 'method' => 'mpesa', 'reference' => 'UNMATCHED-OLD', 'is_allocated' => false]);
    // 1 manual cash payment, matched — should NOT count toward auto match rate
    \App\Models\Payment::create(['account_id' => $account->id, 'lease_id' => $lease1->id, 'tenant_id' => $tenant1->id, 'amount' => '200.00', 'payment_date' => now()->subDays(1)->toDateString(), 'payment_type' => 'rent', 'method' => 'cash', 'reference' => 'CASH1', 'is_allocated' => true]);

    $adminCtrl = app(\App\Http\Controllers\AdminController::class);
    $response  = $adminCtrl->showAccount($account);
    $data      = $response->getData();

    $check('propertiesWithChannel === 1 (only P1 has KCB configured)', $data['propertiesWithChannel'] === 1);
    $check('matchRate === 75 (3 matched of 4 auto payments in last 30 days, cash excluded)', $data['matchRate'] === 75);
    $check('unmatchedCount === 2 (both recent and old, not time-scoped)', $data['unmatchedCount'] === 2);
    $check('unmatchedPayments includes the old one too', $data['unmatchedPayments']->pluck('reference')->contains('UNMATCHED-OLD'));
    $check('unmatchedPayments includes the recent one', $data['unmatchedPayments']->pluck('reference')->contains('UNMATCHED-RECENT'));

    echo "\n=== Result: {$checks} checks, " . ($checks - $failures) . " passed, {$failures} failed ===\n";

} catch (\Throwable $e) {
    $failures++;
    echo "\n!! EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
} finally {
    \Illuminate\Support\Facades\DB::rollBack();
    echo "Rolled back, no data left behind.\n";
    echo $failures > 0 ? "\n{$failures} FAILURE(S).\n" : "\nAll checks passed.\n";
}