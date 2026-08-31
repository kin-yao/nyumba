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

echo "\n=== pullCallback fix + matchUnit modes removed ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account  = \App\Models\Account::create(['name' => 'Match Test Co', 'phone' => '0700000110']);
    $property = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'Match Property', 'type' => 'residential', 'account_format' => 'phone_number']);
    $unit     = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'M1', 'type' => 'bedsitter', 'rent_amount' => '3000.00', 'deposit_amount' => '3000.00']);
    $tenant   = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'Match', 'last_name' => 'Test', 'phone' => '0700020001']);
    \App\Models\Lease::create(['unit_id' => $unit->id, 'tenant_id' => $tenant->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '3000.00', 'deposit_required' => '3000.00', 'status' => 'active']);

    $c2b = app(\App\Http\Controllers\MpesaC2BController::class);

    // Property still configured with old phone_number mode — must fall
    // through to unmatched, not error, even though MSISDN matches a tenant.
    $result = $c2b->processTransaction($property, [
        'TransID' => 'MATCHTEST01', 'TransAmount' => '3000.00',
        'BillRefNumber' => '', 'MSISDN' => '254700020001', 'BusinessShortCode' => '000000',
    ]);
    $check('Old phone_number mode falls through to unmatched (no error)', $result === 'unmatched');

    // unit_number mode still works normally
    $property->update(['account_format' => 'unit_number']);
    $result2 = $c2b->processTransaction($property, [
        'TransID' => 'MATCHTEST02', 'TransAmount' => '3000.00',
        'BillRefNumber' => 'M1', 'MSISDN' => '254700020001', 'BusinessShortCode' => '000000',
    ]);
    $check('unit_number mode still matches correctly', $result2 === 'matched');

    // pullCallback method exists and responds correctly
    $callbackReq = \Illuminate\Http\Request::create('/payments/pull/' . $property->id . '/callback', 'POST', ['some' => 'payload']);
    $response = $c2b->pullCallback($callbackReq, $property->id);
    $check('pullCallback() exists and returns ResultCode 0', $response->getData()->ResultCode === 0);

    // register() now points Safaricom at the URL that actually exists
    $expectedUrl = route('payments.pull.callback', $property->id);
    $check('The route payments.pull.callback resolves', str_contains($expectedUrl, '/payments/pull/' . $property->id . '/callback'));

    echo "\n=== Result: {$checks} checks, " . ($checks - $failures) . " passed, {$failures} failed ===\n";

} catch (\Throwable $e) {
    $failures++;
    echo "\n!! EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
} finally {
    \Illuminate\Support\Facades\DB::rollBack();
    echo "Rolled back, no data left behind.\n";
    echo $failures > 0 ? "\n{$failures} FAILURE(S).\n" : "\nAll checks passed.\n";
}