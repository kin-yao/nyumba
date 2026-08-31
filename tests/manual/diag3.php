<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n=== Diagnostic: CSV import ===\n";

\Illuminate\Support\Facades\DB::beginTransaction();

try {
    $account  = \App\Models\Account::create(['name' => 'Diag Import Co', 'phone' => '0700000170']);
    $adminUser = \App\Models\User::factory()->create(['account_id' => $account->id, 'role' => 'owner', 'is_admin' => true]);
    $property = \App\Models\Property::create(['account_id' => $account->id, 'name' => 'Diag Import Property', 'type' => 'residential']);
    \Illuminate\Support\Facades\Auth::login($adminUser);

    $csvContent = "unit_name,unit_type,rent_amount,deposit_amount,payment_reference,first_name,last_name,phone\n"
        . "X2,bedsitter,4500.00,4500.00,KLM-X2,,,\n"
        . "X3,bedsitter,3500.00,3500.00,,,,\n";
    $tmpPath = tempnam(sys_get_temp_dir(), 'ref_csv_');
    file_put_contents($tmpPath, $csvContent);
    $uploadedFile = new \Illuminate\Http\UploadedFile($tmpPath, 'import.csv', 'text/csv', null, true);

    $importCtrl = app(\App\Http\Controllers\ImportController::class);

    $previewReq = \Illuminate\Http\Request::create('/properties/' . $property->id . '/import/preview', 'POST');
    $previewReq->files->set('csv_file', $uploadedFile);
    $previewResponse = $importCtrl->preview($previewReq, $property);

    echo "preview() response type: " . get_class($previewResponse) . "\n";

    if ($previewResponse instanceof \Illuminate\Http\RedirectResponse) {
        echo "Redirected. Session flash 'error': " . var_export(session('error'), true) . "\n";
        echo "Session flash 'success': " . var_export(session('success'), true) . "\n";
    }

    $rows = session('import_rows') ?? session('bulk_rows') ?? null;
    echo "session('import_rows'): " . var_export($rows, true) . "\n";

    // Dump every session key so we can see the actual key name used
    echo "\nAll session keys after preview():\n";
    foreach (session()->all() as $key => $value) {
        echo "  $key => " . (is_scalar($value) ? var_export($value, true) : gettype($value)) . "\n";
    }

    @unlink($tmpPath);

} catch (\Throwable $e) {
    echo "\n!! EXCEPTION: " . get_class($e) . ": " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
} finally {
    \Illuminate\Support\Facades\DB::rollBack();
    echo "\nRolled back.\n";
}