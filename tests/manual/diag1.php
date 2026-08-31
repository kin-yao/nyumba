<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/**
 * Isolated diagnostic for the collections() discrepancy. No assertions,
 * just dumps exactly what's being produced at each step so we can see
 * where 7777.77 actually diverges, if it does at all.
 */

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account = \App\Models\Account::create(['name' => 'Diag Co', 'phone' => '0700000099']);
    $user    = \App\Models\User::factory()->create(['account_id' => $account->id, 'role' => 'owner']);
    \Illuminate\Support\Facades\Auth::login($user);

    $property = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'Diag Property', 'type' => 'residential']);
    $unitX = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'X', 'type' => 'bedsitter', 'rent_amount' => '6666.67', 'deposit_amount' => '6666.67']);
    $unitY = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'Y', 'type' => 'bedsitter', 'rent_amount' => '8888.89', 'deposit_amount' => '8888.89']);
    $tenantX = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'X', 'last_name' => 'T', 'phone' => '0799999991']);
    $tenantY = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'Y', 'last_name' => 'T', 'phone' => '0799999992']);
    $leaseX = \App\Models\Lease::create(['unit_id' => $unitX->id, 'tenant_id' => $tenantX->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '6666.67', 'deposit_required' => '6666.67', 'status' => 'active']);
    $leaseY = \App\Models\Lease::create(['unit_id' => $unitY->id, 'tenant_id' => $tenantY->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '8888.89', 'deposit_required' => '8888.89', 'status' => 'active']);

    \App\Models\Payment::create(['account_id' => $account->id, 'lease_id' => $leaseX->id, 'tenant_id' => $tenantX->id, 'amount' => '3333.33', 'payment_date' => '2026-08-10', 'payment_type' => 'rent', 'method' => 'mpesa', 'reference' => 'DIAG-1', 'is_allocated' => true]);
    \App\Models\Payment::create(['account_id' => $account->id, 'lease_id' => $leaseX->id, 'tenant_id' => $tenantX->id, 'amount' => '2222.22', 'payment_date' => '2026-08-02', 'payment_type' => 'deposit', 'method' => 'mpesa', 'reference' => 'DIAG-2', 'is_allocated' => true]);
    \App\Models\Payment::create(['account_id' => $account->id, 'lease_id' => $leaseY->id, 'tenant_id' => $tenantY->id, 'amount' => '4444.44', 'payment_date' => '2026-08-11', 'payment_type' => 'rent', 'method' => 'cash', 'reference' => 'DIAG-3', 'is_allocated' => true]);

    $leaseIds = [$leaseX->id, $leaseY->id];

    $payments = \App\Models\Payment::whereIn('lease_id', $leaseIds)
        ->where('payment_type', '!=', 'deposit')
        ->whereMonth('payment_date', 8)
        ->whereYear('payment_date', 2026)
        ->get();

    echo "Row count: " . $payments->count() . "\n";
    foreach ($payments as $p) {
        echo "  id={$p->id} reference={$p->reference} amount=" . var_export($p->amount, true) . " (type: " . gettype($p->amount) . ")\n";
    }

    $reduced = $payments->reduce(fn($carry, $payment) => \App\Support\Money::add($carry, $payment->amount), '0.00');
    echo "\nReduce result (string from Money::add chain): " . var_export($reduced, true) . "\n";

    $asFloat = (float) $reduced;
    echo "Cast to float: " . var_export($asFloat, true) . "\n";
    echo "sprintf 20 decimals: " . sprintf('%.20f', $asFloat) . "\n";

    $literal = 7777.77;
    echo "\nLiteral 7777.77 sprintf 20 decimals: " . sprintf('%.20f', $literal) . "\n";
    echo "Strict equal to literal? " . var_export($asFloat === $literal, true) . "\n";
    echo "Loose equal to literal? " . var_export($asFloat == $literal, true) . "\n";
    echo "Difference: " . var_export($asFloat - $literal, true) . "\n";

    $dbSum = \App\Models\Payment::whereIn('lease_id', $leaseIds)
        ->where('payment_type', '!=', 'deposit')
        ->whereMonth('payment_date', 8)
        ->whereYear('payment_date', 2026)
        ->sum('amount');
    echo "\nDB-level sum() raw: " . var_export($dbSum, true) . " (type: " . gettype($dbSum) . ")\n";

} catch (\Throwable $e) {
    echo "\n!! EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} finally {
    \Illuminate\Support\Facades\DB::rollBack();
}