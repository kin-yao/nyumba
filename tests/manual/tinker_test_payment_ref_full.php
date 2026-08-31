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

echo "\n=== Full payment reference chain: manual edit, CSV import, Pesalink matching, reconciliation ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account  = \App\Models\Account::create(['name' => 'Ref Chain Co', 'phone' => '0700000160']);
    $adminUser = \App\Models\User::factory()->create(['account_id' => $account->id, 'role' => 'owner', 'is_admin' => true]);
    $property = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'Ref Chain Property', 'type' => 'residential', 'ipsl_password' => 'chain_secret']);
    \Illuminate\Support\Facades\Auth::login($adminUser);

    // --- X1: created plainly, reference set via the manual inline-edit route ---
    $x1 = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'X1', 'type' => 'bedsitter', 'rent_amount' => '5000.00', 'deposit_amount' => '5000.00']);
    $unitCtrl = app(\App\Http\Controllers\UnitController::class);
    $refReq = \Illuminate\Http\Request::create('/units/' . $x1->id . '/reference', 'PATCH', ['payment_reference' => 'KLM-X1']);
    $unitCtrl->updateReference($refReq, $x1);
    $check('Manual edit: X1 payment_reference saved', $x1->fresh()->payment_reference === 'KLM-X1');

    // --- X2, X3: created via CSV import, one with a reference set, one blank ---
    $csvContent = "unit_name,unit_type,rent_amount,deposit_amount,payment_reference,first_name,last_name,phone\n"
        . "X2,bedsitter,4500.00,4500.00,KLM-X2,,,\n"
        . "X3,bedsitter,3500.00,3500.00,,,,\n";
    $tmpPath = tempnam(sys_get_temp_dir(), 'ref_csv_');
    file_put_contents($tmpPath, $csvContent);
    $uploadedFile = new \Illuminate\Http\UploadedFile($tmpPath, 'import.csv', 'text/csv', null, true);

    $importCtrl = app(\App\Http\Controllers\ImportController::class);

    $previewReq = \Illuminate\Http\Request::create('/properties/' . $property->id . '/import/preview', 'POST');
    $previewReq->files->set('csv_file', $uploadedFile);
    $importCtrl->preview($previewReq, $property);

    $storeReq = \Illuminate\Http\Request::create('/properties/' . $property->id . '/import/store', 'POST');
    $importCtrl->store($storeReq, $property);
    @unlink($tmpPath);

    $x2 = \App\Models\Unit::where('property_id', $property->id)->where('name', 'X2')->first();
    $x3 = \App\Models\Unit::where('property_id', $property->id)->where('name', 'X3')->first();

    $check('CSV import: X2 created', $x2 !== null);
    $check('CSV import: X2 payment_reference saved as "KLM-X2"', $x2 && $x2->payment_reference === 'KLM-X2');
    $check('CSV import: X3 created', $x3 !== null);
    $check('CSV import: X3 payment_reference left blank (falls back to name)', $x3 && $x3->payment_reference === null);

    // --- Leases + invoices for all three, needed for the reconciliation check below ---
    $tenantX1 = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'T', 'last_name' => 'X1', 'phone' => '0700060001']);
    $tenantX2 = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'T', 'last_name' => 'X2', 'phone' => '0700060002']);
    $leaseX1  = \App\Models\Lease::create(['unit_id' => $x1->id, 'tenant_id' => $tenantX1->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '5000.00', 'deposit_required' => '5000.00', 'status' => 'active']);
    $leaseX2  = \App\Models\Lease::create(['unit_id' => $x2->id, 'tenant_id' => $tenantX2->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '4500.00', 'deposit_required' => '4500.00', 'status' => 'active']);
    \App\Models\Invoice::create(['account_id' => $account->id, 'lease_id' => $leaseX2->id, 'reference' => 'REF-INV-X2', 'period_month' => 8, 'period_year' => 2026, 'invoice_date' => '2026-08-01', 'due_date' => '2026-08-05', 'total_amount' => '4500.00', 'status' => 'sent']);

    $ipsl = app(\App\Http\Controllers\IpslController::class);
    $reconciler = app(\App\Http\Controllers\MpesaC2BController::class);
    $jsonRequest = function (string $uri, array $data) {
        $json = json_encode($data);
        return \Illuminate\Http\Request::create($uri, 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], $json);
    };

    // --- IPSL validate() against all three: manual, CSV-set, CSV-blank ---
    $valX1 = $ipsl->validate($jsonRequest('/payments/ipsl/' . $property->id . '/validate', ['requestId' => 'R1', 'signature' => 'x', 'billRef' => 'klm x1', 'amount' => 100]), $property->id);
    $check('Pesalink matches X1 via manually-set reference', $valX1->getData()->status === 'valid');

    $valX2 = $ipsl->validate($jsonRequest('/payments/ipsl/' . $property->id . '/validate', ['requestId' => 'R2', 'signature' => 'x', 'billRef' => 'KLM#X2', 'amount' => 100]), $property->id);
    $check('Pesalink matches X2 via CSV-imported reference', $valX2->getData()->status === 'valid');

    $valX3 = $ipsl->validate($jsonRequest('/payments/ipsl/' . $property->id . '/validate', ['requestId' => 'R3', 'signature' => 'x', 'billRef' => 'x3', 'amount' => 100]), $property->id);
    $check('Pesalink matches X3 via fallback to unit name (CSV left it blank)', $valX3->getData()->status === 'valid');

    // --- Full reconciliation using the CSV-imported reference (X2) ---
    $body = [
        'sender' => 'T', 'recipient' => 'Ref Chain Property', 'bankSrc' => 'B1', 'bankDst' => 'B2',
        'accountSrc' => '1', 'accountDst' => '2', 'rrn' => 'REFCHAIN001', 'amount' => 4500.00,
        'paymentReason' => 'KLM-X2', 'status' => 'success', 'phoneSrc' => '254700060002', 'phoneDst' => '0',
        'date' => '2026-08-01 12:00:00', 'Bill_reference' => 'KLM-X2',
    ];
    $notifyReq = $jsonRequest('/payments/ipsl/' . $property->id . '/notification', $body);
    $notifyReq->headers->set('X-Signature', hash_hmac('sha1', $notifyReq->getContent(), 'chain_secret'));
    $ipsl->notification($notifyReq, $property->id, $reconciler);

    $invoiceX2 = \App\Models\Invoice::where('lease_id', $leaseX2->id)->first();
    $check('End-to-end: payment against CSV-imported unit reconciled correctly', $invoiceX2 && $invoiceX2->status === 'paid' && $invoiceX2->amount_paid === '4500.00');

    echo "\n=== Result: {$checks} checks, " . ($checks - $failures) . " passed, {$failures} failed ===\n";

} catch (\Throwable $e) {
    $failures++;
    echo "\n!! EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
} finally {
    \Illuminate\Support\Facades\DB::rollBack();
    echo "Rolled back, no data left behind.\n";
    echo $failures > 0 ? "\n{$failures} FAILURE(S).\n" : "\nAll checks passed.\n";
}