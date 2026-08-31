<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/**
 * Manual validation for the public utilities(), rentRoll(), and
 * outstanding() report methods. These call filteredPropertyIds()/
 * filteredUnitIds() internally, which need an authenticated owner-role
 * user to return anything, so this logs one in (unlike the reconciliation
 * webhook tests, which deliberately ran unauthenticated).
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

echo "\n=== ReportController: utilities() / rentRoll() / outstanding() ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account = \App\Models\Account::create(['name' => 'Tinker Report Co 3', 'phone' => '0700000012']);
    $user    = \App\Models\User::factory()->create(['account_id' => $account->id, 'role' => 'owner']);
    \Illuminate\Support\Facades\Auth::login($user);

    $property = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'Report Property 3', 'type' => 'residential']);

    $rate = \App\Models\UtilityRate::create([
        'property_id' => $property->id, 'name' => 'Electricity', 'type' => 'electricity',
        'amount' => '20.00', 'billing_type' => 'per_unit', 'active' => true,
    ]);

    $unitA = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'A', 'type' => 'bedsitter', 'rent_amount' => '7500.25', 'deposit_amount' => '7500.25']);
    $unitB = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'B', 'type' => 'bedsitter', 'rent_amount' => '12499.75', 'deposit_amount' => '12499.75']);

    $tenantA = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'A', 'last_name' => 'Tenant', 'phone' => '0788888881']);
    $tenantB = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'B', 'last_name' => 'Tenant', 'phone' => '0788888882']);

    $leaseA = \App\Models\Lease::create(['unit_id' => $unitA->id, 'tenant_id' => $tenantA->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '7500.25', 'deposit_required' => '7500.25', 'status' => 'active']);
    $leaseB = \App\Models\Lease::create(['unit_id' => $unitB->id, 'tenant_id' => $tenantB->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '12499.75', 'deposit_required' => '12499.75', 'status' => 'active']);

    // Only unit A has an invoice for August — unit B falls back to monthly_rent
    \App\Models\Invoice::create(['account_id' => $account->id, 'lease_id' => $leaseA->id, 'reference' => 'PUB-INV-A', 'period_month' => 8, 'period_year' => 2026, 'invoice_date' => '2026-08-01', 'due_date' => '2026-08-05', 'total_amount' => '7500.25', 'amount_paid' => '2500.10', 'status' => 'partial']);

    \App\Models\Payment::create(['account_id' => $account->id, 'lease_id' => $leaseA->id, 'tenant_id' => $tenantA->id, 'amount' => '2500.10', 'payment_date' => '2026-08-10', 'payment_type' => 'rent', 'method' => 'mpesa', 'reference' => 'PUB-PAY-A', 'is_allocated' => true]);
    \App\Models\Payment::create(['account_id' => $account->id, 'lease_id' => $leaseA->id, 'tenant_id' => $tenantA->id, 'amount' => '1000.00', 'payment_date' => '2026-08-03', 'payment_type' => 'deposit', 'method' => 'mpesa', 'reference' => 'PUB-PAY-A-DEP', 'is_allocated' => true]);
    // Unit B's lease has no invoice and no payment at all, so it should
    // fall out of outstanding() entirely (balance = 0, filtered out).

    \App\Models\UtilityReading::create(['unit_id' => $unitA->id, 'account_id' => $account->id, 'utility_type' => 'electricity', 'reading_month' => 8, 'reading_year' => 2026, 'previous_reading' => '0.00', 'current_reading' => '22.84', 'units_consumed' => '22.84', 'rate_per_unit' => '20.00', 'charge_amount' => '456.78']);
    \App\Models\UtilityReading::create(['unit_id' => $unitB->id, 'account_id' => $account->id, 'utility_type' => 'electricity', 'reading_month' => 8, 'reading_year' => 2026, 'previous_reading' => '0.00', 'current_reading' => '39.45', 'units_consumed' => '39.45', 'rate_per_unit' => '20.00', 'charge_amount' => '789.01']);

    $reportCtrl = app(\App\Http\Controllers\ReportController::class);

    // --- utilities() ---
    $utilitiesResponse = $reportCtrl->utilities(\Illuminate\Http\Request::create('/reports/utilities', 'GET', ['month' => 8, 'year' => 2026]));
    $utilitiesData     = $utilitiesResponse->getData();

    $check('utilities(): grandTotal === 1245.79 (456.78 + 789.01)', $utilitiesData['grandTotal'] === 1245.79);
    $check('utilities(): Electricity total === 1245.79', $utilitiesData['totalsByType']['Electricity'] === 1245.79);
    $check('utilities(): missingCount === 0', $utilitiesData['missingCount'] === 0);
    $check('utilities(): 2 rows', count($utilitiesData['rows']) === 2);

    // --- rentRoll() ---
    $rentRollResponse = $reportCtrl->rentRoll(\Illuminate\Http\Request::create('/reports/rent-roll', 'GET', ['month' => 8, 'year' => 2026]));
    $rentRollData      = $rentRollResponse->getData();

    $check('rentRoll(): totalExpected === 20000.00 (7500.25 invoiced + 12499.75 fallback)', $rentRollData['totalExpected'] === 20000.00);
    $check('rentRoll(): totalCollected === 2500.10', $rentRollData['totalCollected'] === 2500.10);
    $check('rentRoll(): totalOutstanding === 17499.90', $rentRollData['totalOutstanding'] === 17499.90);
    $check('rentRoll(): collectionRate === 12.5', $rentRollData['collectionRate'] === 12.5);

    // --- outstanding() ---
    $outstandingResponse = $reportCtrl->outstanding();
    $outstandingData     = $outstandingResponse->getData();

    $check('outstanding(): only 1 lease has a positive balance (B has none at all)', $outstandingData['leases']->count() === 1);
    $check('outstanding(): balance === 5000.15 (7500.25 charged - 2500.10 paid, deposit excluded)', abs($outstandingData['leases']->first()['balance'] - 5000.15) < 0.001);
    $check('outstanding(): totalOutstanding === 5000.15', abs($outstandingData['totalOutstanding'] - 5000.15) < 0.001);

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