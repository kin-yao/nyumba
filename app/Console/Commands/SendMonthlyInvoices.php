<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Lease;
use App\Models\Message;
use App\Models\UtilityReading;
use AfricasTalking\SDK\AfricasTalking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class SendMonthlyInvoices extends Command
{
    protected $signature   = 'invoices:send-monthly {--force : Run regardless of configured send date}';
    protected $description = 'Auto-generate and send monthly invoices to tenants';

    public function handle()
    {
        $today = now()->day;
        $month = now()->month;
        $year  = now()->year;

        $monthName = \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y');

        $this->info('Running monthly invoice command for ' . $monthName);

        // Get all accounts with auto invoicing enabled
        $accounts = Account::where('auto_invoice_enabled', true)->get();

        if ($accounts->isEmpty()) {
            $this->info('No accounts have auto invoicing enabled.');
            return;
        }

        foreach ($accounts as $account) {

            // Check if today matches this account's send day
            if (!$this->option('force') && $account->invoice_send_day != $today) {
                $this->info('Account ' . $account->name . ': send day is ' . $account->invoice_send_day . ', today is ' . $today . '. Skipping.');
                continue;
            }

            $this->info('Processing account: ' . $account->name);

            $generated = 0;
            $skipped   = [];

            // Get all active leases for this account
            $leases = Lease::with(['unit.property.utilityRates', 'tenant'])
                ->where('status', 'active')
                ->whereHas('unit.property', fn($q) =>
                    $q->where('account_id', $account->id)
                )
                ->get();

            foreach ($leases as $lease) {
                $unit     = $lease->unit;
                $property = $unit->property;
                $tenant   = $lease->tenant;

                // Skip if already invoiced this period
                $exists = Invoice::where('lease_id', $lease->id)
                    ->where('period_month', $month)
                    ->where('period_year', $year)
                    ->exists();

                if ($exists) {
                    $this->line('  Skipping ' . $tenant->full_name . ' (already invoiced)');
                    continue;
                }

                // Check meter readings for meter-based rates
                $meterRates = $property->utilityRates
                    ->whereIn('billing_type', ['per_unit', 'per_meter_reading']);

                $missingReadings = [];

                foreach ($meterRates as $rate) {
                    $reading = UtilityReading::where('unit_id', $unit->id)
                        ->where('utility_type', $rate->type)
                        ->where('reading_month', $month)
                        ->where('reading_year', $year)
                        ->first();

                    if (!$reading) {
                        $missingReadings[] = $rate->name;
                    }
                }

                // Option A: skip tenant if any readings are missing
                if (!empty($missingReadings)) {
                    $skipped[] = [
                        'tenant' => $tenant->full_name,
                        'unit'   => $unit->name,
                        'reason' => implode(', ', $missingReadings) . ' reading(s) missing',
                    ];
                    $this->warn('  Skipping ' . $tenant->full_name . ' (Unit ' . $unit->name . '): missing ' . implode(', ', $missingReadings));
                    continue;
                }

                // Build line items
                $lineItems   = [];
                $totalAmount = 0;

                // Rent
                $lineItems[] = [
                    'description' => $monthName . ' rent',
                    'amount'      => floatval($lease->monthly_rent),
                    'type'        => 'rent',
                ];
                $totalAmount += floatval($lease->monthly_rent);

                // Meter readings
                foreach ($meterRates as $rate) {
                    $reading = UtilityReading::where('unit_id', $unit->id)
                        ->where('utility_type', $rate->type)
                        ->where('reading_month', $month)
                        ->where('reading_year', $year)
                        ->first();

                    if ($reading) {
                        $lineItems[] = [
                            'description' => $rate->name . ' charges',
                            'amount'      => floatval($reading->charge_amount),
                            'type'        => $rate->type,
                        ];
                        $totalAmount += floatval($reading->charge_amount);
                    }
                }

                // Flat fees
                foreach ($property->utilityRates->where('billing_type', 'flat_fee')->where('active', true) as $rate) {
                    $lineItems[] = [
                        'description' => $rate->name,
                        'amount'      => floatval($rate->amount),
                        'type'        => $rate->type,
                    ];
                    $totalAmount += floatval($rate->amount);
                }

                // Create invoice
                $invoice = Invoice::create([
                    'account_id'   => $account->id,
                    'lease_id'     => $lease->id,
                    'reference'    => 'TEMP-' . uniqid(),
                    'period_month' => $month,
                    'period_year'  => $year,
                    'invoice_date' => now()->toDateString(),
                    'due_date'     => now()->addDays(10)->toDateString(),
                    'total_amount' => $totalAmount,
                    'status'       => 'sent',
                ]);

                $invoice->update([
                    'reference' => 'INV-' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT)
                ]);

                foreach ($lineItems as $item) {
                    InvoiceLineItem::create([
                        'invoice_id'  => $invoice->id,
                        'description' => $item['description'],
                        'quantity'    => 1,
                        'unit_price'  => $item['amount'],
                        'amount'      => $item['amount'],
                        'type'        => $item['type'],
                    ]);
                }

                $generated++;
                $this->info('  Generated ' . $invoice->reference . ' for ' . $tenant->full_name);

                // Send SMS with PDF link
                $this->sendInvoiceSms($invoice, $tenant, $account);
            }

            // Send summary to landlord
            $this->sendLandlordSummary($account, $generated, $skipped, $monthName);

            $this->info('Account ' . $account->name . ': ' . $generated . ' invoices generated, ' . count($skipped) . ' skipped.');
        }

        $this->info('Monthly invoice command completed.');
    }

    private function sendInvoiceSms(Invoice $invoice, $tenant, Account $account): void
    {
        if (!$tenant->phone) return;

        $smsService = new \App\Services\SmsService($account);

        if (!$smsService->hasCredits()) {
            \App\Models\Notification::create([
                'account_id' => $account->id,
                'type'       => 'sms_credits_empty',
                'title'      => 'SMS not sent - no credits',
                'body'       => 'Invoice ' . $invoice->reference . ' was generated but SMS could not be sent to ' . $tenant->full_name . ' because you have no SMS credits. Please top up.',
            ]);
            $this->warn('    No SMS credits for ' . $tenant->full_name);
            return;
        }

        $pdfUrl = URL::signedRoute(
            'invoices.pdf.public',
            ['invoice' => $invoice->id],
            now()->addDays(30)
        );

        $message = 'Dear ' . $tenant->first_name . ', your invoice ' . $invoice->reference
            . ' for ' . \Carbon\Carbon::createFromDate($invoice->period_year, $invoice->period_month, 1)->format('F Y')
            . ' is ready. Amount due: KES ' . number_format($invoice->total_amount)
            . '. Due: ' . $invoice->due_date->format('d M Y')
            . '. Download: ' . $pdfUrl;

        $result = $smsService->send($tenant->phone, $message, $tenant->id);

        $this->info('    SMS ' . ($result['success'] ? 'sent' : 'failed') . ' to ' . $tenant->full_name);
    }

    private function sendLandlordSummary(Account $account, int $generated, array $skipped, string $monthName): void
    {
        // Build notification body
        $body = $generated . ' ' . Str::plural('invoice', $generated) . ' generated and sent for ' . $monthName . '.';

        if (!empty($skipped)) {
            $body .= ' ' . count($skipped) . ' ' . Str::plural('tenant', count($skipped)) . ' skipped due to missing readings: ';
            $body .= implode(', ', array_map(fn($s) => 'Unit ' . $s['unit'] . ' (' . $s['reason'] . ')', $skipped)) . '.';
        }

        // Write in-app notification
        \App\Models\Notification::create([
            'account_id' => $account->id,
            'type'       => 'invoice_generated',
            'title'      => $generated . ' invoices generated for ' . $monthName,
            'body'       => $body,
            'data'       => [
                'generated' => $generated,
                'skipped'   => $skipped,
                'month'     => $monthName,
            ],
        ]);

        // Also send SMS to landlord
        $owner = \App\Models\User::where('account_id', $account->id)
            ->where('role', 'owner')
            ->first();

        if (!$owner || !$owner->phone) return;

        $smsBody = $account->name . ': ' . $generated . ' invoices generated and sent for ' . $monthName . '.';

        if (!empty($skipped)) {
            $smsBody .= ' ' . count($skipped) . ' skipped (missing readings): ';
            $smsBody .= implode(', ', array_map(fn($s) => 'Unit ' . $s['unit'], $skipped)) . '.';
        }

        try {
            $at  = new AfricasTalking(
                config('services.africastalking.username'),
                config('services.africastalking.api_key')
            );
            $sms = $at->sms();
            $sms->send([
                'to'      => $this->formatPhone($owner->phone),
                'message' => $smsBody,
                'from'    => config('services.africastalking.from', ''),
            ]);

            $this->info('  Summary SMS sent to landlord.');

        } catch (\Exception $e) {
            Log::error('Landlord summary SMS failed: ' . $e->getMessage());
        }
    }

    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', $phone);

        if (str_starts_with($phone, '0')) {
            return '+254' . substr($phone, 1);
        }

        if (str_starts_with($phone, '254') && !str_starts_with($phone, '+')) {
            return '+' . $phone;
        }

        return $phone;
    }
}