<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

config(['services.kcb.verify_signature' => false]); // test-only, does not affect real code

$failures = 0;
$checks   = 0;
$check = function (string $label, bool $condition) use (&$failures, &$checks) {
    $checks++;
    echo ($condition ? "  PASS  " : "  FAIL  ") . $label . "\n";
    if (!$condition) $failures++;
};

echo "\n=== KCB: validate() + accountNotification() ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account  = \App\Models\Account::create(['name' => 'KCB Test Co', 'phone' => '0700000120']);
    $property = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'KCB Property', 'type' => 'residential', 'kcb_account_number' => '1234567800001']);
    $unit     = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'K1', 'type' => 'bedsitter', 'rent_amount' => '5000.00', 'deposit_amount' => '5000.00']);
    $tenant   = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'Kevin', 'last_name' => 'KCB', 'phone' => '0700030001']);
    $lease    = \App\Models\Lease::create(['unit_id' => $unit->id, 'tenant_id' => $tenant->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '5000.00', 'deposit_required' => '5000.00', 'status' => 'active']);
    \App\Models\Invoice::create(['account_id' => $account->id, 'lease_id' => $lease->id, 'reference' => 'KCB-INV-1', 'period_month' => 8, 'period_year' => 2026, 'invoice_date' => '2026-08-01', 'due_date' => '2026-08-05', 'total_amount' => '5000.00', 'status' => 'sent']);

    $kcb = app(\App\Http\Controllers\KcbIpnController::class);

    // --- validate(): matching unit ---
    $valReq = \Illuminate\Http\Request::create('/payments/kcb/validation', 'POST', [
        'requestId' => 'REQ-001', 'customerReference' => 'K1', 'organizationReference' => '1234567800001',
    ]);
    $valResp = $kcb->validate($valReq);
    $valData = $valResp->getData();

    $check('validate(): statusCode is string "0"', $valData->statusCode === '0');
    $check('validate(): CustomerName is the tenant', $valData->CustomerName === 'Kevin KCB');
    $check('validate(): billAmount is string "5000.00"', $valData->billAmount === '5000.00');
    $check('validate(): billType is PARTIAL', $valData->billType === 'PARTIAL');
    $check('validate(): creditAccountIdentifier echoes the property account', $valData->creditAccountIdentifier === '1234567800001');

    // --- validate(): unmatched unit ---
    $valReq2 = \Illuminate\Http\Request::create('/payments/kcb/validation', 'POST', [
        'requestId' => 'REQ-002', 'customerReference' => 'NOSUCHUNIT', 'organizationReference' => '1234567800001',
    ]);
    $valResp2 = $kcb->validate($valReq2);
    $check('validate(): unmatched unit returns statusCode "1"', $valResp2->getData()->statusCode === '1');

    // --- validate(): unknown property ---
    $valReq3 = \Illuminate\Http\Request::create('/payments/kcb/validation', 'POST', [
        'requestId' => 'REQ-003', 'customerReference' => 'K1', 'organizationReference' => 'NOPE',
    ]);
    $valResp3 = $kcb->validate($valReq3);
    $check('validate(): unknown property returns statusCode "1"', $valResp3->getData()->statusCode === '1');

    // --- accountNotification(): statusCode is now a string ---
    $reconciler = app(\App\Http\Controllers\MpesaC2BController::class);
    $notifyReq = \Illuminate\Http\Request::create('/payments/kcb/account-notification', 'POST', [
        'transactionReference' => 'KCBTX001', 'transactionAmount' => '5000.00',
        'customerReference' => 'K1', 'customerMobileNumber' => '254700030001',
        'timestamp' => '20260801120000', 'creditAccountIdentifier' => '1234567800001',
    ]);
    $notifyResp = $kcb->accountNotification($notifyReq, $reconciler);
    $notifyData = $notifyResp->getData();

    $check('accountNotification(): statusCode is string "0"', $notifyData->statusCode === '0');
    $invoice = \App\Models\Invoice::where('lease_id', $lease->id)->first();
    $check('accountNotification(): invoice paid correctly', $invoice && $invoice->status === 'paid');

    echo "\n=== Result: {$checks} checks, " . ($checks - $failures) . " passed, {$failures} failed ===\n";

} catch (\Throwable $e) {
    $failures++;
    echo "\n!! EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
} finally {
    \Illuminate\Support\Facades\DB::rollBack();
    echo "Rolled back, no data left behind.\n";
    echo $failures > 0 ? "\n{$failures} FAILURE(S).\n" : "\nAll checks passed.\n";
}