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

        $propertyIds    = $this->filteredPropertyIds();
        $filterProperty = $this->filteredProperty();
        $totalProperties = count($propertyIds);

        // ── Unit stats (1 query) ──────────────────────────────────────────
        $unitData = Unit::whereIn('property_id', $propertyIds)
            ->selectRaw('COUNT(*) as total, SUM(status = "occupied") as occupied')
            ->first();

        $totalUnits    = (int) ($unitData->total    ?? 0);
        $occupiedUnits = (int) ($unitData->occupied ?? 0);
        $vacantUnits   = $totalUnits - $occupiedUnits;
        $occupancyRate = $totalUnits > 0
            ? round(($occupiedUnits / $totalUnits) * 100) : 0;

        // ── IDs needed for scoping (2 queries) ───────────────────────────
        $unitIds = Unit::whereIn('property_id', $propertyIds)->pluck('id');

        $leaseIds = Lease::whereIn('unit_id', $unitIds)
            ->where('status', 'active')
            ->pluck('id');

        // ── Tenant count (1 query) ────────────────────────────────────────
        $totalTenants = Lease::whereIn('id', $leaseIds)
            ->distinct()
            ->count('tenant_id');

        // ── This month invoice stats — SQL aggregates, no record loading (1 query) ──
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

        // ── Total outstanding — 2 aggregate queries ───────────────────────
        $totalInvoiced    = floatval(Invoice::whereIn('lease_id', $leaseIds)->sum('total_amount'));
        $totalPaid        = floatval(Payment::whereIn('lease_id', $leaseIds)->sum('amount'));
        $totalOutstanding = $totalInvoiced - $totalPaid;

        // ── This month income & expenses (2 queries) ─────────────────────
        $paymentsThisMonth = floatval(
            Payment::whereIn('lease_id', $leaseIds)
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

        // ── Maintenance counts (1 query) ──────────────────────────────────
        $maintenanceStats = MaintenanceRequest::whereIn('unit_id', $unitIds)
            ->where('status', 'open')
            ->selectRaw('COUNT(*) as open_count, SUM(priority = "urgent") as urgent_count')
            ->first();

        $openMaintenance   = (int) ($maintenanceStats->open_count   ?? 0);
        $urgentMaintenance = (int) ($maintenanceStats->urgent_count ?? 0);

        // ── Overdue invoices (1 query) ────────────────────────────────────
        $overdueCount = Invoice::whereIn('lease_id', $leaseIds)
            ->where(function ($q) {
                $q->where('status', 'overdue')
                  ->orWhere(function ($q2) {
                      $q2->where('status', 'sent')
                         ->where('due_date', '<', now());
                  });
            })->count();

        // ── Recent payments (1 query + eager loads) ───────────────────────
        $recentPayments = Payment::with(['tenant', 'lease.unit.property'])
            ->whereIn('lease_id', $leaseIds)
            ->latest('payment_date')
            ->take(5)
            ->get();

        // ── Tenants with highest balances — withSum avoids loading records (2 queries) ──
        $tenantsWithBalance = Lease::with(['tenant', 'unit.property'])
            ->whereIn('id', $leaseIds)
            ->withSum('invoices', 'total_amount')
            ->withSum('payments',  'amount')
            ->get()
            ->map(fn($lease) => [
                'tenant'   => $lease->tenant,
                'unit'     => $lease->unit,
                'property' => $lease->unit->property,
                'balance'  => floatval($lease->invoices_sum_total_amount ?? 0)
                            - floatval($lease->payments_sum_amount        ?? 0),
            ])
            ->filter(fn($l) => $l['balance'] > 0)
            ->sortByDesc('balance')
            ->take(5)
            ->values();

        // ── Properties overview (1 query) ─────────────────────────────────
        $propertiesOverview = Property::whereIn('id', $propertyIds)
            ->withCount('units')
            ->withCount(['units as occupied_count' => fn($q) => $q->where('status', 'occupied')])
            ->get();

        // ── Chart — 2 queries instead of 12 ──────────────────────────────
        $sixMonthsAgo = now()->subMonths(5)->startOfMonth();
        $endOfMonth   = now()->endOfMonth();

        $incomeByMonth = Payment::whereIn('lease_id', $leaseIds)
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
            $income   = floatval($incomeByMonth->get($key)?->total   ?? 0);
            $expenses = floatval($expensesByMonth->get($key)?->total  ?? 0);

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