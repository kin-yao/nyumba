<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/**
 * Manual validation for Chunk 2A: decimal-safe money.
 * Covers Money helper, Invoice::getBalanceAttribute(), MpesaC2BController::
 * processTransaction() (multi-invoice, partial, overpay/credit-carry,
 * credit re-absorption, malformed input, duplicate detection), and
 * PaymentController::store() as a representative of the three PaymentController
 * methods (assign()/verifyProof() share the identical allocation code path
 * already exercised here and by processTransaction(), so they aren't
 * separately re-tested — say the word if you want them covered too).
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

echo "\n=== Part 1: Money helper (no DB) ===\n";

$naiveFloat = 0.1 + 0.2;
$check('sanity check: PHP float 0.1 + 0.2 !== 0.3 (the classic trap this fix exists to avoid)', $naiveFloat !== 0.3);
$check('Money::add("0.10", "0.20") === "0.30" exactly', \App\Support\Money::add('0.10', '0.20') === '0.30');
$check('Money::sub("100.00", "33.33") === "66.67"', \App\Support\Money::sub('100.00', '33.33') === '66.67');
$check('Money::min("50.00", "20.00") === "20.00"', \App\Support\Money::min('50.00', '20.00') === '20.00');
$check('Money::max("50.00", "20.00") === "50.00"', \App\Support\Money::max('50.00', '20.00') === '50.00');
$check('Money::lte("20.00", "20.00") === true', \App\Support\Money::lte('20.00', '20.00') === true);
$check('Money::gte("19.99", "20.00") === false', \App\Support\Money::gte('19.99', '20.00') === false);
$check('Money::isPositive("0.00") === false', \App\Support\Money::isPositive('0.00') === false);
$check('Money::isPositive("0.01") === true', \App\Support\Money::isPositive('0.01') === true);
$check('Money::normalize(null) === "0.00"', \App\Support\Money::normalize(null) === '0.00');
$check('Money::normalize(1500) === "1500.00" (int input)', \App\Support\Money::normalize(1500) === '1500.00');

$threw = false;
try {
    \App\Support\Money::normalize('not-a-number');
} catch (\InvalidArgumentException $e) {
    $threw = true;
}
$check('Money::normalize("not-a-number") throws InvalidArgumentException', $threw);

echo "\n=== Part 2-4: DB-backed flows (transaction, rolled back at the end) ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    // --- Fixture ---
    $account = \App\Models\Account::create([
        'name'  => 'Tinker Test Landlord',
        'phone' => '0700000000',
    ]);

    $user = \App\Models\User::factory()->create([
        'account_id' => $account->id,
    ]);
    \Illuminate\Support\Facades\Auth::login($user);

    $property = \App\Models\Property::create([
        'account_id' => $account->id,
        'name'       => 'Tinker Test Property',
        'type'       => 'residential',
    ]);

    $unit = \App\Models\Unit::create([
        'property_id'    => $property->id,
        'name'           => 'A1',
        'type'           => 'bedsitter',
        'rent_amount'    => '15000.00',
        'deposit_amount' => '15000.00',
    ]);

    $tenant = \App\Models\Tenant::create([
        'account_id' => $account->id,
        'first_name' => 'Jane',
        'last_name'  => 'Doe',
        'phone'      => '0712345678',
    ]);

    $lease = \App\Models\Lease::create([
        'unit_id'           => $unit->id,
        'tenant_id'         => $tenant->id,
        'move_in_date'      => now()->subMonths(3)->toDateString(),
        'monthly_rent'      => '15000.00',
        'deposit_required'  => '15000.00',
        'status'            => 'active',
    ]);

    // Only A and B exist to start with — C is deliberately created later,
    // between payment 2 and payment 3, so payment 2's leftover 0.01 has
    // genuinely nothing outstanding to absorb it and must become a real
    // carried-forward credit rather than just flowing into the next invoice.
    $invoiceA = \App\Models\Invoice::create([
        'account_id'   => $account->id,
        'lease_id'     => $lease->id,
        'reference'    => 'TINKER-INV-A',
        'period_month' => 1, 'period_year' => 2026,
        'invoice_date' => now()->subDays(2)->toDateString(),
        'due_date'     => now()->addDays(5)->toDateString(),
        'total_amount' => '15000.00',
        'status'       => 'sent',
    ]);

    $invoiceB = \App\Models\Invoice::create([
        'account_id'   => $account->id,
        'lease_id'     => $lease->id,
        'reference'    => 'TINKER-INV-B',
        'period_month' => 2, 'period_year' => 2026,
        'invoice_date' => now()->subDays(1)->toDateString(),
        'due_date'     => now()->addDays(10)->toDateString(),
        'total_amount' => '15000.00',
        'status'       => 'sent',
    ]);

    echo "\n-- Invoice::getBalanceAttribute() --\n";
    $check('Invoice A balance === "15000.00" (string) before any payment', $invoiceA->fresh()->balance === '15000.00');

    echo "\n-- MpesaC2BController::processTransaction() --\n";
    $c2b = app(\App\Http\Controllers\MpesaC2BController::class);

    // Payment 1: 20000.50 -> fully pays A (15000.00), partially pays B (5000.50)
    $result1 = $c2b->processTransaction($property, [
        'TransID'           => 'TINKERTEST0001',
        'TransAmount'       => '20000.50',
        'BillRefNumber'     => 'A1',
        'MSISDN'            => '254712345678',
        'BusinessShortCode' => '000000',
    ]);
    $check('Payment 1 (20000.50) returns "matched"', $result1 === 'matched');
    $check('Invoice A is "paid" after payment 1', $invoiceA->fresh()->status === 'paid');
    $check('Invoice A amount_paid === "15000.00"', $invoiceA->fresh()->amount_paid === '15000.00');
    $check('Invoice B is "partial" after payment 1', $invoiceB->fresh()->status === 'partial');
    $check('Invoice B amount_paid === "5000.50"', $invoiceB->fresh()->amount_paid === '5000.50');

    // Payment 2: 9999.51 -> finishes B (needs exactly 9999.50), leaves 0.01
    // with nothing outstanding left to absorb it (C doesn't exist yet).
    $result2 = $c2b->processTransaction($property, [
        'TransID'           => 'TINKERTEST0002',
        'TransAmount'       => '9999.51',
        'BillRefNumber'     => 'A1',
        'MSISDN'            => '254712345678',
        'BusinessShortCode' => '000000',
    ]);
    $check('Payment 2 (9999.51) returns "matched"', $result2 === 'matched');
    $check('Invoice B is "paid" after payment 2', $invoiceB->fresh()->status === 'paid');
    $check('Invoice B amount_paid === "15000.00"', $invoiceB->fresh()->amount_paid === '15000.00');

    $creditAfterP2 = \App\Models\Payment::withoutGlobalScopes()
        ->where('reference', 'TINKERTEST0002-CR')
        ->first();
    $check('A 0.01 credit was carried forward after payment 2', $creditAfterP2 !== null && $creditAfterP2->amount === '0.01');
    $check('The carried credit is not yet allocated', $creditAfterP2 && $creditAfterP2->is_allocated === false);

    // NOW create invoice C, simulating next month's invoice landing after
    // the credit already exists.
    $invoiceC = \App\Models\Invoice::create([
        'account_id'   => $account->id,
        'lease_id'     => $lease->id,
        'reference'    => 'TINKER-INV-C',
        'period_month' => 3, 'period_year' => 2026,
        'invoice_date' => now()->toDateString(),
        'due_date'     => now()->addDays(15)->toDateString(),
        'total_amount' => '15000.00',
        'status'       => 'sent',
    ]);

    // Payment 3: 14999.99, plus the 0.01 credit from payment 2 = exactly
    // 15000.00, exactly covering C with nothing left over.
    $result3 = $c2b->processTransaction($property, [
        'TransID'           => 'TINKERTEST0003',
        'TransAmount'       => '14999.99',
        'BillRefNumber'     => 'A1',
        'MSISDN'            => '254712345678',
        'BusinessShortCode' => '000000',
    ]);
    $check('Payment 3 (14999.99) returns "matched"', $result3 === 'matched');
    $check('Invoice C is "paid" after payment 3', $invoiceC->fresh()->status === 'paid');
    $check('Invoice C amount_paid === "15000.00"', $invoiceC->fresh()->amount_paid === '15000.00');
    $check('The old 0.01 credit is now marked allocated', $creditAfterP2 && $creditAfterP2->fresh()->is_allocated === true);

    $unallocatedCreditSum = \App\Models\Payment::withoutGlobalScopes()
        ->where('lease_id', $lease->id)
        ->where('reference', 'like', '%-CR')
        ->where('is_allocated', false)
        ->sum('amount');
    $check('No leftover unallocated credit remains after payment 3', (float) $unallocatedCreditSum === 0.0);

    $totalPaidAcrossInvoices = \App\Models\Invoice::withoutGlobalScopes()
        ->where('lease_id', $lease->id)
        ->sum('amount_paid');
    $check('Sum of all three invoices amount_paid === 45000.00 exactly (3 x 15000.00, across 3 odd-cent payments)', (float) $totalPaidAcrossInvoices === 45000.00);

    // Regression check: the bug caught and fixed during self-review, a
    // malformed amount must degrade to 'unmatched', not throw.
    $threwOnMalformed = false;
    $malformedResult  = null;
    try {
        $malformedResult = $c2b->processTransaction($property, [
            'TransID'           => 'TINKERTEST-BAD',
            'TransAmount'       => 'not-a-number',
            'BillRefNumber'     => 'A1',
            'MSISDN'            => '254712345678',
            'BusinessShortCode' => '000000',
        ]);
    } catch (\Throwable $e) {
        $threwOnMalformed = true;
    }
    $check('A non-numeric TransAmount does NOT throw', !$threwOnMalformed);
    $check('A non-numeric TransAmount returns "unmatched"', $malformedResult === 'unmatched');
    $check('No Payment row was created for the malformed transaction',
        \App\Models\Payment::withoutGlobalScopes()->where('reference', 'TINKERTEST-BAD')->doesntExist());

    // Duplicate detection (unchanged by this chunk, confirming it still works)
    $dupResult = $c2b->processTransaction($property, [
        'TransID'           => 'TINKERTEST0001',
        'TransAmount'       => '20000.50',
        'BillRefNumber'     => 'A1',
        'MSISDN'            => '254712345678',
        'BusinessShortCode' => '000000',
    ]);
    $check('Re-sending TransID TINKERTEST0001 returns "duplicate"', $dupResult === 'duplicate');

    echo "\n-- PaymentController::store() (manual cash payment) --\n";

    $tenantD = \App\Models\Tenant::create([
        'account_id' => $account->id,
        'first_name' => 'Kevin',
        'last_name'  => 'Otieno',
        'phone'      => '0722334455',
    ]);

    $unitD = \App\Models\Unit::create([
        'property_id'    => $property->id,
        'name'           => 'B2',
        'type'           => 'bedsitter',
        'rent_amount'    => '12345.67',
        'deposit_amount' => '12345.67',
    ]);

    $leaseD = \App\Models\Lease::create([
        'unit_id'          => $unitD->id,
        'tenant_id'        => $tenantD->id,
        'move_in_date'     => now()->subMonth()->toDateString(),
        'monthly_rent'     => '12345.67',
        'deposit_required' => '12345.67',
        'status'           => 'active',
    ]);

    $invoiceD = \App\Models\Invoice::create([
        'account_id'   => $account->id,
        'lease_id'     => $leaseD->id,
        'reference'    => 'TINKER-INV-D',
        'period_month' => 1, 'period_year' => 2026,
        'invoice_date' => now()->subDays(1)->toDateString(),
        'due_date'     => now()->addDays(10)->toDateString(),
        'total_amount' => '12345.67',
        'status'       => 'sent',
    ]);

    $request = \Illuminate\Http\Request::create('/payments', 'POST', [
        'tenant_id'    => $tenantD->id,
        'payment_type' => 'rent',
        'amount'       => '12345.67',
        'payment_date' => now()->toDateString(),
        'method'       => 'cash',
        'reference'    => 'TINKER-CASH-1',
    ]);

    app(\App\Http\Controllers\PaymentController::class)->store($request);

    $check('Invoice D is "paid" after the manual cash payment', $invoiceD->fresh()->status === 'paid');
    $check('Invoice D amount_paid === "12345.67" exactly', $invoiceD->fresh()->amount_paid === '12345.67');

    echo "\n=== Result: {$checks} checks, " . ($checks - $failures) . " passed, {$failures} failed ===\n";

} catch (\Throwable $e) {
    $failures++;
    echo "\n!! EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} finally {
    \Illuminate\Support\Facades\DB::rollBack();
    echo "\nTransaction rolled back, no data left behind.\n";
    if ($failures > 0) {
        echo "\n{$failures} FAILURE(S). Do not consider Chunk 2A verified until this is clean.\n";
    } else {
        echo "\nAll checks passed.\n";
    }
}