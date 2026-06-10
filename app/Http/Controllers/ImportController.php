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
            ['A1','1 bedroom','15000','30000','John','Doe','0712345678','0722345678','12345678','john@example.com','01/01/2024','31/12/2025','30000','mpesa',''],
            ['A2','2 bedroom','20000','40000','Jane','Smith','0787654321','','','jane@example.com','01/03/2024','','40000','cash','Fixed term'],
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

    // ── Parse date — handles dd/mm/yyyy, d/m/yyyy, yyyy-mm-dd ────────────
    private function parseDate(string $value): ?\Carbon\Carbon
    {
        $value = trim($value);
        if (empty($value)) return null;

        if (preg_match('#^\d{1,2}/\d{1,2}/\d{4}$#', $value)) {
            [$d, $m, $y] = explode('/', $value);
            return \Carbon\Carbon::createFromDate((int)$y, (int)$m, (int)$d);
        }

        try {
            return \Carbon\Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    // ── Normalize any phone format to 07XXXXXXXX ──────────────────────────
    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);

        // Handle Excel scientific notation e.g. 2.54722E+11 → 254722345678
        if (preg_match('/^[\d.]+[eE][+\-]?\d+$/', $phone)) {
            $phone = number_format((float)$phone, 0, '.', '');
        }

        // +254XXXXXXXXX → 07XXXXXXXX
        if (str_starts_with($phone, '+254') && strlen($phone) === 13) {
            return '0' . substr($phone, 4);
        }

        // 254XXXXXXXXX → 07XXXXXXXX
        if (str_starts_with($phone, '254') && strlen($phone) === 12) {
            return '0' . substr($phone, 3);
        }

        // 7XXXXXXXXX (9 digits, Excel stripped leading 0) → 07XXXXXXXX
        if (preg_match('/^7[0-9]{8}$/', $phone)) {
            return '0' . $phone;
        }

        // Already 07XXXXXXXX
        return $phone;
    }

    // ── Validate phone — only 07XXXXXXXX accepted ─────────────────────────
    private function isValidPhone(string $phone): bool
    {
        $normalized = $this->normalizePhone(trim($phone));
        return (bool) preg_match('/^07[0-9]{8}$/', $normalized);
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

        $header = array_map(
            fn($h) => strtolower(trim(str_replace([' ', '-'], '_', $h))),
            $header
        );

        $required = ['unit_name', 'unit_type', 'rent_amount', 'deposit_amount'];
        $missing  = array_diff($required, $header);

        if (!empty($missing)) {
            fclose($handle);
            return back()->with('error',
                'Missing required columns: ' . implode(', ', $missing) . '. Download the sample CSV to see the correct format.'
            );
        }

        $existingUnits = Unit::where('property_id', $property->id)
            ->pluck('name')
            ->map(fn($n) => strtolower(trim($n)))
            ->toArray();

        $rows      = [];
        $rowNumber = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (count(array_filter($data, fn($v) => trim($v) !== '')) === 0) continue;

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

            // ── Tenant validation ────────────────────────────────────────
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
                } elseif (!$this->isValidPhone($row['phone'])) {
                    $errors[] = 'Phone "' . $row['phone'] . '" must be a valid Kenyan number starting with 07 (e.g. 0712345678)';
                }

                if (!empty($row['alt_phone']) && !$this->isValidPhone($row['alt_phone'])) {
                    $warnings[] = 'Alt phone "' . $row['alt_phone'] . '" is not a valid format — it will be skipped';
                    if ($status === 'ready') $status = 'warning';
                }

                if (empty($row['move_in_date'])) {
                    $warnings[] = 'Move-in date missing — today\'s date will be used';
                    if ($status === 'ready') $status = 'warning';
                } else {
                    $parsed = $this->parseDate($row['move_in_date']);
                    if (!$parsed) {
                        $errors[] = 'Move-in date "' . $row['move_in_date'] . '" is not valid. Use dd/mm/yyyy (e.g. 01/03/2024)';
                    }
                }

                if (!empty($row['lease_end_date'])) {
                    $parsed = $this->parseDate($row['lease_end_date']);
                    if (!$parsed) {
                        $errors[] = 'Lease end date "' . $row['lease_end_date'] . '" is not valid. Use dd/mm/yyyy (e.g. 31/12/2025)';
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
                // Normalize phone at parse time so preview matches what gets saved
                'phone'          => !empty($row['phone'])     ? $this->normalizePhone($row['phone'])     : '',
                'alt_phone'      => !empty($row['alt_phone']) ? $this->normalizePhone($row['alt_phone']) : '',
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

        $newUnitsCount = collect($rows)->filter(
            fn($r) => !in_array($r['status'], ['error', 'skip'])
        )->count();

        $account      = auth()->user()->account;
        $currentCount = Unit::whereIn('property_id',
            Property::where('account_id', $account->id)->pluck('id')
        )->count();

        if (($currentCount + $newUnitsCount) > $account->unit_limit) {
            $available = max(0, $account->unit_limit - $currentCount);
            session()->forget(['import_rows', 'import_property_id']);
            return redirect()->route('properties.show', $property)
                ->with('error',
                    'This import would create ' . $newUnitsCount . ' units but you only have '
                    . $available . ' unit slot(s) remaining on your plan. '
                    . 'Upgrade your plan or reduce the number of units in your CSV. '
                    . 'Contact us on WhatsApp: +254705056343'
                );
        }

        $unitsCreated   = 0;
        $tenantsCreated = 0;
        $skipped        = 0;
        $failed         = 0;

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
                        // Phones already normalized at preview time — stored in session
                        $altPhone = !empty($row['alt_phone']) ? $row['alt_phone'] : null;

                        $tenant = Tenant::create([
                            'account_id' => auth()->user()->account_id,
                            'first_name' => $row['first_name'],
                            'last_name'  => $row['last_name'],
                            'phone'      => $row['phone'],
                            'alt_phone'  => $altPhone,
                            'id_number'  => $row['id_number'] ?: null,
                            'email'      => $row['email']     ?: null,
                        ]);

                        $moveInDate = !empty($row['move_in_date'])
                            ? $this->parseDate($row['move_in_date'])->toDateString()
                            : now()->toDateString();

                        $leaseEndDate = !empty($row['lease_end_date'])
                            ? $this->parseDate($row['lease_end_date'])?->toDateString()
                            : null;

                        $depositPaid   = floatval($row['deposit_paid'] ?? 0);
                        $depositMethod = strtolower($row['deposit_method'] ?? 'cash');

                        if (!in_array($depositMethod, ['mpesa','cash','bank','cheque'])) {
                            $depositMethod = 'cash';
                        }

                        $lease = Lease::create([
                            'unit_id'          => $unit->id,
                            'tenant_id'        => $tenant->id,
                            'move_in_date'     => $moveInDate,
                            'lease_end_date'   => $leaseEndDate,
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
                                'payment_type' => 'deposit',
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
        if ($failed  > 0) $msg .= ' ' . $failed  . ' failed.';

        return redirect()->route('properties.show', $property)
            ->with('success', $msg);
    }
}