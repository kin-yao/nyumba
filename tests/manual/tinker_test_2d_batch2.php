<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/**
 * Manual validation for SendMonthlyInvoices, the auto-generated version of
 * the same invoice-total logic just fixed in InvoiceController.
 *
 * Deliberately does NOT create a landlord User, so sendLandlordSummary()
 * returns early before its AfricasTalking SMS call, no real network
 * request happens. Tenant SMS is skipped naturally since sms_credits
 * defaults to 0.
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

echo "\n=== SendMonthlyInvoices ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account = \App\Models\Account::create(['name' => 'Tinker Monthly Co', 'phone' => '0700000040']);

    $property = \App\Models\Property::create([
        'account_id' => $account->id, 'name' => 'Monthly Property', 'type' => 'residential',
        'auto_invoice_enabled' => true, 'invoice_send_day' => 1,
    ]);

    \App\Models\UtilityRate::create(['property_id' => $property->id, 'name' => 'Water', 'type' => 'water', 'amount' => '20.00', 'billing_type' => 'per_meter_reading', 'active' => true, 'auto_bill' => true]);
    \App\Models\UtilityRate::create(['property_id' => $property->id, 'name' => 'Security', 'type' => 'other', 'amount' => '55.55', 'billing_type' => 'flat_fee', 'active' => true, 'auto_bill' => true]);

    $unit   = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'M1', 'type' => 'bedsitter', 'rent_amount' => '4444.44', 'deposit_amount' => '4444.44']);
    $tenant = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'M', 'last_name' => 'One', 'phone' => '0799990001']);
    $lease  = \App\Models\Lease::create(['unit_id' => $unit->id, 'tenant_id' => $tenant->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '4444.44', 'deposit_required' => '4444.44', 'status' => 'active']);

    \App\Models\UtilityReading::create(['unit_id' => $unit->id, 'account_id' => $account->id, 'utility_type' => 'water', 'reading_month' => now()->month, 'reading_year' => now()->year, 'previous_reading' => '0.00', 'current_reading' => '5.56', 'units_consumed' => '5.56', 'rate_per_unit' => '20.00', 'charge_amount' => '111.11']);

    \Illuminate\Support\Facades\Artisan::call('invoices:send-monthly', ['--force' => true]);
    $output = \Illuminate\Support\Facades\Artisan::output();

    $invoice = \App\Models\Invoice::where('lease_id', $lease->id)->first();

    $check('An invoice was generated', $invoice !== null);
    $check('total_amount === 4611.10 (4444.44 rent + 111.11 water + 55.55 security)', $invoice && $invoice->total_amount === '4611.10');

    $lineItems = $invoice ? \App\Models\InvoiceLineItem::where('invoice_id', $invoice->id)->get() : collect();
    $check('3 line items persisted', $lineItems->count() === 3);
    $amounts = $lineItems->pluck('amount')->toArray();
    $check('line item amounts include 4444.44, 111.11, and 55.55', in_array('4444.44', $amounts, true) && in_array('111.11', $amounts, true) && in_array('55.55', $amounts, true));

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