<?php

use Illuminate\Support\Facades\Schedule;

// Send monthly invoices
Schedule::command('invoices:send-monthly')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->runInBackground();

// Mark overdue invoices — runs daily at 6 AM (replaces the old inline version)
Schedule::command('invoices:mark-overdue')->dailyAt('06:00');

// Send invoice reminders — runs daily at 8 AM
Schedule::command('invoices:send-reminders')->dailyAt('08:00');

// Check for expired subscriptions and set grace periods
Schedule::call(function () {
    $accounts = \App\Models\Account::where('plan', '!=', 'explore')
        ->where('plan_expires_at', '<', now())
        ->whereNull('grace_period_ends_at')
        ->get();

    foreach ($accounts as $account) {
        $account->update([
            'grace_period_ends_at' => now()->addDays(5),
        ]);

        \App\Models\Notification::create([
            'account_id' => $account->id,
            'type'       => 'subscription_expired',
            'title'      => 'Subscription expired',
            'body'       => 'Your subscription has expired. You have 5 days to renew before your account is locked. Contact us to renew.',
        ]);
    }
})->dailyAt('00:01');

// Top up monthly SMS credits on renewal date
Schedule::call(function () {
    $accounts = \App\Models\Account::where('plan', '!=', 'explore')
        ->where('plan', '!=', 'enterprise')
        ->get();

    foreach ($accounts as $account) {
        if (!$account->subscribed_at) continue;

        $renewDay = $account->subscribed_at->day;

        if (now()->day === $renewDay && $account->isActive()) {
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

// Send scheduled report alerts — runs every 30 minutes
Schedule::call(function () {
    $accounts = \App\Models\Account::where('plan', '!=', 'explore')->get();

    foreach ($accounts as $account) {
        if (!$account->isActive()) continue;
        if (!$account->phone) continue;

        $now        = now();
        $dayOfWeek  = (int) $now->format('N') - 1; // 0=Mon, 6=Sun
        $dayOfMonth = (int) $now->format('j');
        $month      = (int) $now->format('n');
        $time       = $now->format('H:i');

        $sms = new \App\Services\SmsService($account);
        if (!$sms->hasCredits()) continue;

        // Weekly report
        if (
            $account->weekly_report_enabled &&
            $dayOfWeek === (int) $account->weekly_report_day &&
            $time === $account->weekly_report_time
        ) {
            \App\Services\ReportSmsService::sendWeekly($account, $sms);
        }

        // Monthly report
        if (
            $account->monthly_report_enabled &&
            $dayOfMonth === (int) $account->monthly_report_day &&
            $time === $account->monthly_report_time
        ) {
            \App\Services\ReportSmsService::sendMonthly($account, $sms);
        }

        // Yearly report
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