<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/**
 * Manual validation for the first ReportController batch: depositsSnapshot(),
 * outstandingSnapshot(), rentRollForPeriod(), collectionsForPeriod(),
 * incomeExpensesForPeriod(). These are all private, called via reflection
 * directly rather than through download() (which would trigger real PDF
 * rendering, not needed to verify the numbers).
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

$callPrivate = function (object $obj, string $method, array $args) {
    $ref = new \ReflectionMethod($obj, $method);
    $ref->setAccessible(true);
    return $ref->invoke($obj, ...$args);
};

echo "\n=== ReportController: depositsSnapshot / outstandingSnapshot / rentRollForPeriod / collectionsForPeriod / incomeExpensesForPeriod ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account  = \App\Models\Account::create(['name' => 'Tinker Report Co', 'phone' => '0700000010']);
    $property = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'Report Property', 'type' => 'residential']);

    $unit1 = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'U1', 'type' => 'bedsitter', 'rent_amount' => '9999.99', 'deposit_amount' => '9999.99']);
    $unit2 = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'U2', 'type' => 'bedsitter', 'rent_amount' => '15000.00', 'deposit_amount' => '15000.00']);
    $unit3 = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'U3', 'type' => 'bedsitter', 'rent_amount' => '20000.01', 'deposit_amount' => '20000.01']);

    $tenant1 = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'T', 'last_name' => 'One', 'phone' => '0711111111']);
    $tenant2 = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'T', 'last_name' => 'Two', 'phone' => '0722222222']);
    $tenant3 = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'T', 'last_name' => 'Three', 'phone' => '0733333333']);

    $lease1 = \App\Models\Lease::create(['unit_id' => $unit1->id, 'tenant_id' => $tenant1->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '9999.99', 'deposit_required' => '9999.99', 'deposit_paid' => '3333.33', 'status' => 'active']);
    $lease2 = \App\Models\Lease::create(['unit_id' => $unit2->id, 'tenant_id' => $tenant2->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '15000.00', 'deposit_required' => '15000.00', 'deposit_paid' => '15000.00', 'status' => 'active']);
    $lease3 = \App\Models\Lease::create(['unit_id' => $unit3->id, 'tenant_id' => $tenant3->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '20000.01', 'deposit_required' => '20000.01', 'deposit_paid' => '0.00', 'status' => 'active']);

    // Invoices for August 2026 — U1 partial, U2 fully paid, U3 has none
    \App\Models\Invoice::create(['account_id' => $account->id, 'lease_id' => $lease1->id, 'reference' => 'RPT-INV-1', 'period_month' => 8, 'period_year' => 2026, 'invoice_date' => '2026-08-01', 'due_date' => '2026-08-05', 'total_amount' => '9999.99', 'amount_paid' => '6666.66', 'status' => 'partial']);
    \App\Models\Invoice::create(['account_id' => $account->id, 'lease_id' => $lease2->id, 'reference' => 'RPT-INV-2', 'period_month' => 8, 'period_year' => 2026, 'invoice_date' => '2026-08-01', 'due_date' => '2026-08-05', 'total_amount' => '15000.00', 'amount_paid' => '15000.00', 'status' => 'paid']);

    // Payments in August 2026 — rent for U1 and U2, plus a deposit payment
    // for U1 that must NOT count toward collections/income
    \App\Models\Payment::create(['account_id' => $account->id, 'lease_id' => $lease1->id, 'tenant_id' => $tenant1->id, 'amount' => '6666.66', 'payment_date' => '2026-08-10', 'payment_type' => 'rent', 'method' => 'mpesa', 'reference' => 'RPT-PAY-1', 'is_allocated' => true]);
    \App\Models\Payment::create(['account_id' => $account->id, 'lease_id' => $lease2->id, 'tenant_id' => $tenant2->id, 'amount' => '15000.00', 'payment_date' => '2026-08-12', 'payment_type' => 'rent', 'method' => 'cash', 'reference' => 'RPT-PAY-2', 'is_allocated' => true]);
    \App\Models\Payment::create(['account_id' => $account->id, 'lease_id' => $lease1->id, 'tenant_id' => $tenant1->id, 'amount' => '3333.33', 'payment_date' => '2026-08-03', 'payment_type' => 'deposit', 'method' => 'mpesa', 'reference' => 'RPT-PAY-DEP', 'is_allocated' => true]);

    // Expenses in August 2026
    \App\Models\Expense::create(['account_id' => $account->id, 'property_id' => $property->id, 'category' => 'repairs', 'description' => 'Plumbing fix', 'amount' => '1234.56', 'payment_method' => 'cash', 'expense_date' => '2026-08-14']);
    \App\Models\Expense::create(['account_id' => $account->id, 'property_id' => $property->id, 'category' => 'utilities', 'description' => 'Common area electricity', 'amount' => '999.99', 'payment_method' => 'cash', 'expense_date' => '2026-08-15']);

    $unitIds  = [$unit1->id, $unit2->id, $unit3->id];
    $leaseIds = [$lease1->id, $lease2->id, $lease3->id];

    $reportCtrl = app(\App\Http\Controllers\ReportController::class);

    // --- depositsSnapshot ---
    $deposits = $callPrivate($reportCtrl, 'depositsSnapshot', [$unitIds]);
    $check('depositsSnapshot: totalRequired === 45000.00', $deposits['totalRequired'] === 45000.00);
    $check('depositsSnapshot: totalHeld === 18333.33', $deposits['totalHeld'] === 18333.33);
    $check('depositsSnapshot: totalOutstanding === 26666.67', $deposits['totalOutstanding'] === 26666.67);

    // --- outstandingSnapshot ---
    $outstanding = $callPrivate($reportCtrl, 'outstandingSnapshot', [$unitIds]);
    $check('outstandingSnapshot: only U1 has a positive balance', $outstanding['leases']->count() === 1);
    $check('outstandingSnapshot: U1 balance === 3333.33', abs($outstanding['leases']->first()['balance'] - 3333.33) < 0.001);
    $check('outstandingSnapshot: total === 3333.33', abs($outstanding['total'] - 3333.33) < 0.001);

    // --- rentRollForPeriod ---
    $rentRoll = $callPrivate($reportCtrl, 'rentRollForPeriod', [$property, 8, 2026]);
    $check('rentRollForPeriod: totalExpected === 45000.00 (9999.99 + 15000.00 + 20000.01)', $rentRoll['totalExpected'] === 45000.00);
    $check('rentRollForPeriod: totalCollected === 21666.66', $rentRoll['totalCollected'] === 21666.66);
    $check('rentRollForPeriod: totalOutstanding === 23333.34', $rentRoll['totalOutstanding'] === 23333.34);
    $check('rentRollForPeriod: collectionRate === 48.1', $rentRoll['collectionRate'] === 48.1);

    // --- collectionsForPeriod ---
    $collections = $callPrivate($reportCtrl, 'collectionsForPeriod', [$leaseIds, 8, 2026]);
    $check('collectionsForPeriod: totalCollected === 21666.66 (deposit excluded)', $collections['totalCollected'] === 21666.66);
    $check('collectionsForPeriod: mpesa method amount === 6666.66', $collections['byMethod']['mpesa']['amount'] === 6666.66);
    $check('collectionsForPeriod: cash method amount === 15000.00', $collections['byMethod']['cash']['amount'] === 15000.00);

    // --- incomeExpensesForPeriod ---
    $incomeExpenses = $callPrivate($reportCtrl, 'incomeExpensesForPeriod', [$property, $leaseIds, 8, 2026]);
    $check('incomeExpensesForPeriod: totalIncome === 21666.66', $incomeExpenses['totalIncome'] === 21666.66);
    $check('incomeExpensesForPeriod: totalExpenses === 2234.55', $incomeExpenses['totalExpenses'] === 2234.55);
    $check('incomeExpensesForPeriod: netProfit === 19432.11', $incomeExpenses['netProfit'] === 19432.11);
    $check('incomeExpensesForPeriod: repairs category === 1234.56', $incomeExpenses['byCategory']['repairs'] === 1234.56);
    $check('incomeExpensesForPeriod: utilities category === 999.99', $incomeExpenses['byCategory']['utilities'] === 999.99);

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