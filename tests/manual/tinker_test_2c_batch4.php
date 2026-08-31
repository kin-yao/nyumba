<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/**
 * Manual validation for the last four ReportController methods:
 * collections(), incomeExpenses(), tenantStatement(), deposits(). This
 * closes out the entire file.
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

echo "\n=== ReportController: collections() / incomeExpenses() / tenantStatement() / deposits() ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account = \App\Models\Account::create(['name' => 'Tinker Report Co 4', 'phone' => '0700000013']);
    $user    = \App\Models\User::factory()->create(['account_id' => $account->id, 'role' => 'owner']);
    \Illuminate\Support\Facades\Auth::login($user);

    $property = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'Report Property 4', 'type' => 'residential']);

    $unitX = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'X', 'type' => 'bedsitter', 'rent_amount' => '6666.67', 'deposit_amount' => '6666.67']);
    $unitY = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'Y', 'type' => 'bedsitter', 'rent_amount' => '8888.89', 'deposit_amount' => '8888.89']);

    $tenantX = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'X', 'last_name' => 'Tenant', 'phone' => '0799999991']);
    $tenantY = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'Y', 'last_name' => 'Tenant', 'phone' => '0799999992']);

    $leaseX = \App\Models\Lease::create(['unit_id' => $unitX->id, 'tenant_id' => $tenantX->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '6666.67', 'deposit_required' => '6666.67', 'deposit_paid' => '2222.22', 'status' => 'active']);
    $leaseY = \App\Models\Lease::create(['unit_id' => $unitY->id, 'tenant_id' => $tenantY->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '8888.89', 'deposit_required' => '8888.89', 'deposit_paid' => '8888.89', 'status' => 'active']);

    $invoiceX = \App\Models\Invoice::create(['account_id' => $account->id, 'lease_id' => $leaseX->id, 'reference' => 'FIN-INV-X', 'period_month' => 8, 'period_year' => 2026, 'invoice_date' => '2026-08-01', 'due_date' => '2026-08-05', 'total_amount' => '6666.67', 'amount_paid' => '3333.33', 'status' => 'partial']);
    \App\Models\InvoiceLineItem::create(['invoice_id' => $invoiceX->id, 'description' => 'Rent', 'quantity' => '1.00', 'unit_price' => '6666.67', 'amount' => '6666.67', 'type' => 'rent']);

    \App\Models\Payment::create(['account_id' => $account->id, 'lease_id' => $leaseX->id, 'tenant_id' => $tenantX->id, 'amount' => '3333.33', 'payment_date' => '2026-08-10', 'payment_type' => 'rent', 'method' => 'mpesa', 'reference' => 'FIN-PAY-X', 'is_allocated' => true]);
    \App\Models\Payment::create(['account_id' => $account->id, 'lease_id' => $leaseX->id, 'tenant_id' => $tenantX->id, 'amount' => '2222.22', 'payment_date' => '2026-08-02', 'payment_type' => 'deposit', 'method' => 'mpesa', 'reference' => 'FIN-PAY-X-DEP', 'is_allocated' => true]);
    \App\Models\Payment::create(['account_id' => $account->id, 'lease_id' => $leaseY->id, 'tenant_id' => $tenantY->id, 'amount' => '4444.44', 'payment_date' => '2026-08-11', 'payment_type' => 'rent', 'method' => 'cash', 'reference' => 'FIN-PAY-Y', 'is_allocated' => true]);
    // Lease Y's deposit was recorded directly on the lease with no matching
    // Payment row — deliberately, to test paid_via_payments legitimately
    // differing from paid_on_lease.

    \App\Models\Expense::create(['account_id' => $account->id, 'property_id' => $property->id, 'category' => 'repairs', 'description' => 'Fix', 'amount' => '111.11', 'payment_method' => 'cash', 'expense_date' => '2026-08-15']);
    \App\Models\Expense::create(['account_id' => $account->id, 'property_id' => $property->id, 'category' => 'other', 'description' => 'Misc', 'amount' => '222.22', 'payment_method' => 'cash', 'expense_date' => '2026-08-16']);

    $reportCtrl = app(\App\Http\Controllers\ReportController::class);

    // --- collections() ---
    $collectionsResponse = $reportCtrl->collections(\Illuminate\Http\Request::create('/reports/collections', 'GET', ['month' => 8, 'year' => 2026]));
    $collectionsData      = $collectionsResponse->getData();

    $check('collections(): totalCollected === 7777.77 (deposits excluded)', $collectionsData['totalCollected'] === 7777.77);
    $check('collections(): mpesa amount === 3333.33', $collectionsData['byMethod']['mpesa']['amount'] === 3333.33);
    $check('collections(): cash amount === 4444.44', $collectionsData['byMethod']['cash']['amount'] === 4444.44);

    // --- incomeExpenses() ---
    $incomeExpensesResponse = $reportCtrl->incomeExpenses(\Illuminate\Http\Request::create('/reports/income-expenses', 'GET', ['month' => 8, 'year' => 2026]));
    $incomeExpensesData      = $incomeExpensesResponse->getData();

    $check('incomeExpenses(): payments (income) === 7777.77', $incomeExpensesData['payments'] === 7777.77);
    $check('incomeExpenses(): totalExpenses === 333.33', $incomeExpensesData['totalExpenses'] === 333.33);
    $check('incomeExpenses(): netProfit === 7444.44', $incomeExpensesData['netProfit'] === 7444.44);
    $check('incomeExpenses(): other category === 222.22', $incomeExpensesData['expensesByCategory']['other'] === 222.22);
    $check('incomeExpenses(): repairs category === 111.11', $incomeExpensesData['expensesByCategory']['repairs'] === 111.11);

    // --- tenantStatement() ---
    $statementResponse = $reportCtrl->tenantStatement(\Illuminate\Http\Request::create('/reports/tenant-statement', 'GET', ['tenant_id' => $tenantX->id]));
    $statementData      = $statementResponse->getData();

    $check('tenantStatement(): ledger has 3 entries (1 charge + 2 payments)', $statementData['ledger']->count() === 3);
    $check('tenantStatement(): balance === 3333.34 (6666.67 charged - 3333.33 rent paid, deposit excluded)', abs($statementData['balance'] - 3333.34) < 0.001);

    // --- deposits() ---
    $depositsResponse = $reportCtrl->deposits(\Illuminate\Http\Request::create('/reports/deposits', 'GET'));
    $depositsData      = $depositsResponse->getData();

    $leaseXRow = $depositsData['leases']->firstWhere('lease.id', $leaseX->id);
    $leaseYRow = $depositsData['leases']->firstWhere('lease.id', $leaseY->id);

    $check('deposits(): lease X required === 6666.67', $leaseXRow['required'] === 6666.67);
    $check('deposits(): lease X paid_on_lease === 2222.22', $leaseXRow['paid_on_lease'] === 2222.22);
    $check('deposits(): lease X paid_via_payments === 2222.22', $leaseXRow['paid_via_payments'] === 2222.22);
    $check('deposits(): lease X outstanding === 4444.45', abs($leaseXRow['outstanding'] - 4444.45) < 0.001);
    $check('deposits(): lease X status === "partial"', $leaseXRow['status'] === 'partial');

    $check('deposits(): lease Y paid_via_payments === 0.0 (no Payment row, only the lease column)', $leaseYRow['paid_via_payments'] === 0.0);
    $check('deposits(): lease Y outstanding === 0.0 (fully paid)', $leaseYRow['outstanding'] === 0.0);
    $check('deposits(): lease Y status === "paid"', $leaseYRow['status'] === 'paid');

    $check('deposits(): totalRequired === 15555.56', abs($depositsData['totalRequired'] - 15555.56) < 0.001);
    $check('deposits(): totalHeld === 11111.11', abs($depositsData['totalHeld'] - 11111.11) < 0.001);
    $check('deposits(): totalOutstanding === 4444.45', abs($depositsData['totalOutstanding'] - 4444.45) < 0.001);

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