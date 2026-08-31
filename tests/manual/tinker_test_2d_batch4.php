<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/**
 * Manual validation for TenantController::show() (ledger balance) and
 * transfer() (deposit carry-forward), the write path that sets a new
 * lease's actual deposit_paid value.
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

echo "\n=== TenantController: show() / transfer() ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account = \App\Models\Account::create(['name' => 'Tinker Tenant Co', 'phone' => '0700000060']);
    $user    = \App\Models\User::factory()->create(['account_id' => $account->id, 'role' => 'owner']);
    \Illuminate\Support\Facades\Auth::login($user);

    $property = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'Tenant Property', 'type' => 'residential']);
    $unitA = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'TA', 'type' => 'bedsitter', 'rent_amount' => '7777.77', 'deposit_amount' => '7777.77', 'status' => 'occupied']);
    $unitB = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'TB', 'type' => 'bedsitter', 'rent_amount' => '8888.88', 'deposit_amount' => '8888.88', 'status' => 'vacant']);

    $tenant = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'Trans', 'last_name' => 'Fer', 'phone' => '0755550001']);
    $lease  = \App\Models\Lease::create(['unit_id' => $unitA->id, 'tenant_id' => $tenant->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '7777.77', 'deposit_required' => '7777.77', 'deposit_paid' => '6666.67', 'status' => 'active']);

    // --- show() balance ---
    \App\Models\Invoice::create(['account_id' => $account->id, 'lease_id' => $lease->id, 'reference' => 'TEN-INV-1', 'period_month' => 8, 'period_year' => 2026, 'invoice_date' => '2026-08-01', 'due_date' => '2026-08-05', 'total_amount' => '7777.77', 'status' => 'sent']);
    \App\Models\InvoiceLineItem::create(['invoice_id' => \App\Models\Invoice::where('lease_id', $lease->id)->first()->id, 'description' => 'Rent', 'quantity' => '1.00', 'unit_price' => '7777.77', 'amount' => '7777.77', 'type' => 'rent']);
    \App\Models\Payment::create(['account_id' => $account->id, 'lease_id' => $lease->id, 'tenant_id' => $tenant->id, 'amount' => '3333.33', 'payment_date' => '2026-08-10', 'payment_type' => 'rent', 'method' => 'mpesa', 'reference' => 'TEN-PAY-1', 'is_allocated' => true]);
    \App\Models\Payment::create(['account_id' => $account->id, 'lease_id' => $lease->id, 'tenant_id' => $tenant->id, 'amount' => '6666.67', 'payment_date' => '2026-06-05', 'payment_type' => 'deposit', 'method' => 'mpesa', 'reference' => 'TEN-PAY-DEP', 'is_allocated' => true]);

    $tenantCtrl = app(\App\Http\Controllers\TenantController::class);
    $showResponse = $tenantCtrl->show($tenant);
    $showData     = $showResponse->getData();

    $check('show(): balance === 4444.44 (7777.77 charged - 3333.33 rent paid, deposit excluded)', abs($showData['balance'] - 4444.44) < 0.001);

    // --- transfer() with deposit carry-forward ---
    $transferRequest = \Illuminate\Http\Request::create('/tenants/' . $tenant->id . '/transfer', 'POST', [
        'new_unit_id'      => $unitB->id,
        'transfer_date'    => '2026-08-15',
        'new_monthly_rent' => '8888.88',
        'deposit_action'   => 'carry_forward',
        'notes'            => '',
    ]);
    $tenantCtrl->transfer($transferRequest, $tenant);

    $newLease = \App\Models\Lease::where('unit_id', $unitB->id)->where('tenant_id', $tenant->id)->where('status', 'active')->first();
    $check('transfer(): new lease created on unit B', $newLease !== null);
    $check('transfer(): deposit_paid carried forward === 6666.67', $newLease && $newLease->deposit_paid === '6666.67');
    $check('transfer(): deposit_required === unit B deposit_amount (8888.88)', $newLease && $newLease->deposit_required === '8888.88');

    $carryPayment = \App\Models\Payment::where('lease_id', $newLease->id)->where('payment_type', 'deposit')->first();
    $check('transfer(): a deposit Payment record was created on the new lease', $carryPayment !== null);
    $check('transfer(): carried deposit payment amount === 6666.67', $carryPayment && $carryPayment->amount === '6666.67');

    echo "\n=== Result: {$checks} checks, " . ($checks - $failures) . " passed, {$failures} failed ===\n";

} catch (\Throwable $e) {
    $failures++;
    echo "\n!! EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} finally {
    \Illuminate\Support\Facades\DB::rollBack();
    echo "\nTransaction rolled back, no data left behind.\n";
    if ($failures > 0) {
        echo "\n{$failures} FAILURE(S). Do not consider this batch verified until this is clean.\n";
    } else {
        echo "\nAll checks passed.\n";
    }
}