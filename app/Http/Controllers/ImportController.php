<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ImportController extends Controller
{
    // ── Sample CSV download ───────────────────────────────────────────────
    public function sampleCsv(Property $property)
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="import-sample.csv"',
        ];

        $rows = [
            [
                'unit_name','unit_type','rent_amount','deposit_amount',
                'first_name','last_name','phone','alt_phone','id_number','email',
                'move_in_date','lease_end_date','deposit_paid','deposit_method','notes'
            ],
            // Occupied unit example
            ['A1','1 bedroom','15000','30000','John','Doe','0712345678','','12345678','john@example.com','2024-01-01','','30000','mpesa',''],
            // Occupied with lease end date
            ['A2','2 bedroom','20000','40000','Jane','Smith','0787654321','0711111111','','jane@example.com','2024-03-01','2025-02-28','40000','cash','Fixed term'],
            // Vacant unit — leave tenant columns blank
            ['B1','Bedsitter','8000','16000','','','','','','','','','','','Vacant'],
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Parse CSV and show preview ────────────────────────────────────────
    public function preview(Request $request, Property $property)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ], [
            'csv_file.mimes' => 'Please upload a .csv file.',
            'csv_file.max'   => 'File must be under 5MB.',
        ]);

        $handle = fopen($request->file('csv_file')->getRealPath(), 'r');
        $header = fgetcsv($handle);

        if (!$header) {
            return back()->with('error', 'Could not read the CSV file. Make sure it is a valid CSV.');
        }

        // Normalize header keys
        $header = array_map(
            fn($h) => strtolower(trim(str_replace([' ', '-'], '_', $h))),
            $header
        );

        // Check required columns exist
        $required = ['unit_name', 'unit_type', 'rent_amount', 'deposit_amount'];
        $missing  = array_diff($required, $header);

        if (!empty($missing)) {
            fclose($handle);
            return back()->with('error',
                'Missing required columns: ' . implode(', ', $missing) . '. Download the sample CSV to see the correct format.'
            );
        }

        // Existing unit names for this property (for duplicate detection)
        $existingUnits = Unit::where('property_id', $property->id)
            ->pluck('name')
            ->map(fn($n) => strtolower(trim($n)))
            ->toArray();

        $rows      = [];
        $rowNumber = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $rowNumber++;

            // Skip blank rows
            if (count(array_filter($data, fn($v) => trim($v) !== '')) === 0) continue;

            // Map columns to keys
            $row = [];
            foreach ($header as $i => $key) {
                $row[$key] = trim($data[$i] ?? '');
            }

            $errors   = [];
            $warnings = [];
            $status   = 'ready';

            // ── Unit validation ──────────────────────────────────────────
            if (empty($row['unit_name'])) {
                $errors[] = 'Unit name is required';
            }
            if (empty($row['unit_type'])) {
                $errors[] = 'Unit type is required';
            }
            if (empty($row['rent_amount']) || !is_numeric($row['rent_amount']) || floatval($row['rent_amount']) < 0) {
                $errors[] = 'Rent amount must be a valid number';
            }
            if (empty($row['deposit_amount']) || !is_numeric($row['deposit_amount']) || floatval($row['deposit_amount']) < 0) {
                $errors[] = 'Deposit amount must be a valid number';
            }

            // ── Duplicate check ──────────────────────────────────────────
            if (!empty($row['unit_name']) && in_array(strtolower(trim($row['unit_name'])), $existingUnits)) {
                $status     = 'skip';
                $warnings[] = 'Unit "' . $row['unit_name'] . '" already exists in this property — will be skipped';
            }

            // ── Tenant validation (only if name provided) ────────────────
            $hasTenant = !empty($row['first_name']) || !empty($row['last_name']);

            if ($hasTenant) {
                if (empty($row['first_name'])) {
                    $errors[] = 'First name is required when adding a tenant';
                }
                if (empty($row['last_name'])) {
                    $errors[] = 'Last name is required when adding a tenant';
                }
                if (empty($row['phone'])) {
                    $errors[] = 'Tenant phone number is required';
                }
                if (empty($row['move_in_date'])) {
                    $warnings[] = 'Move-in date missing — today\'s date will be used';
                    if ($status === 'ready') $status = 'warning';
                } else {
                    try {
                        \Carbon\Carbon::parse($row['move_in_date']);
                    } catch (\Exception $e) {
                        $errors[] = 'Move-in date "' . $row['move_in_date'] . '" is not a valid date (use YYYY-MM-DD)';
                    }
                }

                $depositPaid = floatval($row['deposit_paid'] ?? 0);
                if ($depositPaid > 0) {
                    $validMethods = ['mpesa', 'cash', 'bank', 'cheque'];
                    $method       = strtolower($row['deposit_method'] ?? '');
                    if (!in_array($method, $validMethods)) {
                        $warnings[] = 'Deposit method "' . ($row['deposit_method'] ?: 'blank') . '" is not valid — cash will be used';
                        if ($status === 'ready') $status = 'warning';
                    }
                }
            }

            // Set final status
            if (!empty($errors) && $status !== 'skip') {
                $status = 'error';
            }

            $rows[] = [
                'row_number'     => $rowNumber,
                'unit_name'      => $row['unit_name']      ?? '',
                'unit_type'      => $row['unit_type']      ?? '',
                'rent_amount'    => $row['rent_amount']    ?? '',
                'deposit_amount' => $row['deposit_amount'] ?? '',
                'first_name'     => $row['first_name']     ?? '',
                'last_name'      => $row['last_name']      ?? '',
                'phone'          => $row['phone']          ?? '',
                'alt_phone'      => $row['alt_phone']      ?? '',
                'id_number'      => $row['id_number']      ?? '',
                'email'          => $row['email']          ?? '',
                'move_in_date'   => $row['move_in_date']   ?? '',
                'lease_end_date' => $row['lease_end_date'] ?? '',
                'deposit_paid'   => $row['deposit_paid']   ?? '',
                'deposit_method' => $row['deposit_method'] ?? '',
                'notes'          => $row['notes']          ?? '',
                'has_tenant'     => $hasTenant,
                'status'         => $status,
                'errors'         => $errors,
                'warnings'       => $warnings,
            ];
        }

        fclose($handle);

        if (empty($rows)) {
            return back()->with('error', 'The CSV file has no data rows. Make sure it has at least one row after the header.');
        }

        session([
            'import_rows'        => $rows,
            'import_property_id' => $property->id,
        ]);

        $readyCount   = collect($rows)->where('status', 'ready')->count();
        $warningCount = collect($rows)->where('status', 'warning')->count();
        $skipCount    = collect($rows)->where('status', 'skip')->count();
        $errorCount   = collect($rows)->where('status', 'error')->count();

        return view('properties.import-preview', compact(
            'property', 'rows',
            'readyCount', 'warningCount', 'skipCount', 'errorCount'
        ));
    }

    // ── Save confirmed import ─────────────────────────────────────────────
    public function store(Request $request, Property $property)
    {
        $rows = session('import_rows');

        if (!$rows || session('import_property_id') !== $property->id) {
            return redirect()->route('properties.show', $property)
                ->with('error', 'Import session expired. Please upload the CSV again.');
        }

        $unitsCreated   = 0;
        $tenantsCreated = 0;
        $skipped        = 0;
        $failed         = 0;

        // Re-check existing units in case something was added between preview and confirm
        $existingUnits = Unit::where('property_id', $property->id)
            ->pluck('name')
            ->map(fn($n) => strtolower(trim($n)))
            ->toArray();

        \DB::transaction(function () use (
            $rows, $property, $existingUnits,
            &$unitsCreated, &$tenantsCreated, &$skipped, &$failed
        ) {
            foreach ($rows as $row) {
                if (in_array($row['status'], ['error', 'skip'])) {
                    $skipped++;
                    continue;
                }

                if (in_array(strtolower(trim($row['unit_name'])), $existingUnits)) {
                    $skipped++;
                    continue;
                }

                try {
                    $unit = Unit::create([
                        'property_id'    => $property->id,
                        'name'           => $row['unit_name'],
                        'type'           => $row['unit_type'],
                        'rent_amount'    => floatval($row['rent_amount']),
                        'deposit_amount' => floatval($row['deposit_amount']),
                        'status'         => $row['has_tenant'] ? 'occupied' : 'vacant',
                    ]);
                    $unitsCreated++;

                    if ($row['has_tenant']) {
                        $tenant = Tenant::create([
                            'account_id' => auth()->user()->account_id,
                            'first_name' => $row['first_name'],
                            'last_name'  => $row['last_name'],
                            'phone'      => $row['phone'],
                            'alt_phone'  => $row['alt_phone']  ?: null,
                            'id_number'  => $row['id_number']  ?: null,
                            'email'      => $row['email']      ?: null,
                        ]);

                        $moveInDate = !empty($row['move_in_date'])
                            ? \Carbon\Carbon::parse($row['move_in_date'])->toDateString()
                            : now()->toDateString();

                        $depositPaid   = floatval($row['deposit_paid'] ?? 0);
                        $depositMethod = strtolower($row['deposit_method'] ?? 'cash');
                        if (!in_array($depositMethod, ['mpesa','cash','bank','cheque'])) {
                            $depositMethod = 'cash';
                        }

                        $lease = Lease::create([
                            'unit_id'          => $unit->id,
                            'tenant_id'        => $tenant->id,
                            'move_in_date'     => $moveInDate,
                            'lease_end_date'   => !empty($row['lease_end_date'])
                                ? \Carbon\Carbon::parse($row['lease_end_date'])->toDateString()
                                : null,
                            'monthly_rent'     => floatval($row['rent_amount']),
                            'deposit_required' => floatval($row['deposit_amount']),
                            'deposit_paid'     => $depositPaid,
                            'status'           => 'active',
                            'notes'            => $row['notes'] ?: null,
                        ]);

                        if ($depositPaid > 0) {
                            Payment::create([
                                'account_id'   => auth()->user()->account_id,
                                'tenant_id'    => $tenant->id,
                                'lease_id'     => $lease->id,
                                'amount'       => $depositPaid,
                                'payment_date' => $moveInDate,
                                'method'       => $depositMethod,
                                'reference'    => null,
                                'notes'        => 'Opening deposit — imported from CSV',
                                'is_allocated' => false,
                            ]);
                        }

                        $tenantsCreated++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    \Log::error('CSV import row failed: ' . $e->getMessage(), ['row' => $row]);
                }
            }
        });

        session()->forget(['import_rows', 'import_property_id']);

        AuditService::log(
            'property.data_imported',
            'CSV import into "' . $property->name . '": '
                . $unitsCreated . ' units, '
                . $tenantsCreated . ' tenants imported'
                . ($skipped > 0 ? ', ' . $skipped . ' skipped' : '')
                . ($failed  > 0 ? ', ' . $failed  . ' failed'  : ''),
            $property,
            [
                'units_created'   => $unitsCreated,
                'tenants_created' => $tenantsCreated,
                'skipped'         => $skipped,
                'failed'          => $failed,
            ]
        );

        $msg = $unitsCreated . ' ' . Str::plural('unit', $unitsCreated)
            . ' and ' . $tenantsCreated . ' ' . Str::plural('tenant', $tenantsCreated)
            . ' imported successfully into ' . $property->name . '.';
        if ($skipped > 0) $msg .= ' ' . $skipped . ' skipped.';
        if ($failed  > 0) $msg .= ' ' . $failed  . ' failed — check the system log.';

        return redirect()->route('properties.show', $property)
            ->with('success', $msg);
    }
}