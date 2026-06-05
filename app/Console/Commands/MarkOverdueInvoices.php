<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class MarkOverdueInvoices extends Command
{
    protected $signature   = 'invoices:mark-overdue';
    protected $description = 'Mark all past-due unpaid invoices as overdue';

    public function handle(): int
    {
        $updated = Invoice::whereIn('status', ['sent', 'partial'])
            ->where('due_date', '<', now()->startOfDay())
            ->update(['status' => 'overdue']);

        $this->info($updated . ' ' . str($updated === 1 ? 'invoice' : 'invoices') . ' marked as overdue.');

        return self::SUCCESS;
    }
}