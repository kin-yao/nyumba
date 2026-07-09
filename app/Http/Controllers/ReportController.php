<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\UtilityReading;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $propertyIds = $this->filteredPropertyIds();
        $properties  = Property::whereIn('id', $propertyIds)->orderBy('name')->get();

        return view('reports.index', compact('properties'));
    }

    public function utilities(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        $propertyIds = $this->filteredPropertyIds();

        $properties = Property::whereIn('id', $propertyIds)
            ->with([
                'units.activeLease.tenant',
                'utilityRates' => fn($q) => $q->where('active', true)
                    ->whereIn('billing_type', ['per_unit', 'per_meter_reading']),
            ])->get();

        $rows         = [];
        $totalsByType = [];
        $grandTotal   = 0;

        foreach ($properties as $property) {
            if ($property->utilityRates->isEmpty()) continue;

            $unitIds  = $property->units->pluck('id')->toArray();
            $readings = UtilityReading::whereIn('unit_id', $unitIds)
                ->where('reading_month', $month)
                ->where('reading_year', $year)
                ->get()
                ->groupBy(fn($r) => $r->unit_id . '_' . $r->utility_type);

            foreach ($property->units as $unit) {
                if (!$unit->activeLease) continue;

                foreach ($property->utilityRates as $rate) {
                    $reading = $readings->get($unit->id . '_' . $rate->type)?->first();
                    $charge  = $reading ? floatval($reading->charge_amount) : 0;

                    $rows[] = [
                        'property'     => $property->name,
                        'unit'         => $unit->name,
                        'tenant'       => $unit->activeLease->tenant->full_name,
                        'utility_name' => $rate->name,
                        'utility_type' => $rate->type,
                        'previous'     => $reading ? floatval($reading->previous_reading) : null,
                        'current'      => $reading ? floatval($reading->current_reading) : null,
                        'consumed'     => $reading ? floatval($reading->units_consumed) : null,
                        'charge'       => $charge,
                        'has_reading'  => (bool) $reading,
                    ];

                    $totalsByType[$rate->name] = ($totalsByType[$rate->name] ?? 0) + $charge;
                    $grandTotal += $charge;
                }
            }
        }

        $missingCount = collect($rows)->where('has_reading', false)->count();

        return view('reports.utilities', compact(
            'rows', 'totalsByType', 'grandTotal', 'missingCount', 'month', 'year'
        ));
    }

    /**
     * Generate one combined PDF report for a single property.
     * With a month: full detailed report for that month.
     * Without a month: a high-level yearly summary (no month-by-month breakdown).
     */
    public function download(Request $request)
    {
        $validated = $request->validate([
            'property_id' => ['required', 'integer'],
            'year'        => ['required', 'integer', 'min:2000', 'max:2100'],
            'month'       => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $property = Property::where('id', $validated['property_id'])
            ->whereIn('id', $this->filteredPropertyIds())
            ->firstOrFail();

        $account = auth()->user()->account;
        $year    = (int) $validated['year'];
        $month   = $validated['month'] ?? null;
        $month   = $month ? (int) $month : null;

        $unitIds  = Unit::where('property_id', $property->id)->pluck('id')->toArray();
        $leaseIds = Lease::whereIn('unit_id', $unitIds)->pluck('id')->toArray();

        $data = [
            'account'     => $account,
            'property'    => $property,
            'occupancy'   => $this->occupancySnapshot($property),
            'deposits'    => $this->depositsSnapshot($unitIds),
            'outstanding' => $this->outstandingSnapshot($unitIds),
        ];

        if ($month) {
            $data['mode']           = 'monthly';
            $data['periodLabel']    = \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y');
            $data['rentRoll']       = $this->rentRollForPeriod($property, $month, $year);
            $data['collections']    = $this->collectionsForPeriod($leaseIds, $month, $year);
            $data['incomeExpenses'] = $this->incomeExpensesForPeriod($property, $leaseIds, $month, $year);
            $data['utilities']      = $this->utilitiesForPeriod($property, $unitIds, $month, $year);

            $filename = 'Report-' . \Illuminate\Support\Str::slug($property->name) . '-' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.pdf';
        } else {
            $data['mode']        = 'yearly';
            $data['periodLabel'] = (string) $year;
            $data['yearly']      = $this->yearlySummary($property, $leaseIds, $unitIds, $year);

            $filename = 'Report-' . \Illuminate\Support\Str::slug($property->name) . '-' . $year . '.pdf';
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf.general', $data);

        return $pdf->download($filename);
    }

    // ─── Combined-PDF helpers ───────────────────────────────────────────────

    private function occupancySnapshot(Property $property): array
    {
        $units    = Unit::where('property_id', $property->id)->get();
        $total    = $units->count();
        $occupied = $units->where('status', 'occupied')->count();
        $vacant   = $total - $occupied;
        $rate     = $total > 0 ? round(($occupied / $total) * 100) : 0;

        return compact('total', 'occupied', 'vacant', 'rate');
    }

    private function depositsSnapshot(array $unitIds): array
    {
        $leases = Lease::where('status', 'active')
            ->whereIn('unit_id', $unitIds)
            ->get();

        $totalRequired    = floatval($leases->sum(fn($l) => floatval($l->deposit_required ?? 0)));
        $totalHeld        = floatval($leases->sum(fn($l) => floatval($l->deposit_paid ?? 0)));
        $totalOutstanding = floatval($leases->sum(
            fn($l) => max(0, floatval($l->deposit_required ?? 0) - floatval($l->deposit_paid ?? 0))
        ));

        return compact('totalRequired', 'totalHeld', 'totalOutstanding');
    }

    private function outstandingSnapshot(array $unitIds): array
    {
        $leases = Lease::with(['tenant', 'invoices', 'payments'])
            ->where('status', 'active')
            ->whereIn('unit_id', $unitIds)
            ->get()
            ->map(function ($lease) {
                $totalCharged = floatval($lease->invoices->sum('total_amount'));
                $totalPaid    = floatval(
                    $lease->payments->where('payment_type', '!=', 'deposit')->sum('amount')
                );

                return [
                    'tenant'  => $lease->tenant,
                    'balance' => $totalCharged - $totalPaid,
                ];
            })
            ->filter(fn($l) => $l['balance'] > 0)
            ->sortByDesc('balance')
            ->values();

        return [
            'leases' => $leases,
            'total'  => $leases->sum('balance'),
        ];
    }

    private function rentRollForPeriod(Property $property, int $month, int $year): array
    {
        $property->load([
            'units.activeLease.tenant',
            'units.activeLease.invoices' => fn($q) =>
                $q->where('period_month', $month)->where('period_year', $year),
        ]);

        $rows           = [];
        $totalExpected  = 0;
        $totalCollected = 0;

        foreach ($property->units as $unit) {
            if (!$unit->activeLease) continue;

            $invoice   = $unit->activeLease->invoices->first();
            $expected  = $invoice ? floatval($invoice->total_amount) : floatval($unit->activeLease->monthly_rent);
            $collected = $invoice ? floatval($invoice->amount_paid) : 0;

            $totalExpected  += $expected;
            $totalCollected += $collected;

            $rows[] = [
                'unit'      => $unit,
                'tenant'    => $unit->activeLease->tenant,
                'expected'  => $expected,
                'collected' => $collected,
            ];
        }

        $totalOutstanding = $totalExpected - $totalCollected;
        $collectionRate   = $totalExpected > 0 ? round(($totalCollected / $totalExpected) * 100, 1) : 0;

        return compact('rows', 'totalExpected', 'totalCollected', 'totalOutstanding', 'collectionRate');
    }

    private function collectionsForPeriod(array $leaseIds, int $month, int $year): array
    {
        $payments = Payment::with('tenant')
            ->whereIn('lease_id', $leaseIds)
            ->where('payment_type', '!=', 'deposit')
            ->whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->get();

        $totalCollected = floatval($payments->sum('amount'));

        $byMethod = $payments->groupBy('method')
            ->map(fn($g) => [
                'count'  => $g->count(),
                'amount' => floatval($g->sum('amount')),
            ]);

        return compact('payments', 'totalCollected', 'byMethod');
    }

    private function incomeExpensesForPeriod(Property $property, array $leaseIds, int $month, int $year): array
    {
        $totalIncome = floatval(
            Payment::whereIn('lease_id', $leaseIds)
                ->where('payment_type', '!=', 'deposit')
                ->whereMonth('payment_date', $month)
                ->whereYear('payment_date', $year)
                ->sum('amount')
        );

        $expenses = Expense::where('property_id', $property->id)
            ->whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->get();

        $totalExpenses = floatval($expenses->sum('amount'));
        $netProfit     = $totalIncome - $totalExpenses;

        $byCategory = $expenses->groupBy('category')
            ->map(fn($g) => floatval($g->sum('amount')))
            ->sortByDesc(fn($v) => $v);

        return compact('totalIncome', 'totalExpenses', 'netProfit', 'byCategory');
    }

    private function utilitiesForPeriod(Property $property, array $unitIds, int $month, int $year): array
    {
        $meterRates = $property->utilityRates()->where('active', true)
            ->whereIn('billing_type', ['per_unit', 'per_meter_reading'])
            ->get();

        $readings = UtilityReading::whereIn('unit_id', $unitIds)
            ->where('reading_month', $month)
            ->where('reading_year', $year)
            ->get()
            ->groupBy(fn($r) => $r->unit_id . '_' . $r->utility_type);

        $units = Unit::where('property_id', $property->id)
            ->with('activeLease.tenant')
            ->get();

        $rows       = [];
        $totalCharge = 0;
        $missing    = 0;

        foreach ($units as $unit) {
            if (!$unit->activeLease) continue;

            foreach ($meterRates as $rate) {
                $reading = $readings->get($unit->id . '_' . $rate->type)?->first();
                $charge  = $reading ? floatval($reading->charge_amount) : 0;

                if (!$reading) {
                    $missing++;
                    continue;
                }

                $rows[] = [
                    'unit'         => $unit->name,
                    'tenant'       => $unit->activeLease->tenant->full_name,
                    'utility_name' => $rate->name,
                    'consumed'     => floatval($reading->units_consumed),
                    'charge'       => $charge,
                ];

                $totalCharge += $charge;
            }
        }

        return compact('rows', 'totalCharge', 'missing');
    }

    private function yearlySummary(Property $property, array $leaseIds, array $unitIds, int $year): array
    {
        $months = [];

        for ($m = 1; $m <= 12; $m++) {
            $invoices = Invoice::whereIn('lease_id', $leaseIds)
                ->where('period_month', $m)
                ->where('period_year', $year)
                ->get();

            $expected  = floatval($invoices->sum('total_amount'));
            $collected = floatval($invoices->sum('amount_paid'));
            $rate      = $expected > 0 ? round(($collected / $expected) * 100) : 0;

            $income = floatval(
                Payment::whereIn('lease_id', $leaseIds)
                    ->where('payment_type', '!=', 'deposit')
                    ->whereMonth('payment_date', $m)
                    ->whereYear('payment_date', $year)
                    ->sum('amount')
            );

            $expenses = floatval(
                Expense::where('property_id', $property->id)
                    ->whereMonth('expense_date', $m)
                    ->whereYear('expense_date', $year)
                    ->sum('amount')
            );

            $utilityCharge = floatval(
                UtilityReading::whereIn('unit_id', $unitIds)
                    ->where('reading_month', $m)
                    ->where('reading_year', $year)
                    ->sum('charge_amount')
            );

            $months[] = [
                'label'     => \Carbon\Carbon::createFromDate($year, $m, 1)->format('M'),
                'expected'  => $expected,
                'collected' => $collected,
                'rate'      => $rate,
                'income'    => $income,
                'expenses'  => $expenses,
                'net'       => $income - $expenses,
                'utilities' => $utilityCharge,
            ];
        }

        $totals = [
            'expected'  => array_sum(array_column($months, 'expected')),
            'collected' => array_sum(array_column($months, 'collected')),
            'income'    => array_sum(array_column($months, 'income')),
            'expenses'  => array_sum(array_column($months, 'expenses')),
            'net'       => array_sum(array_column($months, 'net')),
            'utilities' => array_sum(array_column($months, 'utilities')),
        ];
        $totals['rate'] = $totals['expected'] > 0
            ? round(($totals['collected'] / $totals['expected']) * 100)
            : 0;

        return compact('months', 'totals');
    }

    public function rentRoll(Request $request)
    {
        $month       = (int) $request->input('month', now()->month);
        $year        = (int) $request->input('year', now()->year);
        $propertyIds = $this->filteredPropertyIds();

        $properties = Property::whereIn('id', $propertyIds)
            ->with([
                'units.activeLease.tenant',
                'units.activeLease.invoices' => fn($q) =>
                    $q->where('period_month', $month)
                      ->where('period_year', $year),
            ])->get();

        $totalExpected  = 0;
        $totalCollected = 0;

        foreach ($properties as $property) {
            foreach ($property->units as $unit) {
                if (!$unit->activeLease) continue;

                $invoice = $unit->activeLease->invoices->first();

                if ($invoice) {
                    $totalExpected  += floatval($invoice->total_amount);
                    $totalCollected += floatval($invoice->amount_paid);
                } else {
                    $totalExpected += floatval($unit->activeLease->monthly_rent);
                }
            }
        }

        $totalOutstanding = $totalExpected - $totalCollected;
        $collectionRate   = $totalExpected > 0
            ? round(($totalCollected / $totalExpected) * 100, 1)
            : 0;

        return view('reports.rent-roll', compact(
            'properties', 'month', 'year',
            'totalExpected', 'totalCollected',
            'totalOutstanding', 'collectionRate'
        ));
    }

    public function outstanding()
    {
        $unitIds = $this->filteredUnitIds();

        $leases = Lease::with(['tenant', 'unit.property', 'invoices', 'payments'])
            ->where('status', 'active')
            ->whereIn('unit_id', $unitIds)
            ->get()
            ->map(function ($lease) {
                $totalCharged = floatval($lease->invoices->sum('total_amount'));
                // Exclude deposits from balance calculation
                $totalPaid    = floatval(
                    $lease->payments->where('payment_type', '!=', 'deposit')->sum('amount')
                );
                $balance     = $totalCharged - $totalPaid;
                $lastPayment = $lease->payments
                    ->where('payment_type', '!=', 'deposit')
                    ->sortByDesc('payment_date')
                    ->first();

                return [
                    'tenant'       => $lease->tenant,
                    'unit'         => $lease->unit,
                    'property'     => $lease->unit->property,
                    'balance'      => $balance,
                    'last_payment' => $lastPayment,
                    'days_since'   => $lastPayment
                        ? $lastPayment->payment_date->diffInDays(now())
                        : null,
                ];
            })
            ->filter(fn($l) => $l['balance'] > 0)
            ->sortByDesc('balance')
            ->values();

        $totalOutstanding = $leases->sum('balance');

        return view('reports.outstanding', compact('leases', 'totalOutstanding'));
    }

    public function collections(Request $request)
    {
        $month    = (int) $request->input('month', now()->month);
        $year     = (int) $request->input('year', now()->year);
        $leaseIds = $this->filteredLeaseIds();

        // Exclude deposits — collections report is rent income only
        $payments = Payment::with(['tenant', 'lease.unit.property'])
            ->whereIn('lease_id', $leaseIds)
            ->where('payment_type', '!=', 'deposit')
            ->whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->get();

        $totalCollected = floatval($payments->sum('amount'));

        $byMethod = $payments->groupBy('method')
            ->map(fn($g) => [
                'count'  => $g->count(),
                'amount' => floatval($g->sum('amount')),
            ]);

        return view('reports.collections', compact(
            'payments', 'totalCollected', 'byMethod', 'month', 'year'
        ));
    }

    public function incomeExpenses(Request $request)
    {
        $month       = (int) $request->input('month', now()->month);
        $year        = (int) $request->input('year', now()->year);
        $propertyIds = $this->filteredPropertyIds();
        $leaseIds    = $this->filteredLeaseIds();

        // Exclude deposits — income is rent/utilities only
        $totalIncome = floatval(
            Payment::whereIn('lease_id', $leaseIds)
                ->where('payment_type', '!=', 'deposit')
                ->whereMonth('payment_date', $month)
                ->whereYear('payment_date', $year)
                ->sum('amount')
        );

        $expenses = Expense::whereIn('property_id', $propertyIds)
            ->whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->get();

        $totalExpenses = floatval($expenses->sum('amount'));
        $netProfit     = $totalIncome - $totalExpenses;

        $expensesByCategory = $expenses->groupBy('category')
            ->map(fn($g) => floatval($g->sum('amount')))
            ->sortByDesc(fn($v) => $v);

        $payments = $totalIncome;

        return view('reports.income-expenses', compact(
            'payments', 'totalExpenses', 'netProfit',
            'expensesByCategory', 'month', 'year'
        ));
    }

    public function tenantStatement(Request $request)
    {
        $tenantId = $request->input('tenant_id');
        $unitIds  = $this->filteredUnitIds();

        $tenants = Tenant::with('activeLease')
            ->whereHas('activeLease', fn($q) => $q->whereIn('unit_id', $unitIds))
            ->get();

        $tenant  = null;
        $ledger  = collect();
        $balance = 0;

        if ($tenantId) {
            $tenant = Tenant::with([
                'leases.invoices.lineItems',
                'leases.payments',
                'leases.unit.property',
            ])->find($tenantId);

            if ($tenant) {
                $activeLease = $tenant->leases->where('status', 'active')->first();

                if ($activeLease) {
                    foreach ($activeLease->invoices as $invoice) {
                        foreach ($invoice->lineItems as $item) {
                            $ledger->push([
                                'date'        => $invoice->invoice_date,
                                'description' => $item->description,
                                'reference'   => $invoice->reference,
                                'charged'     => floatval($item->amount),
                                'paid'        => null,
                                'type'        => 'charge',
                            ]);
                        }
                    }

                    foreach ($activeLease->payments as $payment) {
                        $ledger->push([
                            'date'        => $payment->payment_date,
                            'description' => $payment->payment_type === 'deposit'
                                ? 'Deposit received'
                                : 'Payment received',
                            'reference'   => $payment->reference ?? strtoupper($payment->method),
                            'charged'     => null,
                            'paid'        => floatval($payment->amount),
                            'type'        => $payment->payment_type,
                        ]);
                    }

                    $ledger = $ledger->sortBy('date')->values();

                    // Balance only counts rent payments, not deposits
                    $rentPaid = floatval(
                        $activeLease->payments
                            ->where('payment_type', '!=', 'deposit')
                            ->sum('amount')
                    );
                    $balance = floatval($activeLease->invoices->sum('total_amount')) - $rentPaid;
                }
            }
        }

        return view('reports.tenant-statement', compact(
            'tenants', 'tenant', 'ledger', 'balance'
        ));
    }

    public function occupancy()
    {
        $propertyIds = $this->filteredPropertyIds();

        $properties = Property::whereIn('id', $propertyIds)
            ->with(['units.activeLease'])
            ->get();

        $summary = $properties->map(function ($property) {
            $total    = $property->units->count();
            $occupied = $property->units->where('status', 'occupied')->count();
            $vacant   = $property->units->where('status', 'vacant')->count();
            $rate     = $total > 0 ? round(($occupied / $total) * 100) : 0;

            return [
                'property' => $property,
                'total'    => $total,
                'occupied' => $occupied,
                'vacant'   => $vacant,
                'rate'     => $rate,
            ];
        });

        $totalUnits    = $summary->sum('total');
        $totalOccupied = $summary->sum('occupied');
        $totalVacant   = $summary->sum('vacant');
        $overallRate   = $totalUnits > 0
            ? round(($totalOccupied / $totalUnits) * 100)
            : 0;

        return view('reports.occupancy', compact(
            'summary', 'totalUnits', 'totalOccupied',
            'totalVacant', 'overallRate'
        ));
    }

    public function deposits(Request $request)
    {
        $propertyId  = $request->input('property_id');
        $propertyIds = $this->filteredPropertyIds();
        $unitIds     = $this->filteredUnitIds();

        $properties = Property::whereIn('id', $propertyIds)->get();

        // All deposit payments for this account's leases
        $leaseIds = Lease::whereIn('unit_id', $unitIds)->pluck('id');

        $depositPayments = Payment::with(['tenant', 'lease.unit.property'])
            ->whereIn('lease_id', $leaseIds)
            ->where('payment_type', 'deposit')
            ->when($propertyId, fn($q) =>
                $q->whereHas('lease.unit', fn($q2) =>
                    $q2->where('property_id', $propertyId)
                )
            )
            ->latest('payment_date')
            ->get();

        // Leases with deposit info from the lease record
        $leases = Lease::with(['tenant', 'unit.property'])
            ->whereIn('unit_id', $unitIds)
            ->where('status', 'active')
            ->when($propertyId, fn($q) =>
                $q->whereHas('unit', fn($q2) =>
                    $q2->where('property_id', $propertyId)
                )
            )
            ->get()
            ->map(function ($lease) use ($leaseIds) {
                $paidViaPayments = floatval(
                    Payment::where('lease_id', $lease->id)
                        ->where('payment_type', 'deposit')
                        ->sum('amount')
                );

                return [
                    'lease'            => $lease,
                    'tenant'           => $lease->tenant,
                    'unit'             => $lease->unit,
                    'property'         => $lease->unit->property,
                    'required'         => floatval($lease->deposit_required ?? 0),
                    'paid_on_lease'    => floatval($lease->deposit_paid ?? 0),
                    'paid_via_payments'=> $paidViaPayments,
                    'outstanding'      => max(0, floatval($lease->deposit_required ?? 0) - floatval($lease->deposit_paid ?? 0)),
                    'status'           => floatval($lease->deposit_paid ?? 0) <= 0
                        ? 'unpaid'
                        : (floatval($lease->deposit_paid ?? 0) >= floatval($lease->deposit_required ?? 0)
                            ? 'paid'
                            : 'partial'),
                ];
            });

        $totalRequired   = $leases->sum('required');
        $totalHeld       = $leases->sum('paid_on_lease');
        $totalOutstanding = $leases->sum('outstanding');

        return view('reports.deposits', compact(
            'leases', 'depositPayments', 'properties',
            'totalRequired', 'totalHeld', 'totalOutstanding',
            'propertyId'
        ));
    }
}