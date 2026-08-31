<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/**
 * Manual validation for the last three Chunk 2C files: DashboardController,
 * Portal/DashboardController, Portal/PaymentController.
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

echo "\n=== DashboardController / Portal/DashboardController / Portal/PaymentController ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    // ---------- Landlord dashboard ----------
    $account = \App\Models\Account::create(['name' => 'Tinker Dash Co', 'phone' => '0700000020']);
    $user    = \App\Models\User::factory()->create(['account_id' => $account->id, 'role' => 'owner']);
    \Illuminate\Support\Facades\Auth::login($user);

    $property = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'Dash Property', 'type' => 'residential']);

    $unit1 = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'D1', 'type' => 'bedsitter', 'rent_amount' => '5555.55', 'deposit_amount' => '5555.55']);
    $unit2 = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'D2', 'type' => 'bedsitter', 'rent_amount' => '6666.66', 'deposit_amount' => '6666.66']);

    $tenant1 = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'D', 'last_name' => 'One', 'phone' => '0711111101']);
    $tenant2 = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'D', 'last_name' => 'Two', 'phone' => '0711111102']);

    $lease1 = \App\Models\Lease::create(['unit_id' => $unit1->id, 'tenant_id' => $tenant1->id, 'move_in_date' => now()->subMonths(2)->toDateString(), 'monthly_rent' => '5555.55', 'deposit_required' => '5555.55', 'status' => 'active']);
    $lease2 = \App\Models\Lease::create(['unit_id' => $unit2->id, 'tenant_id' => $tenant2->id, 'move_in_date' => now()->subMonths(2)->toDateString(), 'monthly_rent' => '6666.66', 'deposit_required' => '6666.66', 'status' => 'active']);

    \App\Models\Invoice::create(['account_id' => $account->id, 'lease_id' => $lease1->id, 'reference' => 'DASH-INV-1', 'period_month' => now()->month, 'period_year' => now()->year, 'invoice_date' => now()->toDateString(), 'due_date' => now()->addDays(5)->toDateString(), 'total_amount' => '5555.55', 'amount_paid' => '2222.22', 'status' => 'partial']);
    \App\Models\Invoice::create(['account_id' => $account->id, 'lease_id' => $lease2->id, 'reference' => 'DASH-INV-2', 'period_month' => now()->month, 'period_year' => now()->year, 'invoice_date' => now()->toDateString(), 'due_date' => now()->addDays(5)->toDateString(), 'total_amount' => '6666.66', 'amount_paid' => '6666.66', 'status' => 'paid']);

    \App\Models\Payment::create(['account_id' => $account->id, 'lease_id' => $lease1->id, 'tenant_id' => $tenant1->id, 'amount' => '2222.22', 'payment_date' => now()->toDateString(), 'payment_type' => 'rent', 'method' => 'mpesa', 'reference' => 'DASH-PAY-1', 'is_allocated' => true]);
    \App\Models\Payment::create(['account_id' => $account->id, 'lease_id' => $lease2->id, 'tenant_id' => $tenant2->id, 'amount' => '6666.66', 'payment_date' => now()->toDateString(), 'payment_type' => 'rent', 'method' => 'cash', 'reference' => 'DASH-PAY-2', 'is_allocated' => true]);

    \App\Models\Expense::create(['account_id' => $account->id, 'property_id' => $property->id, 'category' => 'repairs', 'description' => 'Fix', 'amount' => '333.33', 'payment_method' => 'cash', 'expense_date' => now()->toDateString()]);

    $dashResponse = app(\App\Http\Controllers\DashboardController::class)->index();
    $dashData     = $dashResponse->getData();

    $check('Dashboard: expectedThisMonth === 12222.21', $dashData['expectedThisMonth'] === 12222.21);
    $check('Dashboard: collectedThisMonth === 8888.88', $dashData['collectedThisMonth'] === 8888.88);
    $check('Dashboard: outstandingThisMonth === 3333.33', abs($dashData['outstandingThisMonth'] - 3333.33) < 0.001);
    $check('Dashboard: collectionRate === 73.0', $dashData['collectionRate'] === 73.0);
    $check('Dashboard: totalOutstanding === 3333.33', abs($dashData['totalOutstanding'] - 3333.33) < 0.001);
    $check('Dashboard: paymentsThisMonth === 8888.88', $dashData['paymentsThisMonth'] === 8888.88);
    $check('Dashboard: expensesThisMonth === 333.33', $dashData['expensesThisMonth'] === 333.33);
    $check('Dashboard: netProfitThisMonth === 8555.55', abs($dashData['netProfitThisMonth'] - 8555.55) < 0.001);
    $check('Dashboard: only 1 tenant has a positive balance', $dashData['tenantsWithBalance']->count() === 1);
    $check('Dashboard: that balance === 3333.33', abs($dashData['tenantsWithBalance']->first()['balance'] - 3333.33) < 0.001);

    $currentMonthChart = collect($dashData['chartData'])->firstWhere('label', now()->format('M Y'));
    $check('Dashboard: current month chart profit === 8555.55', abs($currentMonthChart['profit'] - 8555.55) < 0.001);

    // ---------- Tenant portal dashboard ----------
    \Illuminate\Support\Facades\Auth::logout();

    $tenantPortalD = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'Portal', 'last_name' => 'D', 'phone' => '0722222201']);
    $unitPD = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'PD1', 'type' => 'bedsitter', 'rent_amount' => '1111.11', 'deposit_amount' => '1111.11']);
    $leasePD = \App\Models\Lease::create(['unit_id' => $unitPD->id, 'tenant_id' => $tenantPortalD->id, 'move_in_date' => now()->subMonths(2)->toDateString(), 'monthly_rent' => '1111.11', 'deposit_required' => '1111.11', 'status' => 'active']);

    $invoicePD1 = \App\Models\Invoice::create(['account_id' => $account->id, 'lease_id' => $leasePD->id, 'reference' => 'PORTAL-D-INV-1', 'period_month' => now()->month, 'period_year' => now()->year, 'invoice_date' => now()->toDateString(), 'due_date' => now()->addDays(5)->toDateString(), 'total_amount' => '1111.11', 'status' => 'sent']);
    $invoicePD2 = \App\Models\Invoice::create(['account_id' => $account->id, 'lease_id' => $leasePD->id, 'reference' => 'PORTAL-D-INV-2', 'period_month' => now()->subMonth()->month, 'period_year' => now()->subMonth()->year, 'invoice_date' => now()->subMonth()->toDateString(), 'due_date' => now()->addDays(5)->toDateString(), 'total_amount' => '2222.22', 'status' => 'sent']);

    \App\Models\InvoiceLineItem::create(['invoice_id' => $invoicePD1->id, 'description' => 'Rent', 'quantity' => '1.00', 'unit_price' => '1111.11', 'amount' => '1111.11', 'type' => 'rent']);
    \App\Models\InvoiceLineItem::create(['invoice_id' => $invoicePD2->id, 'description' => 'Rent', 'quantity' => '1.00', 'unit_price' => '2222.22', 'amount' => '2222.22', 'type' => 'rent']);

    \App\Models\Payment::create(['account_id' => $account->id, 'lease_id' => $leasePD->id, 'tenant_id' => $tenantPortalD->id, 'amount' => '1111.11', 'payment_date' => now()->toDateString(), 'payment_type' => 'rent', 'method' => 'mpesa', 'reference' => 'PORTAL-D-PAY-1', 'is_allocated' => true]);
    \App\Models\Payment::create(['account_id' => $account->id, 'lease_id' => $leasePD->id, 'tenant_id' => $tenantPortalD->id, 'amount' => '500.00', 'payment_date' => now()->toDateString(), 'payment_type' => 'deposit', 'method' => 'mpesa', 'reference' => 'PORTAL-D-PAY-DEP', 'is_allocated' => true]);

    session(['portal_tenant_id' => $tenantPortalD->id]);

    $portalDashResponse = app(\App\Http\Controllers\Portal\DashboardController::class)->index();
    $portalDashData     = $portalDashResponse->getData();

    $check('Portal dashboard: balance === 2222.22 (1111.11 + 2222.22 charged - 1111.11 rent paid, deposit excluded)', abs($portalDashData['balance'] - 2222.22) < 0.001);

    // ---------- Tenant portal payment page ----------
    $portalPayResponse = app(\App\Http\Controllers\Portal\PaymentController::class)->index();
    $portalPayData     = $portalPayResponse->getData();

    $check('Portal payment: balance === 2222.22 (same calc, different method)', abs($portalPayData['balance'] - 2222.22) < 0.001);
    $check('Portal payment: depositPaid === 0.0 (no deposit_paid column set on lease, only a Payment row)', $portalPayData['depositPaid'] === 0.0);
    $check('Portal payment: depositRequired === 1111.11', $portalPayData['depositRequired'] === 1111.11);

    $ledgerFlat = collect($portalPayData['ledgerByMonth'])->flatten(1);
    $check('Portal payment: ledger has 4 entries (2 charges from line items + 2 payments)', $ledgerFlat->count() === 4);
    $chargeEntries = $ledgerFlat->whereNotNull('charged');
    $check('Portal payment: charge entries sum to 3333.33 (1111.11 + 2222.22)', abs($chargeEntries->sum('charged') - 3333.33) < 0.001);

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