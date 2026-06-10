<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;

class DashboardController extends Controller
{
    public function index()
    {
        $month = now()->month;
        $year  = now()->year;

        $propertyIds     = $this->filteredPropertyIds();
        $filterProperty  = $this->filteredProperty();
        $totalProperties = count($propertyIds);

        // ── Unit stats ────────────────────────────────────────────────────
        $unitData = Unit::whereIn('property_id', $propertyIds)
            ->selectRaw('COUNT(*) as total, SUM(status = "occupied") as occupied')
            ->first();

        $totalUnits    = (int) ($unitData->total    ?? 0);
        $occupiedUnits = (int) ($unitData->occupied ?? 0);
        $vacantUnits   = $totalUnits - $occupiedUnits;
        $occupancyRate = $totalUnits > 0
            ? round(($occupiedUnits / $totalUnits) * 100) : 0;

        // ── IDs ───────────────────────────────────────────────────────────
        $unitIds = Unit::whereIn('property_id', $propertyIds)->pluck('id');

        $leaseIds = Lease::whereIn('unit_id', $unitIds)
            ->where('status', 'active')
            ->pluck('id');

        // ── Tenant count ──────────────────────────────────────────────────
        $totalTenants = Lease::whereIn('id', $leaseIds)
            ->distinct()
            ->count('tenant_id');

        // ── Invoice stats this month ──────────────────────────────────────
        $invoiceStats = Invoice::whereIn('lease_id', $leaseIds)
            ->whereMonth('invoice_date', $month)
            ->whereYear('invoice_date', $year)
            ->selectRaw('
                COALESCE(SUM(total_amount), 0) as expected,
                COALESCE(SUM(amount_paid),  0) as collected
            ')
            ->first();

        $expectedThisMonth    = floatval($invoiceStats->expected  ?? 0);
        $collectedThisMonth   = floatval($invoiceStats->collected ?? 0);
        $outstandingThisMonth = $expectedThisMonth - $collectedThisMonth;
        $collectionRate       = $expectedThisMonth > 0
            ? round(($collectedThisMonth / $expectedThisMonth) * 100) : 0;

        // ── Total outstanding ─────────────────────────────────────────────
        $totalInvoiced = floatval(Invoice::whereIn('lease_id', $leaseIds)->sum('total_amount'));
        $totalPaid     = floatval(
            Payment::whereIn('lease_id', $leaseIds)
                ->where('payment_type', '!=', 'deposit')
                ->sum('amount')
        );
        $totalOutstanding = $totalInvoiced - $totalPaid;

        // ── This month income & expenses — deposits excluded ──────────────
        $paymentsThisMonth = floatval(
            Payment::whereIn('lease_id', $leaseIds)
                ->where('payment_type', '!=', 'deposit')
                ->whereMonth('payment_date', $month)
                ->whereYear('payment_date', $year)
                ->sum('amount')
        );

        $expensesThisMonth = floatval(
            Expense::whereIn('property_id', $propertyIds)
                ->whereMonth('expense_date', $month)
                ->whereYear('expense_date', $year)
                ->sum('amount')
        );

        $netProfitThisMonth = $paymentsThisMonth - $expensesThisMonth;

        // ── Maintenance ───────────────────────────────────────────────────
        $maintenanceStats = MaintenanceRequest::whereIn('unit_id', $unitIds)
            ->where('status', 'open')
            ->selectRaw('COUNT(*) as open_count, SUM(priority = "urgent") as urgent_count')
            ->first();

        $openMaintenance   = (int) ($maintenanceStats->open_count   ?? 0);
        $urgentMaintenance = (int) ($maintenanceStats->urgent_count ?? 0);

        // ── Overdue invoices ──────────────────────────────────────────────
        $overdueCount = Invoice::whereIn('lease_id', $leaseIds)
            ->where(function ($q) {
                $q->where('status', 'overdue')
                  ->orWhere(function ($q2) {
                      $q2->where('status', 'sent')
                         ->where('due_date', '<', now());
                  });
            })->count();

        // ── Recent payments ───────────────────────────────────────────────
        $recentPayments = Payment::with(['tenant', 'lease.unit.property'])
            ->whereIn('lease_id', $leaseIds)
            ->latest('payment_date')
            ->take(5)
            ->get();

        // ── Tenants with highest balances — deposits excluded ─────────────
        $tenantsWithBalance = Lease::with(['tenant', 'unit.property'])
            ->whereIn('id', $leaseIds)
            ->withSum('invoices', 'total_amount')
            ->get()
            ->map(function ($lease) {
                $paid = floatval(
                    Payment::where('lease_id', $lease->id)
                        ->where('payment_type', '!=', 'deposit')
                        ->sum('amount')
                );
                return [
                    'tenant'   => $lease->tenant,
                    'unit'     => $lease->unit,
                    'property' => $lease->unit->property,
                    'balance'  => floatval($lease->invoices_sum_total_amount ?? 0) - $paid,
                ];
            })
            ->filter(fn($l) => $l['balance'] > 0)
            ->sortByDesc('balance')
            ->take(5)
            ->values();

        // ── Properties overview ───────────────────────────────────────────
        $propertiesOverview = Property::whereIn('id', $propertyIds)
            ->withCount('units')
            ->withCount(['units as occupied_count' => fn($q) => $q->where('status', 'occupied')])
            ->get();

        // ── 6-month chart — deposits excluded ─────────────────────────────
        $sixMonthsAgo = now()->subMonths(5)->startOfMonth();
        $endOfMonth   = now()->endOfMonth();

        $incomeByMonth = Payment::whereIn('lease_id', $leaseIds)
            ->where('payment_type', '!=', 'deposit')
            ->whereBetween('payment_date', [$sixMonthsAgo, $endOfMonth])
            ->selectRaw('MONTH(payment_date) as m, YEAR(payment_date) as y, SUM(amount) as total')
            ->groupBy('y', 'm')
            ->get()
            ->keyBy(fn($r) => $r->y . '-' . str_pad($r->m, 2, '0', STR_PAD_LEFT));

        $expensesByMonth = Expense::whereIn('property_id', $propertyIds)
            ->whereBetween('expense_date', [$sixMonthsAgo, $endOfMonth])
            ->selectRaw('MONTH(expense_date) as m, YEAR(expense_date) as y, SUM(amount) as total')
            ->groupBy('y', 'm')
            ->get()
            ->keyBy(fn($r) => $r->y . '-' . str_pad($r->m, 2, '0', STR_PAD_LEFT));

        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date     = now()->subMonths($i);
            $key      = $date->format('Y-m');
            $income   = floatval($incomeByMonth->get($key)?->total  ?? 0);
            $expenses = floatval($expensesByMonth->get($key)?->total ?? 0);

            $chartData[] = [
                'label'    => $date->format('M Y'),
                'income'   => $income,
                'expenses' => $expenses,
                'profit'   => $income - $expenses,
            ];
        }

        return view('dashboard', compact(
            'totalProperties', 'totalUnits', 'occupiedUnits',
            'vacantUnits', 'occupancyRate', 'totalTenants',
            'expectedThisMonth', 'collectedThisMonth',
            'outstandingThisMonth', 'collectionRate',
            'totalOutstanding', 'paymentsThisMonth',
            'expensesThisMonth', 'netProfitThisMonth',
            'openMaintenance', 'urgentMaintenance',
            'overdueCount', 'recentPayments',
            'tenantsWithBalance', 'propertiesOverview',
            'month', 'year', 'chartData'
        ));
    }

    public function notifications()
    {
        $notifications = \App\Models\Notification::where('account_id', auth()->user()->account_id)
            ->latest()
            ->take(50)
            ->get();

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(\App\Models\Notification $notification)
    {
        if ($notification->account_id === auth()->user()->account_id) {
            $notification->markAsRead();
            cache()->forget('notif_count_' . auth()->user()->account_id);
        }
        return back();
    }

    public function markAllRead()
    {
        \App\Models\Notification::where('account_id', auth()->user()->account_id)
            ->unread()
            ->update(['read_at' => now()]);

        cache()->forget('notif_count_' . auth()->user()->account_id);

        return back()->with('success', 'All notifications marked as read.');
    }
}