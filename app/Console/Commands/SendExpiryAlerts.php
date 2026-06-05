<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Notification;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Console\Command;

class SendExpiryAlerts extends Command
{
    protected $signature   = 'accounts:send-expiry-alerts';
    protected $description = 'Send SMS + notification alerts before trial and subscription expiry';

    public function handle(): int
    {
        $this->alertTrialAccounts();
        $this->alertPaidAccounts();

        return self::SUCCESS;
    }

    private function alertTrialAccounts(): void
    {
        // Explore accounts whose trial ends exactly 3 days from today
        $accounts = Account::where('plan', 'explore')
            ->whereNotNull('trial_ends_at')
            ->whereDate('trial_ends_at', now()->addDays(3)->toDateString())
            ->get();

        foreach ($accounts as $account) {
            // Skip if alert already sent
            $alreadySent = Notification::where('account_id', $account->id)
                ->where('type', 'trial_expiry_alert')
                ->exists();

            if ($alreadySent) continue;

            $daysLeft  = 3;
            $expiryDate = $account->trial_ends_at->format('d M Y');

            Notification::create([
                'account_id' => $account->id,
                'type'       => 'trial_expiry_alert',
                'title'      => 'Your free trial ends in ' . $daysLeft . ' days',
                'body'       => 'Your 30-day free trial expires on ' . $expiryDate . '. '
                    . 'Upgrade to a paid plan to keep managing your properties without interruption. '
                    . 'Contact us on WhatsApp: +254705056343',
            ]);

            $this->sendSms($account,
                'NYUMBA ALERT: Your free trial expires in ' . $daysLeft . ' days (' . $expiryDate . '). '
                . 'Upgrade now to avoid losing access. '
                . 'WhatsApp us: +254705056343'
            );

            $this->line('Trial alert sent: ' . $account->name);
        }
    }

    private function alertPaidAccounts(): void
    {
        // Paid accounts whose subscription ends exactly 7 days from today
        $accounts = Account::where('plan', '!=', 'explore')
            ->whereNotNull('plan_expires_at')
            ->whereDate('plan_expires_at', now()->addDays(7)->toDateString())
            ->get();

        foreach ($accounts as $account) {
            $alreadySent = Notification::where('account_id', $account->id)
                ->where('type', 'subscription_expiry_alert')
                ->where('created_at', '>=', now()->subDays(8))
                ->exists();

            if ($alreadySent) continue;

            $expiryDate = $account->plan_expires_at->format('d M Y');
            $planName   = ucfirst($account->plan);

            Notification::create([
                'account_id' => $account->id,
                'type'       => 'subscription_expiry_alert',
                'title'      => 'Your ' . $planName . ' subscription expires in 7 days',
                'body'       => 'Your Nyumba ' . $planName . ' plan expires on ' . $expiryDate . '. '
                    . 'Renew now to keep your automated invoicing, payment tracking and SMS alerts running. '
                    . 'Contact us on WhatsApp: +254705056343',
            ]);

            $this->sendSms($account,
                'NYUMBA ALERT: Your ' . $planName . ' plan expires in 7 days (' . $expiryDate . '). '
                . 'Renew to avoid interruption. '
                . 'WhatsApp: +254705056343'
            );

            $this->line('Subscription alert sent: ' . $account->name);
        }
    }

    private function sendSms(Account $account, string $message): void
    {
        $phone = $account->phone;
        if (!$phone) {
            // Fall back to owner user's phone
            $phone = $account->users()->where('role', 'owner')->value('phone');
        }

        if (!$phone) return;

        try {
            $sms = new SmsService($account);
            // Use Nyumba's own credits for alerts — don't deduct from landlord
            $sms->send($phone, $message);
        } catch (\Exception $e) {
            \Log::error('Expiry alert SMS failed for account ' . $account->id . ': ' . $e->getMessage());
        }
    }
}