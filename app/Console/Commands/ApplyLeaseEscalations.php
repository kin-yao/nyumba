<?php

namespace App\Console\Commands;

use App\Models\Lease;
use App\Models\Notification;
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Command;

class ApplyLeaseEscalations extends Command
{
    protected $signature   = 'leases:apply-escalations';
    protected $description = 'Apply rent escalations to leases due for review today';

    public function handle(): int
    {
        $today   = now()->toDateString();
        $applied = 0;
        $skipped = 0;

        $leases = Lease::with(['tenant', 'unit.property'])
            ->where('status', 'active')
            ->whereNotNull('next_review_date')
            ->whereNotNull('escalation_percentage')
            ->where('escalation_percentage', '>', 0)
            ->whereDate('next_review_date', $today)
            ->get();

        foreach ($leases as $lease) {
            $result = $this->applyEscalation($lease);
            $result ? $applied++ : $skipped++;
        }

        $this->info("Escalations applied: {$applied}. Skipped: {$skipped}.");

        return self::SUCCESS;
    }

    private function applyEscalation(Lease $lease): bool
    {
        $tenant   = $lease->tenant;
        $unit     = $lease->unit;
        $property = $unit?->property;
        $account  = $property?->account;

        if (!$tenant || !$unit || !$property || !$account) return false;

        $oldRent    = floatval($lease->monthly_rent);
        $percentage = floatval($lease->escalation_percentage);
        $increase   = round($oldRent * ($percentage / 100));
        $newRent    = $oldRent + $increase;

        // Set next review date to 1 year from today
        $nextReview = now()->addYear()->toDateString();

        $lease->update([
            'monthly_rent'     => $newRent,
            'next_review_date' => $nextReview,
        ]);

        $effectiveDate = now()->format('d M Y');
        $nextReviewFmt = now()->addYear()->format('d M Y');

        // In-app notification to landlord
        Notification::create([
            'account_id' => $account->id,
            'type'       => 'rent_escalation_applied',
            'title'      => 'Rent escalation applied — ' . $tenant->full_name,
            'body'       => 'Rent for ' . $tenant->full_name . ' (Unit ' . $unit->name . ', '
                . $property->name . ') has been automatically increased by '
                . $percentage . '% from KES ' . number_format($oldRent)
                . ' to KES ' . number_format($newRent)
                . ' effective ' . $effectiveDate . '. '
                . 'Next review: ' . $nextReviewFmt . '.',
        ]);

        // SMS to landlord
        $landlordPhone = $account->phone
            ?? $account->users()->where('role', 'owner')->value('phone');

        if ($landlordPhone) {
            try {
                $sms = new SmsService($account);

                $landlordMsg =
                    'RENT ESCALATION APPLIED - ' . strtoupper($property->name) . "\n" .
                    'Tenant: ' . $tenant->full_name . "\n" .
                    'Unit: ' . $unit->name . "\n" .
                    'Old rent: KES ' . number_format($oldRent) . "\n" .
                    'New rent: KES ' . number_format($newRent) . ' (+' . $percentage . '%)' . "\n" .
                    'Effective: ' . $effectiveDate . "\n" .
                    'Next review: ' . $nextReviewFmt . "\n" .
                    'Powered by Nyumba.';

                $sms->send($landlordPhone, $landlordMsg);
            } catch (\Exception $e) {
                Log::error('Escalation landlord SMS failed: ' . $e->getMessage());
            }
        }

        // SMS to tenant
        if ($tenant->phone) {
            try {
                $sms = new SmsService($account);

                $tenantMsg =
                    'RENT INCREASE NOTICE - ' . strtoupper($property->name) . "\n" .
                    'Dear ' . $tenant->first_name . ',' . "\n" .
                    'Your monthly rent for Unit ' . $unit->name . ' has been revised.' . "\n" .
                    'Previous rent: KES ' . number_format($oldRent) . "\n" .
                    'New rent: KES ' . number_format($newRent) . "\n" .
                    'Effective: ' . $effectiveDate . "\n" .
                    'Please update your payment standing order if applicable.' . "\n" .
                    'Powered by Nyumba.';

                $sms->send($tenant->phone, $tenantMsg, $tenant->id);
            } catch (\Exception $e) {
                Log::error('Escalation tenant SMS failed: ' . $e->getMessage());
            }
        }

        $this->line(
            'Escalation applied: ' . $tenant->full_name
            . ' Unit ' . $unit->name
            . ' KES ' . number_format($oldRent) . ' → KES ' . number_format($newRent)
            . ' (+' . $percentage . '%)'
        );

        return true;
    }
}