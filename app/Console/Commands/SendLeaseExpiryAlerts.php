<?php

namespace App\Console\Commands;

use App\Models\Lease;
use App\Models\Notification;
use App\Services\SmsService;
use Illuminate\Console\Command;

class SendLeaseExpiryAlerts extends Command
{
    protected $signature   = 'leases:send-expiry-alerts';
    protected $description = 'Alert landlords when tenant leases are expiring in 30 or 7 days';

    public function handle(): int
    {
        $sent   = 0;
        $failed = 0;

        // Check both 30-day and 7-day windows
        foreach ([30, 7] as $daysAhead) {
            $targetDate = now()->addDays($daysAhead)->toDateString();

            $leases = Lease::with(['tenant', 'unit.property'])
                ->where('status', 'active')
                ->whereNotNull('lease_end_date')
                ->whereDate('lease_end_date', $targetDate)
                ->get();

            foreach ($leases as $lease) {
                $result = $this->sendAlert($lease, $daysAhead);
                $result ? $sent++ : $failed++;
            }
        }

        $this->info("Lease expiry alerts sent: {$sent}. Failed: {$failed}.");

        return self::SUCCESS;
    }

    private function sendAlert(Lease $lease, int $daysAhead): bool
    {
        $tenant   = $lease->tenant;
        $unit     = $lease->unit;
        $property = $unit?->property;
        $account  = $property?->account;

        if (!$tenant || !$unit || !$property || !$account) return false;

        $expiryDate = $lease->lease_end_date->format('d M Y');
        $notifType  = 'lease_expiry_' . $daysAhead . 'day';

        // Skip if already sent this alert for this lease at this window
        $alreadySent = Notification::where('account_id', $account->id)
            ->where('type', $notifType)
            ->where('body', 'like', '%' . $tenant->full_name . '%')
            ->where('created_at', '>=', now()->subDays(2))
            ->exists();

        if ($alreadySent) return false;

        $title = 'Lease expiring in ' . $daysAhead . ' days — ' . $tenant->full_name;
        $body  = $tenant->full_name . ' (Unit ' . $unit->name . ', ' . $property->name . ') '
            . 'has a fixed-term lease expiring on ' . $expiryDate . ' (' . $daysAhead . ' days). '
            . 'Decide whether to renew, convert to open-ended, or begin move-out process.';

        // In-app notification to landlord
        Notification::create([
            'account_id' => $account->id,
            'type'       => $notifType,
            'title'      => $title,
            'body'       => $body,
        ]);

        // SMS to landlord's phone
        $landlordPhone = $account->phone
            ?? $account->users()->where('role', 'owner')->value('phone');

        if ($landlordPhone) {
            try {
                $sms = new SmsService($account);

                $message =
                    'LEASE EXPIRY ALERT - ' . strtoupper($property->name) . "\n" .
                    'Tenant: ' . $tenant->full_name . "\n" .
                    'Unit: ' . $unit->name . "\n" .
                    'Lease ends: ' . $expiryDate . ' (' . $daysAhead . ' days)' . "\n" .
                    'Action required: Renew or begin move-out.' . "\n" .
                    'Powered by Nyumba.';

                $sms->send($landlordPhone, $message);
            } catch (\Exception $e) {
                \Log::error('Lease expiry SMS failed: ' . $e->getMessage());
            }
        }

        $this->line(
            $daysAhead . '-day expiry alert sent for '
            . $tenant->full_name . ' (Unit ' . $unit->name . ')'
        );

        return true;
    }
}