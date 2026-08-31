<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/**
 * Manual validation for ImportController::preview() and store(), the CSV
 * bulk-import flow. Low practical risk (no arithmetic, single values
 * straight to storage), but verified for consistency with everything
 * else in this sweep.
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

echo "\n=== ImportController: preview() / store() ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account = \App\Models\Account::create(['name' => 'Tinker Import Co', 'phone' => '0700000070']);
    $user    = \App\Models\User::factory()->create(['account_id' => $account->id, 'role' => 'owner']);
    \Illuminate\Support\Facades\Auth::login($user);

    $property = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'Import Property', 'type' => 'residential']);

    $csvContent = "unit_name,unit_type,rent_amount,deposit_amount,first_name,last_name,phone,alt_phone,id_number,email,move_in_date,lease_end_date,deposit_paid,deposit_method,notes\n"
        . "IM1,bedsitter,6543.21,6543.21,Import,Test,0712345678,,,,01/08/2026,,2000.50,mpesa,\n"
        . "IM2,bedsitter,1234.56,1234.56,,,,,,,,,,,\n";
    $tmpPath = tempnam(sys_get_temp_dir(), 'import_csv_');
    file_put_contents($tmpPath, $csvContent);
    $uploadedFile = new \Illuminate\Http\UploadedFile($tmpPath, 'import.csv', 'text/csv', null, true);

    $previewRequest = \Illuminate\Http\Request::create('/properties/' . $property->id . '/import/preview', 'POST');
    $previewRequest->files->set('csv_file', $uploadedFile);

    $importCtrl = app(\App\Http\Controllers\ImportController::class);
    $importCtrl->preview($previewRequest, $property);

    $rows = session('import_rows');
    $check('preview(): 2 rows parsed', $rows !== null && count($rows) === 2);
    $row1 = collect($rows)->firstWhere('unit_name', 'IM1');
    $check('preview(): row 1 status is ready or warning (not error)', $row1 && in_array($row1['status'], ['ready', 'warning']));

    $storeRequest = \Illuminate\Http\Request::create('/properties/' . $property->id . '/import/store', 'POST');
    $importCtrl->store($storeRequest, $property);

    $unit1 = \App\Models\Unit::where('property_id', $property->id)->where('name', 'IM1')->first();
    $unit2 = \App\Models\Unit::where('property_id', $property->id)->where('name', 'IM2')->first();

    $check('store(): unit IM1 created', $unit1 !== null);
    $check('store(): unit IM1 rent_amount === 6543.21', $unit1 && $unit1->rent_amount === '6543.21');
    $check('store(): unit IM1 deposit_amount === 6543.21', $unit1 && $unit1->deposit_amount === '6543.21');
    $check('store(): unit IM1 status is occupied (has a tenant)', $unit1 && $unit1->status === 'occupied');

    $check('store(): unit IM2 created (vacant, no tenant)', $unit2 !== null);
    $check('store(): unit IM2 rent_amount === 1234.56', $unit2 && $unit2->rent_amount === '1234.56');
    $check('store(): unit IM2 status is vacant', $unit2 && $unit2->status === 'vacant');

    $lease1 = $unit1 ? \App\Models\Lease::where('unit_id', $unit1->id)->first() : null;
    $check('store(): a lease was created for IM1', $lease1 !== null);
    $check('store(): lease monthly_rent === 6543.21', $lease1 && $lease1->monthly_rent === '6543.21');
    $check('store(): lease deposit_required === 6543.21', $lease1 && $lease1->deposit_required === '6543.21');
    $check('store(): lease deposit_paid === 2000.50', $lease1 && $lease1->deposit_paid === '2000.50');

    $depositPayment = $lease1 ? \App\Models\Payment::where('lease_id', $lease1->id)->where('payment_type', 'deposit')->first() : null;
    $check('store(): opening deposit Payment created', $depositPayment !== null);
    $check('store(): opening deposit payment amount === 2000.50', $depositPayment && $depositPayment->amount === '2000.50');

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