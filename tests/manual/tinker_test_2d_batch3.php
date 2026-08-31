<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/**
 * Manual validation for UtilityController::store() and
 * uploadReadingsCsv(), the two spots that actually compute
 * charge_amount = units_consumed x rate_per_unit, which then flows
 * straight into invoice generation.
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

echo "\n=== UtilityController: store() / uploadReadingsCsv() ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account = \App\Models\Account::create(['name' => 'Tinker Utility Co', 'phone' => '0700000050']);
    $user    = \App\Models\User::factory()->create(['account_id' => $account->id, 'role' => 'owner']);
    \Illuminate\Support\Facades\Auth::login($user);

    $property = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'Utility Property', 'type' => 'residential']);
    \App\Models\UtilityRate::create(['property_id' => $property->id, 'name' => 'Water', 'type' => 'water', 'amount' => '20.00', 'billing_type' => 'per_meter_reading', 'active' => true]);

    $unit1 = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'W1', 'type' => 'bedsitter', 'rent_amount' => '10000.00', 'deposit_amount' => '10000.00']);
    $unit2 = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'W2', 'type' => 'bedsitter', 'rent_amount' => '10000.00', 'deposit_amount' => '10000.00']);
    $tenant1 = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'W', 'last_name' => 'One', 'phone' => '0766660001']);
    $tenant2 = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'W', 'last_name' => 'Two', 'phone' => '0766660002']);
    \App\Models\Lease::create(['unit_id' => $unit1->id, 'tenant_id' => $tenant1->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '10000.00', 'deposit_required' => '10000.00', 'status' => 'active']);
    \App\Models\Lease::create(['unit_id' => $unit2->id, 'tenant_id' => $tenant2->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '10000.00', 'deposit_required' => '10000.00', 'status' => 'active']);

    $utilityCtrl = app(\App\Http\Controllers\UtilityController::class);

    // --- store() ---
    $storeRequest = \Illuminate\Http\Request::create('/utilities', 'POST', [
        'unit_id' => $unit1->id, 'utility_type' => 'water',
        'reading_month' => 8, 'reading_year' => 2026,
        'previous_reading' => '10.50', 'current_reading' => '28.75',
    ]);
    $utilityCtrl->store($storeRequest);

    $reading1 = \App\Models\UtilityReading::where('unit_id', $unit1->id)->where('reading_month', 8)->where('reading_year', 2026)->first();
    $check('store(): units_consumed === 18.25 (28.75 - 10.50)', $reading1 && $reading1->units_consumed === '18.25');
    $check('store(): charge_amount === 365.00 (18.25 x 20.00)', $reading1 && $reading1->charge_amount === '365.00');

    // --- uploadReadingsCsv() ---
    $csvContent = "unit_id,property,unit,tenant,utility_type,utility_name,previous_reading,current_reading\n"
        . $unit2->id . ",Utility Property,W2,W Two,water,Water,5.00,22.20\n";
    $tmpPath = tempnam(sys_get_temp_dir(), 'util_csv_');
    file_put_contents($tmpPath, $csvContent);
    $uploadedFile = new \Illuminate\Http\UploadedFile($tmpPath, 'readings.csv', 'text/csv', null, true);

    $csvRequest = \Illuminate\Http\Request::create('/utilities/readings/csv', 'POST', ['month' => 8, 'year' => 2026]);
    $csvRequest->files->set('csv_file', $uploadedFile);
    $utilityCtrl->uploadReadingsCsv($csvRequest);

    $reading2 = \App\Models\UtilityReading::where('unit_id', $unit2->id)->where('reading_month', 8)->where('reading_year', 2026)->first();
    $check('uploadReadingsCsv(): a reading was created for W2', $reading2 !== null);
    $check('uploadReadingsCsv(): units_consumed === 17.20 (22.20 - 5.00)', $reading2 && $reading2->units_consumed === '17.20');
    $check('uploadReadingsCsv(): charge_amount === 430.00 (17.20 x 25.00... wait rate is 20.00) === 344.00', $reading2 && $reading2->charge_amount === '344.00');

    @unlink($tmpPath);

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