<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/**
 * Manual validation for utilitiesForPeriod() and yearlySummary(), the last
 * two private helpers behind ReportController::download().
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

$callPrivate = function (object $obj, string $method, array $args) {
    $ref = new \ReflectionMethod($obj, $method);
    $ref->setAccessible(true);
    return $ref->invoke($obj, ...$args);
};

echo "\n=== ReportController: utilitiesForPeriod / yearlySummary ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account  = \App\Models\Account::create(['name' => 'Tinker Report Co 2', 'phone' => '0700000011']);
    $property = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'Report Property 2', 'type' => 'residential']);

    // --- utilitiesForPeriod fixture ---
    $rate = \App\Models\UtilityRate::create([
        'property_id' => $property->id, 'name' => 'Water', 'type' => 'water',
        'amount' => '50.00', 'billing_type' => 'per_meter_reading', 'active' => true,
    ]);

    $v1 = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'V1', 'type' => 'bedsitter', 'rent_amount' => '10000.00', 'deposit_amount' => '10000.00']);
    $v2 = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'V2', 'type' => 'bedsitter', 'rent_amount' => '10000.00', 'deposit_amount' => '10000.00']);
    $v3 = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'V3', 'type' => 'bedsitter', 'rent_amount' => '10000.00', 'deposit_amount' => '10000.00']);

    $tv1 = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'V', 'last_name' => 'One', 'phone' => '0744444444']);
    $tv2 = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'V', 'last_name' => 'Two', 'phone' => '0755555555']);
    $tv3 = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'V', 'last_name' => 'Three', 'phone' => '0766666666']);

    \App\Models\Lease::create(['unit_id' => $v1->id, 'tenant_id' => $tv1->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '10000.00', 'deposit_required' => '10000.00', 'status' => 'active']);
    \App\Models\Lease::create(['unit_id' => $v2->id, 'tenant_id' => $tv2->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '10000.00', 'deposit_required' => '10000.00', 'status' => 'active']);
    \App\Models\Lease::create(['unit_id' => $v3->id, 'tenant_id' => $tv3->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '10000.00', 'deposit_required' => '10000.00', 'status' => 'active']);

    \App\Models\UtilityReading::create(['unit_id' => $v1->id, 'account_id' => $account->id, 'utility_type' => 'water', 'reading_month' => 8, 'reading_year' => 2026, 'previous_reading' => '100.00', 'current_reading' => '150.55', 'units_consumed' => '50.55', 'rate_per_unit' => '50.00', 'charge_amount' => '2527.50']);
    \App\Models\UtilityReading::create(['unit_id' => $v2->id, 'account_id' => $account->id, 'utility_type' => 'water', 'reading_month' => 8, 'reading_year' => 2026, 'previous_reading' => '200.00', 'current_reading' => '233.33', 'units_consumed' => '33.33', 'rate_per_unit' => '50.00', 'charge_amount' => '1666.50']);
    // V3 deliberately has no reading for this period, to test the "missing" count

    $reportCtrl = app(\App\Http\Controllers\ReportController::class);

    $utilities = $callPrivate($reportCtrl, 'utilitiesForPeriod', [$property, [$v1->id, $v2->id, $v3->id], 8, 2026]);

    $check('utilitiesForPeriod: 2 rows (V1, V2 have readings)', count($utilities['rows']) === 2);
    $check('utilitiesForPeriod: totalCharge === 4194.00 (2527.50 + 1666.50)', $utilities['totalCharge'] === 4194.00);
    $check('utilitiesForPeriod: missing === 1 (V3 has no reading)', $utilities['missing'] === 1);
    $check('utilitiesForPeriod: consumed reading for V1 preserved as a plain reading value (not money)', abs($utilities['rows'][0]['consumed'] - 50.55) < 0.001);

    // --- yearlySummary fixture: data only in August, everything else should be zero ---
    $y1 = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'Y1', 'type' => 'bedsitter', 'rent_amount' => '8888.88', 'deposit_amount' => '8888.88']);
    $ty1 = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'Y', 'last_name' => 'One', 'phone' => '0777777777']);
    $leaseY1 = \App\Models\Lease::create(['unit_id' => $y1->id, 'tenant_id' => $ty1->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '8888.88', 'deposit_required' => '8888.88', 'status' => 'active']);

    \App\Models\Invoice::create(['account_id' => $account->id, 'lease_id' => $leaseY1->id, 'reference' => 'YR-INV-1', 'period_month' => 8, 'period_year' => 2026, 'invoice_date' => '2026-08-01', 'due_date' => '2026-08-05', 'total_amount' => '8888.88', 'amount_paid' => '4444.44', 'status' => 'partial']);
    \App\Models\Payment::create(['account_id' => $account->id, 'lease_id' => $leaseY1->id, 'tenant_id' => $ty1->id, 'amount' => '4444.44', 'payment_date' => '2026-08-10', 'payment_type' => 'rent', 'method' => 'mpesa', 'reference' => 'YR-PAY-1', 'is_allocated' => true]);
    \App\Models\Expense::create(['account_id' => $account->id, 'property_id' => $property->id, 'category' => 'other', 'description' => 'Misc', 'amount' => '555.55', 'payment_method' => 'cash', 'expense_date' => '2026-08-20']);
    \App\Models\UtilityReading::create(['unit_id' => $y1->id, 'account_id' => $account->id, 'utility_type' => 'electricity', 'reading_month' => 8, 'reading_year' => 2026, 'previous_reading' => '0.00', 'current_reading' => '10.00', 'units_consumed' => '10.00', 'rate_per_unit' => '33.33', 'charge_amount' => '333.33']);

    $yearly = $callPrivate($reportCtrl, 'yearlySummary', [$property, [$leaseY1->id], [$y1->id], 2026]);

    $august  = $yearly['months'][7]; // index 7 = the 8th entry = August
    $january = $yearly['months'][0];

    $check('yearlySummary: August expected === 8888.88', $august['expected'] === 8888.88);
    $check('yearlySummary: August collected === 4444.44', $august['collected'] === 4444.44);
    $check('yearlySummary: August rate === 50.0', $august['rate'] === 50.0);
    $check('yearlySummary: August income === 4444.44', $august['income'] === 4444.44);
    $check('yearlySummary: August expenses === 555.55', $august['expenses'] === 555.55);
    $check('yearlySummary: August net === 3888.89', $august['net'] === 3888.89);
    $check('yearlySummary: August utilities === 333.33', $august['utilities'] === 333.33);
    $check('yearlySummary: January (no data) expected === 0.0', $january['expected'] === 0.0);
    $check('yearlySummary: January (no data) net === 0.0', $january['net'] === 0.0);
    $check('yearlySummary: yearly totals expected === 8888.88 (only August contributes)', $yearly['totals']['expected'] === 8888.88);
    $check('yearlySummary: yearly totals net === 3888.89', $yearly['totals']['net'] === 3888.89);
    $check('yearlySummary: yearly totals rate === 50.0', $yearly['totals']['rate'] === 50.0);

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