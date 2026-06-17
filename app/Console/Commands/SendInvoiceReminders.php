<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Invoice;
use App\Services\AuditService;
use App\Services\SmsService;
use Illuminate\Console\Command;

class SendInvoiceReminders extends Command
{
    protected $signature   = 'invoices:send-reminders';
    protected $description = 'Send SMS reminders to tenants for invoices due in 3 days';

    public function handle(): void
    {
        $threeDays = now()->addDays(3)->toDateString();

        $sent   = 0;
        $failed = 0;

        $invoices = Invoice::with(['lease.tenant', 'lease.unit.property'])
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->whereDate('due_date', $threeDays)
            ->whereNull('reminder_3day_sent_at')
            ->get();

        foreach ($invoices as $invoice) {
            $result = $this->sendReminder($invoice);
            $result ? $sent++ : $failed++;
        }

        $this->info("3-day reminders sent: {$sent}. Failed: {$failed}.");
    }

    private function sendReminder(Invoice $invoice): bool
    {
        $lease    = $invoice->lease;
        $tenant   = $lease?->tenant;
        $unit     = $lease?->unit;
        $property = $unit?->property;

        if (!$tenant?->phone) return false;

        $account = Account::find($invoice->account_id);
        if (!$account) return false;

        $sms = new SmsService($account);
        if (!$sms->hasCredits()) return false;

        $balance  = floatval($invoice->total_amount) - floatval($invoice->amount_paid);
        $dueDate  = $invoice->due_date->format('d M Y');
        $propName = strtoupper($property?->name ?? '');
        $unitName = $unit?->name ?? '';

        $message =
            'RENT REMINDER - ' . $propName . "\n" .
            'Dear ' . $tenant->first_name . ',' . "\n" .
            'Invoice ' . $invoice->reference . ' for Unit ' . $unitName . ' is due in 3 days on ' . $dueDate . '.' . "\n" .
            'Amount due: KES ' . number_format($balance) . "\n" .
            'Please ensure payment is made on time.' . "\n" .
            'Powered by Nyumba.';

        $result = $sms->send($tenant->phone, $message, $tenant->id);

        if ($result['success']) {
            $invoice->update(['reminder_3day_sent_at' => now()]);

            AuditService::system(
                $invoice->account_id,
                'invoice.reminder_3day',
                '3-day SMS reminder sent to ' . $tenant->full_name . ' for ' . $invoice->reference,
                $invoice,
                [
                    'tenant'   => $tenant->full_name,
                    'phone'    => $tenant->phone,
                    'due_date' => $dueDate,
                    'balance'  => $balance,
                ]
            );

            return true;
        }

        return false;
    }
}