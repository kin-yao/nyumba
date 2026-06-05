<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Unit;
use App\Models\MaintenanceRequest;

class ReportSmsService
{
    public static function sendWeekly(Account $account, SmsService $sms): void
    {
        $propertyIds = $account->properties()->pluck('id')->toArray();
        $unitIds     = Unit::whereIn('property_id', $propertyIds)->pluck('id')->toArray();
        $leaseIds    = Lease::whereIn('unit_id', $unitIds)->pluck('id')->toArray();

        $weekStart = now()->startOfWeek();
        $weekEnd   = now()->endOfWeek();

        $paymentsThisWeek = Payment::whereIn('lease_id', $leaseIds)
            ->whereBetween('payment_date', [$weekStart, $weekEnd])
            ->get();

        $totalCollected = $paymentsThisWeek->sum('amount');
        $paymentCount   = $paymentsThisWeek->count();

        // Month collection rate
        $invoicesThisMonth = Invoice::whereIn('lease_id', $leaseIds)
            ->whereMonth('invoice_date', now()->month)
            ->whereYear('invoice_date', now()->year)
            ->get();

        $expected       = $invoicesThisMonth->sum('total_amount');
        $collected      = $invoicesThisMonth->sum('amount_paid');
        $collectionRate = $expected > 0 ? round(($collected / $expected) * 100) : 0;

        $outstanding = Lease::with(['invoices', 'payments'])
            ->where('status', 'active')
            ->whereIn('unit_id', $unitIds)
            ->get()
            ->sum(fn($l) => $l->invoices->sum('total_amount') - $l->payments->sum('amount'));

        $overdueCount = Invoice::whereIn('lease_id', $leaseIds)
            ->where(fn($q) => $q->where('status', 'overdue')
                ->orWhere(fn($q2) => $q2->where('status', 'sent')->where('due_date', '<', now())))
            ->count();

        $maintenanceCount = MaintenanceRequest::whereIn('unit_id', $unitIds)
            ->where('status', 'open')
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->count();

        $message =
            'NYUMBA WEEKLY REPORT' . "\n" .
            $account->name . "\n" .
            'Week: ' . $weekStart->format('d M') . ' - ' . $weekEnd->format('d M Y') . "\n\n" .
            'Payments this week: ' . $paymentCount . ' (KES ' . number_format($totalCollected) . ')' . "\n" .
            'Collection rate: ' . $collectionRate . '%' . "\n" .
            'Outstanding: KES ' . number_format($outstanding) . "\n" .
            'Overdue invoices: ' . $overdueCount . "\n" .
            'New maintenance: ' . $maintenanceCount . "\n\n" .
            'Login to Nyumba for details.';

        $sms->send($account->phone, $message);
    }

    public static function sendMonthly(Account $account, SmsService $sms): void
    {
        $propertyIds = $account->properties()->pluck('id')->toArray();
        $unitIds     = Unit::whereIn('property_id', $propertyIds)->pluck('id')->toArray();
        $leaseIds    = Lease::whereIn('unit_id', $unitIds)->pluck('id')->toArray();

        $month = now()->subMonth()->month;
        $year  = now()->subMonth()->year;
        $monthName = now()->subMonth()->format('F Y');

        $invoices = Invoice::whereIn('lease_id', $leaseIds)
            ->whereMonth('invoice_date', $month)
            ->whereYear('invoice_date', $year)
            ->get();

        $expected       = $invoices->sum('total_amount');
        $collected      = $invoices->sum('amount_paid');
        $collectionRate = $expected > 0 ? round(($collected / $expected) * 100) : 0;

        $expenses = Expense::whereIn('property_id', $propertyIds)
            ->whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->sum('amount');

        $payments = Payment::whereIn('lease_id', $leaseIds)
            ->whereMonth('payment_date', $month)
            ->whereYear('payment_date', $year)
            ->sum('amount');

        $netProfit = $payments - $expenses;

        $totalUnits    = count($unitIds);
        $occupiedUnits = Unit::whereIn('id', $unitIds)->where('status', 'occupied')->count();
        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100) : 0;

        $outstanding = Lease::with(['invoices', 'payments'])
            ->where('status', 'active')
            ->whereIn('unit_id', $unitIds)
            ->get()
            ->sum(fn($l) => $l->invoices->sum('total_amount') - $l->payments->sum('amount'));

        $message =
            'NYUMBA MONTHLY REPORT' . "\n" .
            $account->name . "\n" .
            $monthName . "\n\n" .
            'Expected: KES ' . number_format($expected) . "\n" .
            'Collected: KES ' . number_format($collected) . "\n" .
            'Collection rate: ' . $collectionRate . '%' . "\n" .
            'Outstanding: KES ' . number_format($outstanding) . "\n" .
            'Expenses: KES ' . number_format($expenses) . "\n" .
            'Net profit: KES ' . number_format($netProfit) . "\n" .
            'Occupancy: ' . $occupancyRate . '%' . "\n\n" .
            'Login to Nyumba for full report.';

        $sms->send($account->phone, $message);
    }

    public static function sendYearly(Account $account, SmsService $sms): void
    {
        $propertyIds = $account->properties()->pluck('id')->toArray();
        $unitIds     = Unit::whereIn('property_id', $propertyIds)->pluck('id')->toArray();
        $leaseIds    = Lease::whereIn('unit_id', $unitIds)->pluck('id')->toArray();

        $year = now()->subYear()->year;

        $totalIncome = Payment::whereIn('lease_id', $leaseIds)
            ->whereYear('payment_date', $year)
            ->sum('amount');

        $totalExpenses = Expense::whereIn('property_id', $propertyIds)
            ->whereYear('expense_date', $year)
            ->sum('amount');

        $netProfit = $totalIncome - $totalExpenses;

        // Best month by income
        $bestMonth = null;
        $bestIncome = 0;
        for ($m = 1; $m <= 12; $m++) {
            $monthIncome = Payment::whereIn('lease_id', $leaseIds)
                ->whereYear('payment_date', $year)
                ->whereMonth('payment_date', $m)
                ->sum('amount');
            if ($monthIncome > $bestIncome) {
                $bestIncome = $monthIncome;
                $bestMonth  = \Carbon\Carbon::createFromDate($year, $m, 1)->format('F');
            }
        }

        // Average collection rate
        $invoices = Invoice::whereIn('lease_id', $leaseIds)
            ->whereYear('invoice_date', $year)
            ->get();

        $avgCollectionRate = $invoices->sum('total_amount') > 0
            ? round(($invoices->sum('amount_paid') / $invoices->sum('total_amount')) * 100)
            : 0;

        $message =
            'NYUMBA YEARLY REPORT' . "\n" .
            $account->name . "\n" .
            'Year: ' . $year . "\n\n" .
            'Total income: KES ' . number_format($totalIncome) . "\n" .
            'Total expenses: KES ' . number_format($totalExpenses) . "\n" .
            'Net profit: KES ' . number_format($netProfit) . "\n" .
            'Avg collection rate: ' . $avgCollectionRate . '%' . "\n" .
            'Best month: ' . ($bestMonth ?? 'N/A') . ' (KES ' . number_format($bestIncome) . ')' . "\n\n" .
            'Login to Nyumba for full annual report.';

        $sms->send($account->phone, $message);
    }
}