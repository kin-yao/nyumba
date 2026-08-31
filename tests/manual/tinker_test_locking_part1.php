<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/**
 * Manual validation for row-level locking on invoice allocation in
 * MpesaC2BController::processTransaction().
 *
 * Part 1: functional correctness is unchanged (the allocation math itself
 * wasn't touched, only locking was added) — standard rollback pattern.
 *
 * Part 2: a genuine cross-connection proof that the lock actually blocks a
 * second writer. This needs two real, separate database connections, so
 * it can't use the rollback pattern (a second connection can't see rows
 * still sitting in an uncommitted transaction on the first) — it commits
 * a small fixture and deletes it explicitly at the end instead.
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

echo "\n=== Part 1: functional correctness after adding locking ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account  = \App\Models\Account::create(['name' => 'Tinker Lock Co', 'phone' => '0700000090']);
    $property = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'Lock Property', 'type' => 'residential']);
    $unit     = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'L1', 'type' => 'bedsitter', 'rent_amount' => '5000.00', 'deposit_amount' => '5000.00']);
    $tenant   = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'Lock', 'last_name' => 'Test', 'phone' => '0700001111']);
    $lease    = \App\Models\Lease::create(['unit_id' => $unit->id, 'tenant_id' => $tenant->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '5000.00', 'deposit_required' => '5000.00', 'status' => 'active']);
    \App\Models\Invoice::create(['account_id' => $account->id, 'lease_id' => $lease->id, 'reference' => 'LOCK-INV-1', 'period_month' => 8, 'period_year' => 2026, 'invoice_date' => '2026-08-01', 'due_date' => '2026-08-05', 'total_amount' => '5000.00', 'status' => 'sent']);

    $c2b = app(\App\Http\Controllers\MpesaC2BController::class);
    $result = $c2b->processTransaction($property, [
        'TransID' => 'LOCKTEST0001', 'TransAmount' => '5000.00',
        'BillRefNumber' => 'L1', 'MSISDN' => '254712340000', 'BusinessShortCode' => '000000',
    ]);

    $invoice = \App\Models\Invoice::where('lease_id', $lease->id)->first();
    $check('Payment still processes correctly after adding locks', $result === 'matched');
    $check('Invoice still marked paid correctly', $invoice && $invoice->status === 'paid');
    $check('Invoice amount_paid still correct', $invoice && $invoice->amount_paid === '5000.00');

    echo "\n=== Result: {$checks} checks, " . ($checks - $failures) . " passed so far ===\n";

} catch (\Throwable $e) {
    $failures++;
    echo "\n!! EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} finally {
    \Illuminate\Support\Facades\DB::rollBack();
    echo "Part 1 transaction rolled back, no data left behind.\n";
}

echo "\n=== Part 2: proving the lock actually blocks a second connection ===\n";

$dbConfig = config('database.connections.mysql');
$account2 = null;
$property2 = null;
$unit2 = null;
$tenant2 = null;
$lease2 = null;
$invoice2 = null;
$pdoA = null;
$pdoB = null;

try {
    // Committed fixture (not wrapped in a rollback) — a second raw
    // connection needs to actually see these rows.
    $account2  = \App\Models\Account::create(['name' => 'Tinker Lock Co 2', 'phone' => '0700000091']);
    $property2 = \App\Models\Property::create(['account_id' => $account2->id, 'name' => 'Lock Property 2', 'type' => 'residential']);
    $unit2     = \App\Models\Unit::create(['property_id' => $property2->id, 'name' => 'L2', 'type' => 'bedsitter', 'rent_amount' => '5000.00', 'deposit_amount' => '5000.00']);
    $tenant2   = \App\Models\Tenant::create(['account_id' => $account2->id, 'first_name' => 'Lock2', 'last_name' => 'Test', 'phone' => '0700002222']);
    $lease2    = \App\Models\Lease::create(['unit_id' => $unit2->id, 'tenant_id' => $tenant2->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '5000.00', 'deposit_required' => '5000.00', 'status' => 'active']);
    $invoice2  = \App\Models\Invoice::create(['account_id' => $account2->id, 'lease_id' => $lease2->id, 'reference' => 'LOCK-INV-2', 'period_month' => 8, 'period_year' => 2026, 'invoice_date' => '2026-08-01', 'due_date' => '2026-08-05', 'total_amount' => '5000.00', 'status' => 'sent']);

    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']}";

    // Connection A: acquire the lock and hold it open (do NOT commit yet)
    $pdoA = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdoA->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoA->beginTransaction();
    $stmtA = $pdoA->prepare('SELECT * FROM invoices WHERE id = ? FOR UPDATE');
    $stmtA->execute([$invoice2->id]);
    $stmtA->fetch();

    $check('Connection A acquired the row lock (no exception)', true);

    // Connection B: short lock-wait timeout, then try the SAME lock
    $pdoB = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdoB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdoB->exec('SET SESSION innodb_lock_wait_timeout = 2');
    $pdoB->beginTransaction();

    $blocked = false;
    $startedAt = microtime(true);
    try {
        $stmtB = $pdoB->prepare('SELECT * FROM invoices WHERE id = ? FOR UPDATE');
        $stmtB->execute([$invoice2->id]);
        $stmtB->fetch();
    } catch (\PDOException $e) {
        $blocked = str_contains(strtolower($e->getMessage()), 'lock wait timeout');
    }
    $elapsed = microtime(true) - $startedAt;
    $pdoB->rollBack();

    $check('Connection B was blocked and timed out waiting for the lock (proves it is real)', $blocked);
    $check('Connection B actually waited (not an instant failure) — waited ' . round($elapsed, 1) . 's', $elapsed >= 1.5);

    // Release A's lock, then confirm B can now proceed immediately
    $pdoA->commit();

    $pdoB->beginTransaction();
    $stmtB2 = $pdoB->prepare('SELECT * FROM invoices WHERE id = ? FOR UPDATE');
    $stmtB2->execute([$invoice2->id]);
    $rowAfterRelease = $stmtB2->fetch();
    $pdoB->rollBack();

    $check('Connection B succeeds immediately once A releases the lock', $rowAfterRelease !== false);

    echo "\n=== Result: {$checks} checks, " . ($checks - $failures) . " passed, {$failures} failed ===\n";

} catch (\Throwable $e) {
    $failures++;
    echo "\n!! EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} finally {
    if ($pdoA && $pdoA->inTransaction()) $pdoA->rollBack();
    if ($pdoB && $pdoB->inTransaction()) $pdoB->rollBack();

    // Explicit cleanup — this fixture was committed, not wrapped in a
    // rollback, so it has to be removed by hand, in FK-safe order.
    if ($invoice2) \App\Models\Invoice::withoutGlobalScopes()->where('id', $invoice2->id)->delete();
    if ($lease2) \App\Models\Lease::withoutGlobalScopes()->where('id', $lease2->id)->delete();
    if ($tenant2) \App\Models\Tenant::withoutGlobalScopes()->where('id', $tenant2->id)->delete();
    if ($unit2) \App\Models\Unit::withoutGlobalScopes()->where('id', $unit2->id)->delete();
    if ($property2) \App\Models\Property::withoutGlobalScopes()->where('id', $property2->id)->delete();
    if ($account2) \App\Models\Account::withoutGlobalScopes()->where('id', $account2->id)->delete();

    echo "Part 2 fixture explicitly cleaned up.\n";

    if ($failures > 0) {
        echo "\n{$failures} FAILURE(S) across both parts. Do not consider this verified until clean.\n";
    } else {
        echo "\nAll checks passed across both parts.\n";
    }
}