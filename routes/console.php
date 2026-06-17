<?php

use App\Console\Commands\MpesaPullReconcile;
use Illuminate\Support\Facades\Schedule;

// Send monthly invoices — skip expired accounts (handled inside command)
Schedule::command('invoices:send-monthly')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground();

// Mark overdue invoices — runs globally for data integrity
Schedule::command('invoices:mark-overdue')->dailyAt('06:00');

// Send invoice reminders
Schedule::command('invoices:send-reminders')->dailyAt('08:00');

// Send expiry alerts — 3 days before trial, 7 days before paid subscription
Schedule::command('accounts:send-expiry-alerts')->dailyAt('09:00');

// Alert landlords when tenant leases expire in 30 or 7 days
Schedule::command('leases:send-expiry-alerts')->dailyAt('08:30')->withoutOverlapping();

// Apply rent escalations on review date
Schedule::command('leases:apply-escalations')->dailyAt('07:00')->withoutOverlapping();

// Reconcile M-Pesa C2B transactions via Pull API for registered properties.
// Runs every 3 hours — worst case a missed C2B callback is caught within 3hrs.
// Reduce to ->hourly() if landlords report payment delays.
// Pull API allows frequent polling — no rate limit concerns.
Schedule::command(MpesaPullReconcile::class)
    ->everyThreeHours()
    ->withoutOverlapping();

// Top up monthly SMS credits on renewal date
Schedule::call(function () {
    $accounts = \App\Models\Account::where('plan', '!=', 'explore')
        ->where('plan', '!=', 'enterprise')
        ->get();

    foreach ($accounts as $account) {
        if (!$account->subscribed_at) continue;
        if (!$account->isActive()) continue;

        $renewDay = $account->subscribed_at->day;

        if (now()->day === $renewDay) {
            $account->topUpMonthlyCredits();

            \App\Models\Notification::create([
                'account_id' => $account->id,
                'type'       => 'sms_credits_topped_up',
                'title'      => 'SMS credits refreshed',
                'body'       => $account->sms_credits_monthly . ' SMS credits have been added to your account for this month.',
            ]);
        }
    }
})->dailyAt('00:10');

// Send scheduled report alerts
Schedule::call(function () {
    $accounts = \App\Models\Account::where('plan', '!=', 'explore')->get();

    foreach ($accounts as $account) {
        if (!$account->isActive()) continue;
        if (!$account->phone) continue;

        $now        = now();
        $dayOfWeek  = (int) $now->format('N') - 1;
        $dayOfMonth = (int) $now->format('j');
        $month      = (int) $now->format('n');
        $time       = $now->format('H:i');

        $sms = new \App\Services\SmsService($account);
        if (!$sms->hasCredits()) continue;

        if (
            $account->weekly_report_enabled &&
            $dayOfWeek === (int) $account->weekly_report_day &&
            $time === $account->weekly_report_time
        ) {
            \App\Services\ReportSmsService::sendWeekly($account, $sms);
        }

        if (
            $account->monthly_report_enabled &&
            $dayOfMonth === (int) $account->monthly_report_day &&
            $time === $account->monthly_report_time
        ) {
            \App\Services\ReportSmsService::sendMonthly($account, $sms);
        }

        if (
            $account->yearly_report_enabled &&
            $month === (int) $account->yearly_report_month &&
            $dayOfMonth === (int) $account->yearly_report_day &&
            $time === $account->yearly_report_time
        ) {
            \App\Services\ReportSmsService::sendYearly($account, $sms);
        }
    }
})->everyThirtyMinutes();