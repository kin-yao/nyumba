<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function rentRoll(Request $request)
    {
        $month       = (int) $request->input('month', now()->month);
        $year        = (int) $request->input('year', now()->year);
        $propertyIds = $this->filteredPropertyIds();

        // Eager-load invoices filtered to the selected period only
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
                    // Use actual invoice total (rent + utilities) as expected
                    $totalExpected  += floatval($invoice->total_amount);
                    $totalCollected += floatval($invoice->amount_paid);
                } else {
                    // No invoice issued yet this period — use monthly rent as expected
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
                $totalPaid    = floatval($lease->payments->sum('amount'));
                $balance      = $totalCharged - $totalPaid;
                $lastPayment  = $lease->payments->sortByDesc('payment_date')->first();

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
        $leaseIds = $this->filteredLeaseIds(); // uses base Controller method

        $payments = Payment::with(['tenant', 'lease.unit.property'])
            ->whereIn('lease_id', $leaseIds)
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
        $leaseIds    = $this->filteredLeaseIds(); // uses base Controller method

        $totalIncome = floatval(
            Payment::whereIn('lease_id', $leaseIds)
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

        // $payments variable holds the income total for view compatibility
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
                            ]);
                        }
                    }

                    foreach ($activeLease->payments as $payment) {
                        $ledger->push([
                            'date'        => $payment->payment_date,
                            'description' => 'Payment received',
                            'reference'   => $payment->reference ?? strtoupper($payment->method),
                            'charged'     => null,
                            'paid'        => floatval($payment->amount),
                        ]);
                    }

                    $ledger = $ledger->sortBy('date')->values();

                    $balance = floatval($activeLease->invoices->sum('total_amount'))
                             - floatval($activeLease->payments->sum('amount'));
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
}