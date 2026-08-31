<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n=== Diagnostic ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account  = \App\Models\Account::create(['name' => 'KCB Diag Co', 'phone' => '0700000121']);
    $property = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'KCB Diag Property', 'type' => 'residential', 'kcb_account_number' => '1234567800001']);

    echo "Property saved kcb_account_number: " . var_export($property->fresh()->kcb_account_number, true) . "\n";

    $lookup = \App\Models\Property::withoutGlobalScopes()->where('kcb_account_number', '1234567800001')->first();
    echo "Lookup by kcb_account_number found property: " . var_export($lookup !== null, true) . "\n";

    $unit   = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'K1', 'type' => 'bedsitter', 'rent_amount' => '5000.00', 'deposit_amount' => '5000.00']);
    $tenant = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'Kevin', 'last_name' => 'KCB', 'phone' => '0700030099']);
    $lease  = \App\Models\Lease::create(['unit_id' => $unit->id, 'tenant_id' => $tenant->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '5000.00', 'deposit_required' => '5000.00', 'status' => 'active']);
    \App\Models\Invoice::create(['account_id' => $account->id, 'lease_id' => $lease->id, 'reference' => 'DIAG-INV', 'period_month' => 8, 'period_year' => 2026, 'invoice_date' => '2026-08-01', 'due_date' => '2026-08-05', 'total_amount' => '5000.00', 'status' => 'sent']);

    // Test the exact reduce() call in isolation
    echo "\n--- Testing lease->invoices()->reduce() directly ---\n";
    try {
        $totalCharged = $lease->invoices()->reduce(
            fn($carry, $invoice) => \App\Support\Money::add($carry, $invoice->total_amount), '0.00'
        );
        echo "reduce() worked, result: " . var_export($totalCharged, true) . "\n";
    } catch (\Throwable $e) {
        echo "reduce() THREW: " . get_class($e) . ": " . $e->getMessage() . "\n";
    }

    // Now call validate() and dump the raw response + any output before it
    echo "\n--- Calling KcbIpnController::validate() ---\n";
    $kcb = app(\App\Http\Controllers\KcbIpnController::class);
    $valReq = \Illuminate\Http\Request::create('/payments/kcb/validation', 'POST', [
        'requestId' => 'DIAG-001', 'customerReference' => 'K1', 'organizationReference' => '1234567800001',
    ]);

    try {
        $valResp = $kcb->validate($valReq);
        echo "Raw response body: " . $valResp->getContent() . "\n";
    } catch (\Throwable $e) {
        echo "validate() THREW: " . get_class($e) . ": " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
    }

    // accountNotification too
    echo "\n--- Calling KcbIpnController::accountNotification() ---\n";
    $reconciler = app(\App\Http\Controllers\MpesaC2BController::class);
    $notifyReq = \Illuminate\Http\Request::create('/payments/kcb/account-notification', 'POST', [
        'transactionReference' => 'DIAGTX001', 'transactionAmount' => '5000.00',
        'customerReference' => 'K1', 'customerMobileNumber' => '254700030099',
        'timestamp' => '20260801120000', 'creditAccountIdentifier' => '1234567800001',
    ]);
    try {
        $notifyResp = $kcb->accountNotification($notifyReq, $reconciler);
        echo "Raw response body: " . $notifyResp->getContent() . "\n";
    } catch (\Throwable $e) {
        echo "accountNotification() THREW: " . get_class($e) . ": " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
    }

} catch (\Throwable $e) {
    echo "\n!! OUTER EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
} finally {
    \Illuminate\Support\Facades\DB::rollBack();
    echo "\nRolled back.\n";
}