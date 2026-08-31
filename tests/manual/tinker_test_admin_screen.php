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

echo "\n=== Admin screen: bank config + unit payment references ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account   = \App\Models\Account::create(['name' => 'Admin Screen Co', 'phone' => '0700000150']);
    $adminUser = \App\Models\User::factory()->create(['account_id' => $account->id, 'role' => 'owner', 'is_admin' => true]);
    $property  = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'Admin Screen Property', 'type' => 'residential', 'payment_type' => 'paybill']);
    $unit      = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'A1', 'type' => 'bedsitter', 'rent_amount' => '5000.00', 'deposit_amount' => '5000.00']);

    \Illuminate\Support\Facades\Auth::login($adminUser);

    // --- account_format bug fix ---
    $propertyCtrl = app(\App\Http\Controllers\PropertyController::class);

    $badReq = \Illuminate\Http\Request::create('/properties/' . $property->id, 'PUT', [
        'name' => 'Admin Screen Property', 'type' => 'residential', 'payment_type' => 'paybill', 'account_format' => 'tenant_name',
    ]);
    $rejected = false;
    try {
        $propertyCtrl->update($badReq, $property);
    } catch (\Illuminate\Validation\ValidationException $e) {
        $rejected = true;
    }
    $check('account_format "tenant_name" is now rejected by validation', $rejected);

    $goodReq = \Illuminate\Http\Request::create('/properties/' . $property->id, 'PUT', [
        'name' => 'Admin Screen Property', 'type' => 'residential', 'payment_type' => 'paybill', 'account_format' => 'unit_number',
    ]);
    $propertyCtrl->update($goodReq, $property);
    $check('account_format "unit_number" still works', $property->fresh()->account_format === 'unit_number');

    // --- unit payment reference ---
    $unitCtrl = app(\App\Http\Controllers\UnitController::class);

    $refReq = \Illuminate\Http\Request::create('/units/' . $unit->id . '/reference', 'PATCH', ['payment_reference' => 'KLM-A1']);
    $unitCtrl->updateReference($refReq, $unit);
    $check('payment_reference saved', $unit->fresh()->payment_reference === 'KLM-A1');

    $clearReq = \Illuminate\Http\Request::create('/units/' . $unit->id . '/reference', 'PATCH', ['payment_reference' => '']);
    $unitCtrl->updateReference($clearReq, $unit);
    $check('payment_reference clears to null when blanked', $unit->fresh()->payment_reference === null);

    // Confirm IPSL matching still works using the fallback after clearing
    $check('UnitMatcher still matches on unit name after reference cleared', \App\Services\UnitMatcher::matchNormalized($property, 'A1')?->id === $unit->id);

    // --- KCB/IPSL bank config ---
    $adminCtrl = app(\App\Http\Controllers\AdminController::class);

    $bankReq1 = \Illuminate\Http\Request::create('/admin/accounts/' . $account->id . '/properties/' . $property->id . '/bank-config', 'POST', [
        'kcb_account_number' => '1234567800001', 'ipsl_password' => 'first_secret',
    ]);
    $adminCtrl->updatePropertyBankConfig($bankReq1, $account, $property);
    $check('kcb_account_number saved', $property->fresh()->kcb_account_number === '1234567800001');
    $check('ipsl_password saved', $property->fresh()->ipsl_password === 'first_secret');

    // Blank ipsl_password on a second save must NOT wipe out the existing one
    $bankReq2 = \Illuminate\Http\Request::create('/admin/accounts/' . $account->id . '/properties/' . $property->id . '/bank-config', 'POST', [
        'kcb_account_number' => '1234567800002', 'ipsl_password' => '',
    ]);
    $adminCtrl->updatePropertyBankConfig($bankReq2, $account, $property);
    $check('kcb_account_number updated on second save', $property->fresh()->kcb_account_number === '1234567800002');
    $check('blank ipsl_password does NOT overwrite the existing password', $property->fresh()->ipsl_password === 'first_secret');

    echo "\n=== Result: {$checks} checks, " . ($checks - $failures) . " passed, {$failures} failed ===\n";

} catch (\Throwable $e) {
    $failures++;
    echo "\n!! EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
} finally {
    \Illuminate\Support\Facades\DB::rollBack();
    echo "Rolled back, no data left behind.\n";
    echo $failures > 0 ? "\n{$failures} FAILURE(S).\n" : "\nAll checks passed.\n";
}