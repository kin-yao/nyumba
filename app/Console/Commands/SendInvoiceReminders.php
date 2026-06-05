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
    protected $description = 'Send SMS reminders to tenants for invoices due in 3 days and due today';

    public function handle(): void
    {
        $today      = now()->toDateString();
        $threeDays  = now()->addDays(3)->toDateString();

        $sent   = 0;
        $failed = 0;

        // 3-day reminders
        $invoices3Day = Invoice::with(['lease.tenant', 'lease.unit.property'])
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->whereDate('due_date', $threeDays)
            ->whereNull('reminder_3day_sent_at')
            ->get();

        foreach ($invoices3Day as $invoice) {
            $result = $this->sendReminder($invoice, '3day');
            $result ? $sent++ : $failed++;
        }

        // Due today reminders
        $invoicesToday = Invoice::with(['lease.tenant', 'lease.unit.property'])
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->whereDate('due_date', $today)
            ->whereNull('reminder_due_sent_at')
            ->get();

        foreach ($invoicesToday as $invoice) {
            $result = $this->sendReminder($invoice, 'due');
            $result ? $sent++ : $failed++;
        }

        $this->info("Reminders sent: {$sent}. Failed: {$failed}.");
    }

    private function sendReminder(Invoice $invoice, string $type): bool
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

        $balance    = $invoice->total_amount - $invoice->amount_paid;
        $dueDate    = $invoice->due_date->format('d M Y');
        $propName   = strtoupper($property?->name ?? '');
        $unitName   = $unit?->name ?? '';

        if ($type === '3day') {
            $message =
                'RENT REMINDER - ' . $propName . "\n" .
                'Dear ' . $tenant->first_name . ',' . "\n" .
                'Invoice ' . $invoice->reference . ' for Unit ' . $unitName . ' is due in 3 days on ' . $dueDate . '.' . "\n" .
                'Amount due: ' . currency($balance) . "\n" .
                'Please ensure payment is made on time.' . "\n" .
                'Powered by Nyumba.';

            $column = 'reminder_3day_sent_at';
            $event  = 'invoice.reminder_3day';
            $desc   = '3-day SMS reminder sent to ' . $tenant->full_name . ' for ' . $invoice->reference;
        } else {
            $message =
                'RENT DUE TODAY - ' . $propName . "\n" .
                'Dear ' . $tenant->first_name . ',' . "\n" .
                'Invoice ' . $invoice->reference . ' for Unit ' . $unitName . ' is due TODAY (' . $dueDate . ').' . "\n" .
                'Amount due: ' . currency($balance) . "\n" .
                'Please pay now to avoid being marked overdue.' . "\n" .
                'Powered by Nyumba.';

            $column = 'reminder_due_sent_at';
            $event  = 'invoice.reminder_due';
            $desc   = 'Due-date SMS reminder sent to ' . $tenant->full_name . ' for ' . $invoice->reference;
        }

        $result = $sms->send($tenant->phone, $message, $tenant->id);

        if ($result['success']) {
            $invoice->update([$column => now()]);

            AuditService::system(
                $invoice->account_id,
                $event,
                $desc,
                $invoice,
                [
                    'tenant'    => $tenant->full_name,
                    'phone'     => $tenant->phone,
                    'due_date'  => $dueDate,
                    'balance'   => $balance,
                ]
            );

            return true;
        }

        return false;
    }
}