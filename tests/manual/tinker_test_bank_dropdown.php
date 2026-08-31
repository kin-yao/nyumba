<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Current APP_ENV: " . app()->environment() . "\n";

$failures = 0;
$checks   = 0;
$check = function (string $label, bool $condition) use (&$failures, &$checks) {
    $checks++;
    echo ($condition ? "  PASS  " : "  FAIL  ") . $label . "\n";
    if (!$condition) $failures++;
};

echo "\n=== Bank dropdown: bank_code + bank_account_number ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account   = \App\Models\Account::create(['name' => 'Bank Dropdown Co', 'phone' => '0700000190']);
    $adminUser = \App\Models\User::factory()->create(['account_id' => $account->id, 'role' => 'owner', 'is_admin' => true]);
    $property  = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'Bank Dropdown Property', 'type' => 'residential']);
    \Illuminate\Support\Facades\Auth::login($adminUser);

    // --- Admin saves via the dropdown ---
    $adminCtrl = app(\App\Http\Controllers\AdminController::class);
    $bankReq = \Illuminate\Http\Request::create('/admin/accounts/' . $account->id . '/properties/' . $property->id . '/bank-config', 'POST', [
        'bank_code' => 'kcb', 'bank_account_number' => '1234567800001', 'ipsl_password' => '',
    ]);
    $adminCtrl->updatePropertyBankConfig($bankReq, $account, $property);
    $check('bank_code saved as "kcb"', $property->fresh()->bank_code === 'kcb');
    $check('bank_account_number saved', $property->fresh()->bank_account_number === '1234567800001');

    // --- A bank not yet built is rejected by validation ---
    $rejected = false;
    try {
        $badReq = \Illuminate\Http\Request::create('/admin/x', 'POST', ['bank_code' => 'equity', 'bank_account_number' => '999', 'ipsl_password' => '']);
        $adminCtrl->updatePropertyBankConfig($badReq, $account, $property);
    } catch (\Illuminate\Validation\ValidationException $e) {
        $rejected = true;
    }
    $check('bank_code "equity" rejected (not yet built)', $rejected);

    // --- KCB matching actually uses the new fields ---
    $unit   = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'K1', 'type' => 'bedsitter', 'rent_amount' => '5000.00', 'deposit_amount' => '5000.00']);
    $tenant = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'Bank', 'last_name' => 'Test', 'phone' => '0700080001']);
    \App\Models\Lease::create(['unit_id' => $unit->id, 'tenant_id' => $tenant->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '5000.00', 'deposit_required' => '5000.00', 'status' => 'active']);

    $kcb = app(\App\Http\Controllers\KcbIpnController::class);
    $valReq = \Illuminate\Http\Request::create('/payments/kcb/validation', 'POST', [
        'requestId' => 'BD-1', 'customerReference' => 'K1', 'organizationReference' => '1234567800001',
    ]);
    $valResp = $kcb->validate($valReq);

    echo "DEBUG raw response: " . $valResp->getContent() . "\n";
    echo "DEBUG property fresh state: " . json_encode($property->fresh()->only(['id', 'bank_code', 'bank_account_number'])) . "\n";

    $check('KCB validate() finds the property via bank_code+bank_account_number', $valResp->getData()->statusCode === '0');

    echo "\n=== Result: {$checks} checks, " . ($checks - $failures) . " passed, {$failures} failed ===\n";

} catch (\Throwable $e) {
    $failures++;
    echo "\n!! EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
} finally {
    \Illuminate\Support\Facades\DB::rollBack();
    echo "Rolled back, no data left behind.\n";
    echo $failures > 0 ? "\n{$failures} FAILURE(S).\n" : "\nAll checks passed.\n";
}