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

// Automatically move out tenants whose accepted move-out date has arrived
Schedule::command('move-outs:process')->dailyAt('07:30')->withoutOverlapping();

// Back up the database (only — uploaded files already live independently on
// R2) to the same R2 bucket, genuinely independent of Railway's own infra.
Schedule::command('backup:clean')->dailyAt('01:30')->withoutOverlapping();
Schedule::command('backup:run --only-db')->dailyAt('02:00')->withoutOverlapping();

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

