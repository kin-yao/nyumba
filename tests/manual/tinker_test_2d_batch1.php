<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/**
 * Manual validation for InvoiceController::bulkPreview() and bulkStore(),
 * specifically the total_amount that ends up written to the invoices
 * table, since that's the ground-truth figure the reconciliation engine
 * compares payments against later.
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

echo "\n=== InvoiceController: bulkPreview() / bulkStore() ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account = \App\Models\Account::create(['name' => 'Tinker Invoice Co', 'phone' => '0700000030']);
    $user    = \App\Models\User::factory()->create(['account_id' => $account->id, 'role' => 'owner']);
    \Illuminate\Support\Facades\Auth::login($user);

    $property = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'Invoice Property', 'type' => 'residential']);

    \App\Models\UtilityRate::create(['property_id' => $property->id, 'name' => 'Water', 'type' => 'water', 'amount' => '25.00', 'billing_type' => 'per_meter_reading', 'active' => true]);
    \App\Models\UtilityRate::create(['property_id' => $property->id, 'name' => 'Garbage', 'type' => 'garbage', 'amount' => '333.33', 'billing_type' => 'flat_fee', 'active' => true]);

    $unit1 = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'I1', 'type' => 'bedsitter', 'rent_amount' => '6666.67', 'deposit_amount' => '6666.67']);
    $unit2 = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'I2', 'type' => 'bedsitter', 'rent_amount' => '5432.10', 'deposit_amount' => '5432.10']);
    $unit3 = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'I3', 'type' => 'bedsitter', 'rent_amount' => '9999.99', 'deposit_amount' => '9999.99']);

    $tenant1 = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'I', 'last_name' => 'One', 'phone' => '0788881001']);
    $tenant2 = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'I', 'last_name' => 'Two', 'phone' => '0788881002']);
    $tenant3 = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'I', 'last_name' => 'Three', 'phone' => '0788881003']);

    $lease1 = \App\Models\Lease::create(['unit_id' => $unit1->id, 'tenant_id' => $tenant1->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '6666.67', 'deposit_required' => '6666.67', 'status' => 'active']);
    $lease2 = \App\Models\Lease::create(['unit_id' => $unit2->id, 'tenant_id' => $tenant2->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '5432.10', 'deposit_required' => '5432.10', 'status' => 'active']);
    $lease3 = \App\Models\Lease::create(['unit_id' => $unit3->id, 'tenant_id' => $tenant3->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '9999.99', 'deposit_required' => '9999.99', 'status' => 'active']);

    \App\Models\UtilityReading::create(['unit_id' => $unit1->id, 'account_id' => $account->id, 'utility_type' => 'water', 'reading_month' => 8, 'reading_year' => 2026, 'previous_reading' => '0.00', 'current_reading' => '18.27', 'units_consumed' => '18.27', 'rate_per_unit' => '25.00', 'charge_amount' => '456.78']);

    $invoiceCtrl = app(\App\Http\Controllers\InvoiceController::class);

    // --- bulkPreview() ---
    $previewRequest = \Illuminate\Http\Request::create('/invoices/bulk/preview', 'POST', [
        'period_month' => 8, 'period_year' => 2026,
        'invoice_date' => '2026-08-01', 'due_date' => '2026-08-05',
    ]);
    $invoiceCtrl->bulkPreview($previewRequest);

    $previews = session('bulk_previews');
    $preview1 = collect($previews)->firstWhere('lease_id', $lease1->id);

    $check('bulkPreview(): unit I1 total === 7456.78 (6666.67 rent + 456.78 water + 333.33 garbage)', abs($preview1['total'] - 7456.78) < 0.001);
    $check('bulkPreview(): unit I1 has 3 line items', count($preview1['line_items']) === 3);

    // --- bulkStore(): fallback path (no submitted line items, rent only) ---
    $storeRequest1 = \Illuminate\Http\Request::create('/invoices/bulk', 'POST', [
        'invoice_date' => '2026-08-01', 'due_date' => '2026-08-05',
        'period_month' => 8, 'period_year' => 2026,
        'lease_ids'    => [$lease2->id],
    ]);
    $invoiceCtrl->bulkStore($storeRequest1);

    $invoice2 = \App\Models\Invoice::where('lease_id', $lease2->id)->first();
    $check('bulkStore() fallback: invoice created for I2', $invoice2 !== null);
    $check('bulkStore() fallback: total_amount === 5432.10 (rent only, no line items submitted)', $invoice2 && $invoice2->total_amount === '5432.10');

    // --- bulkStore(): explicit submitted line items (simulating user edits in the preview UI) ---
    $storeRequest2 = \Illuminate\Http\Request::create('/invoices/bulk', 'POST', [
        'invoice_date' => '2026-08-01', 'due_date' => '2026-08-05',
        'period_month' => 8, 'period_year' => 2026,
        'lease_ids'    => [$lease3->id],
        'line_items_' . $lease3->id => json_encode([
            ['description' => 'August rent', 'amount' => 1111.11, 'type' => 'rent'],
            ['description' => 'Water charges', 'amount' => 222.22, 'type' => 'water'],
        ]),
    ]);
    $invoiceCtrl->bulkStore($storeRequest2);

    $invoice3 = \App\Models\Invoice::where('lease_id', $lease3->id)->first();
    $check('bulkStore() with submitted line items: invoice created for I3', $invoice3 !== null);
    $check('bulkStore() with submitted line items: total_amount === 1333.33 (1111.11 + 222.22)', $invoice3 && $invoice3->total_amount === '1333.33');

    $lineItems3 = \App\Models\InvoiceLineItem::where('invoice_id', $invoice3->id)->get();
    $check('bulkStore(): 2 line items persisted for I3', $lineItems3->count() === 2);
    $amounts3 = $lineItems3->pluck('amount')->toArray();
    $check('bulkStore(): line item amounts stored as clean 2dp decimals (1111.11 and 222.22, either order)', in_array('1111.11', $amounts3, true) && in_array('222.22', $amounts3, true));

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