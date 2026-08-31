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

echo "\n=== PaymentController: store() / assign() / verifyProof() after locking ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account = \App\Models\Account::create(['name' => 'Lock PC Co', 'phone' => '0700000100']);
    $user    = \App\Models\User::factory()->create(['account_id' => $account->id, 'role' => 'owner']);
    \Illuminate\Support\Facades\Auth::login($user);
    $property = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'Lock PC Property', 'type' => 'residential']);

    // store()
    $unitA = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'PA', 'type' => 'bedsitter', 'rent_amount' => '3000.00', 'deposit_amount' => '3000.00']);
    $tenantA = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'A', 'last_name' => 'One', 'phone' => '0700010001']);
    $leaseA = \App\Models\Lease::create(['unit_id' => $unitA->id, 'tenant_id' => $tenantA->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '3000.00', 'deposit_required' => '3000.00', 'status' => 'active']);
    \App\Models\Invoice::create(['account_id' => $account->id, 'lease_id' => $leaseA->id, 'reference' => 'PC-INV-A', 'period_month' => 8, 'period_year' => 2026, 'invoice_date' => '2026-08-01', 'due_date' => '2026-08-05', 'total_amount' => '3000.00', 'status' => 'sent']);

    $pc = app(\App\Http\Controllers\PaymentController::class);
    $req = \Illuminate\Http\Request::create('/payments', 'POST', [
        'tenant_id' => $tenantA->id, 'payment_type' => 'rent', 'amount' => '3000.00',
        'payment_date' => '2026-08-10', 'method' => 'cash', 'reference' => 'PC-PAY-A',
    ]);
    $pc->store($req);
    $invA = \App\Models\Invoice::where('lease_id', $leaseA->id)->first();
    $check('store(): invoice paid correctly', $invA && $invA->status === 'paid' && $invA->amount_paid === '3000.00');

    // assign()
    $unitB = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'PB', 'type' => 'bedsitter', 'rent_amount' => '4000.00', 'deposit_amount' => '4000.00']);
    $tenantB = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'B', 'last_name' => 'Two', 'phone' => '0700010002']);
    $leaseB = \App\Models\Lease::create(['unit_id' => $unitB->id, 'tenant_id' => $tenantB->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '4000.00', 'deposit_required' => '4000.00', 'status' => 'active']);
    \App\Models\Invoice::create(['account_id' => $account->id, 'lease_id' => $leaseB->id, 'reference' => 'PC-INV-B', 'period_month' => 8, 'period_year' => 2026, 'invoice_date' => '2026-08-01', 'due_date' => '2026-08-05', 'total_amount' => '4000.00', 'status' => 'sent']);
    $unmatchedPayment = \App\Models\Payment::create(['account_id' => $account->id, 'amount' => '4000.00', 'payment_date' => '2026-08-10', 'payment_type' => 'rent', 'method' => 'mpesa', 'reference' => 'PC-PAY-B', 'is_allocated' => false]);

    $assignReq = \Illuminate\Http\Request::create('/payments/' . $unmatchedPayment->id . '/assign', 'POST', ['tenant_id' => $tenantB->id]);
    $pc->assign($assignReq, $unmatchedPayment);
    $invB = \App\Models\Invoice::where('lease_id', $leaseB->id)->first();
    $check('assign(): invoice paid correctly', $invB && $invB->status === 'paid' && $invB->amount_paid === '4000.00');

    // verifyProof()
    $unitC = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'PC', 'type' => 'bedsitter', 'rent_amount' => '2500.00', 'deposit_amount' => '2500.00']);
    $tenantC = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'C', 'last_name' => 'Three', 'phone' => '0700010003']);
    $leaseC = \App\Models\Lease::create(['unit_id' => $unitC->id, 'tenant_id' => $tenantC->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '2500.00', 'deposit_required' => '2500.00', 'status' => 'active']);
    \App\Models\Invoice::create(['account_id' => $account->id, 'lease_id' => $leaseC->id, 'reference' => 'PC-INV-C', 'period_month' => 8, 'period_year' => 2026, 'invoice_date' => '2026-08-01', 'due_date' => '2026-08-05', 'total_amount' => '2500.00', 'status' => 'sent']);
    $proof = \App\Models\ProofOfPayment::create(['account_id' => $account->id, 'tenant_id' => $tenantC->id, 'lease_id' => $leaseC->id, 'payment_for' => 'rent', 'method' => 'cash', 'message' => 'paid', 'status' => 'pending']);

    $verifyReq = \Illuminate\Http\Request::create('/proofs/' . $proof->id . '/verify', 'POST', ['amount' => '2500.00', 'payment_date' => '2026-08-10']);
    $pc->verifyProof($verifyReq, $proof);
    $invC = \App\Models\Invoice::where('lease_id', $leaseC->id)->first();
    $check('verifyProof(): invoice paid correctly', $invC && $invC->status === 'paid' && $invC->amount_paid === '2500.00');

    echo "\n=== Result: {$checks} checks, " . ($checks - $failures) . " passed, {$failures} failed ===\n";

} catch (\Throwable $e) {
    $failures++;
    echo "\n!! EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
} finally {
    \Illuminate\Support\Facades\DB::rollBack();
    echo "Rolled back, no data left behind.\n";
    echo $failures > 0 ? "\n{$failures} FAILURE(S).\n" : "\nAll checks passed.\n";
}