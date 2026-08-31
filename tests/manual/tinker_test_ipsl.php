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

// Builds a real JSON-body request, needed so getContent() returns the exact
// bytes we can independently HMAC for a genuine signature test.
$jsonRequest = function (string $uri, array $data) {
    $json = json_encode($data);
    return \Illuminate\Http\Request::create($uri, 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], $json);
};

echo "\n=== IPSL (Pesalink) ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account  = \App\Models\Account::create(['name' => 'IPSL Test Co', 'phone' => '0700000140']);
    $property = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'IPSL Property', 'type' => 'residential', 'ipsl_password' => 'ipsl_secret_key']);

    $unit1 = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'A1', 'payment_reference' => 'KLM-A1', 'type' => 'bedsitter', 'rent_amount' => '6000.00', 'deposit_amount' => '6000.00']);
    $unit2 = \App\Models\Unit::create(['property_id' => $property->id, 'name' => 'B2', 'type' => 'bedsitter', 'rent_amount' => '4000.00', 'deposit_amount' => '4000.00']); // no custom reference, falls back to name

    $tenant1 = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'Ipsl', 'last_name' => 'One', 'phone' => '0700050001']);
    $tenant2 = \App\Models\Tenant::create(['account_id' => $account->id, 'first_name' => 'Ipsl', 'last_name' => 'Two', 'phone' => '0700050002']);
    $lease1  = \App\Models\Lease::create(['unit_id' => $unit1->id, 'tenant_id' => $tenant1->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '6000.00', 'deposit_required' => '6000.00', 'status' => 'active']);
    $lease2  = \App\Models\Lease::create(['unit_id' => $unit2->id, 'tenant_id' => $tenant2->id, 'move_in_date' => '2026-06-01', 'monthly_rent' => '4000.00', 'deposit_required' => '4000.00', 'status' => 'active']);
    \App\Models\Invoice::create(['account_id' => $account->id, 'lease_id' => $lease1->id, 'reference' => 'IPSL-INV-1', 'period_month' => 8, 'period_year' => 2026, 'invoice_date' => '2026-08-01', 'due_date' => '2026-08-05', 'total_amount' => '6000.00', 'status' => 'sent']);
    \App\Models\Invoice::create(['account_id' => $account->id, 'lease_id' => $lease2->id, 'reference' => 'IPSL-INV-2', 'period_month' => 8, 'period_year' => 2026, 'invoice_date' => '2026-08-01', 'due_date' => '2026-08-05', 'total_amount' => '4000.00', 'status' => 'sent']);

    $ipsl = app(\App\Http\Controllers\IpslController::class);
    $reconciler = app(\App\Http\Controllers\MpesaC2BController::class);

    // --- validate(): matches via custom payment_reference, punctuation/case variants ---
    foreach (['KLM-A1', 'klm a1', 'KLM#A1', 'KLMA1'] as $variant) {
        $valReq = $jsonRequest('/payments/ipsl/' . $property->id . '/validate', [
            'requestId' => 'REQ-' . $variant, 'signature' => 'x', 'billRef' => $variant, 'amount' => 100.00,
        ]);
        $valResp = $ipsl->validate($valReq, $property->id);
        $check("validate(): '$variant' matches unit A1 via payment_reference", $valResp->getData()->status === 'valid');
    }

    // --- validate(): matches via fallback to unit name (no custom reference) ---
    $valReq2 = $jsonRequest('/payments/ipsl/' . $property->id . '/validate', [
        'requestId' => 'REQ-B2', 'signature' => 'x', 'billRef' => 'B2', 'amount' => 100.00,
    ]);
    $valResp2 = $ipsl->validate($valReq2, $property->id);
    $check('validate(): falls back to unit name when no payment_reference set', $valResp2->getData()->status === 'valid');

    // --- validate(): unmatched ---
    $valReq3 = $jsonRequest('/payments/ipsl/' . $property->id . '/validate', [
        'requestId' => 'REQ-BAD', 'signature' => 'x', 'billRef' => 'NOSUCHUNIT', 'amount' => 100.00,
    ]);
    $valResp3 = $ipsl->validate($valReq3, $property->id);
    $check('validate(): unmatched returns error status', $valResp3->getData()->status === 'error');

    // --- notification(): correct signature, reconciles the payment ---
    $body1 = [
        'sender' => 'Test Sender', 'recipient' => 'IPSL Property', 'bankSrc' => 'TESTBANK', 'bankDst' => 'TESTBANK2',
        'accountSrc' => '111', 'accountDst' => '222', 'rrn' => 'IPSLRRN001', 'amount' => 6000.00,
        'paymentReason' => 'KLM-A1', 'status' => 'success', 'phoneSrc' => '254700050001', 'phoneDst' => '254700000000',
        'date' => '2026-08-01 12:00:00', 'Bill_reference' => 'KLM-A1',
    ];
    $notifyReq1 = $jsonRequest('/payments/ipsl/' . $property->id . '/notification', $body1);
    $goodSig = hash_hmac('sha1', $notifyReq1->getContent(), 'ipsl_secret_key');
    $notifyReq1->headers->set('X-Signature', $goodSig);

    $notifyResp1 = $ipsl->notification($notifyReq1, $property->id, $reconciler);
    $check('notification(): correct signature returns SUCCESS', $notifyResp1->getData()->status === 'SUCCESS');
    $invoice1 = \App\Models\Invoice::where('lease_id', $lease1->id)->first();
    $check('notification(): invoice A1 paid correctly', $invoice1 && $invoice1->status === 'paid');

    // --- notification(): wrong signature ---
    $body2 = $body1;
    $body2['rrn'] = 'IPSLRRN002';
    $notifyReq2 = $jsonRequest('/payments/ipsl/' . $property->id . '/notification', $body2);
    $notifyReq2->headers->set('X-Signature', 'deadbeef0000000000000000000000000000000000');
    $notifyResp2 = $ipsl->notification($notifyReq2, $property->id, $reconciler);
    $check('notification(): wrong signature returns INSECURE, 401', $notifyResp2->getData()->status === 'INSECURE' && $notifyResp2->status() === 401);
    $check('notification(): wrong signature does not create a payment', \App\Models\Payment::withoutGlobalScopes()->where('reference', 'IPSLRRN002')->doesntExist());

    // --- notification(): non-success status is ignored ---
    $body3 = $body1;
    $body3['rrn'] = 'IPSLRRN003';
    $body3['status'] = 'RJCT';
    $notifyReq3 = $jsonRequest('/payments/ipsl/' . $property->id . '/notification', $body3);
    $notifyReq3->headers->set('X-Signature', hash_hmac('sha1', $notifyReq3->getContent(), 'ipsl_secret_key'));
    $notifyResp3 = $ipsl->notification($notifyReq3, $property->id, $reconciler);
    $check('notification(): RJCT status acknowledged but not reconciled', $notifyResp3->getData()->status === 'SUCCESS');
    $check('notification(): RJCT does not create a payment', \App\Models\Payment::withoutGlobalScopes()->where('reference', 'IPSLRRN003')->doesntExist());

    echo "\n=== Result: {$checks} checks, " . ($checks - $failures) . " passed, {$failures} failed ===\n";

} catch (\Throwable $e) {
    $failures++;
    echo "\n!! EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
} finally {
    \Illuminate\Support\Facades\DB::rollBack();
    echo "Rolled back, no data left behind.\n";
    echo $failures > 0 ? "\n{$failures} FAILURE(S).\n" : "\nAll checks passed.\n";
}