<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\InvoiceLineItem;
use App\Models\Lease;
use App\Models\Property;
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
        $today     = now()->day;
        $month     = now()->month;
        $year      = now()->year;
        $monthName = \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y');

        $this->info('Running monthly invoice command for ' . $monthName);

        $properties = Property::where('auto_invoice_enabled', true)->with('account')->get();

        if ($properties->isEmpty()) {
            $this->info('No properties have auto invoicing enabled.');
            return;
        }

        foreach ($properties as $property) {

            $account = $property->account;
            if (!$account) {
                $this->warn('Property ' . $property->name . ': no account found. Skipping.');
                continue;
            }

            if (!$this->option('force') && $property->invoice_send_day != $today) {
                $this->info('Property ' . $property->name . ': send day is ' . $property->invoice_send_day . ', today is ' . $today . '. Skipping.');
                continue;
            }

            $this->info('Processing property: ' . $property->name);

            $generated = 0;
            $omitted   = [];

            $leases = Lease::with(['unit.property.utilityRates', 'tenant'])
                ->where('status', 'active')
                ->whereHas('unit', fn($q) => $q->where('property_id', $property->id))
                ->get();

            foreach ($leases as $lease) {
                $unit   = $lease->unit;
                $tenant = $lease->tenant;

                $exists = Invoice::where('lease_id', $lease->id)
                    ->where('period_month', $month)
                    ->where('period_year', $year)
                    ->exists();

                if ($exists) {
                    $this->line('  Skipping ' . $tenant->full_name . ' (already invoiced)');
                    continue;
                }

                $autoRates  = $property->utilityRates
                    ->where('active', true)
                    ->where('auto_bill', true);

                $meterRates = $autoRates->whereIn('billing_type', ['per_unit', 'per_meter_reading']);
                $flatRates  = $autoRates->where('billing_type', 'flat_fee');

                $lineItems   = [];
                $totalAmount = 0;
                $missing     = [];

                $lineItems[] = [
                    'description' => $monthName . ' rent',
                    'amount'      => floatval($lease->monthly_rent),
                    'type'        => 'rent',
                ];
                $totalAmount += floatval($lease->monthly_rent);

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
                    } else {
                        $missing[] = $rate->name;
                    }
                }

                foreach ($flatRates as $rate) {
                    $lineItems[] = [
                        'description' => $rate->name,
                        'amount'      => floatval($rate->amount),
                        'type'        => $rate->type,
                    ];
                    $totalAmount += floatval($rate->amount);
                }

                // Create invoice with TEMP reference first
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

                // #7: per-account sequential reference
                $count = Invoice::where('account_id', $account->id)
                    ->whereNot('reference', 'like', 'TEMP-%')
                    ->count();
                $invoice->update([
                    'reference' => 'INV-' . str_pad($count, 4, '0', STR_PAD_LEFT),
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

                if (!empty($missing)) {
                    $omitted[] = [
                        'tenant'  => $tenant->full_name,
                        'unit'    => $unit->name,
                        'missing' => implode(', ', $missing),
                    ];
                    $this->warn('  ' . $invoice->reference . ' for ' . $tenant->full_name
                        . ' — billed WITHOUT: ' . implode(', ', $missing) . ' (no reading entered)');
                } else {
                    $this->info('  Generated ' . $invoice->reference . ' for ' . $tenant->full_name);
                }

                $this->sendInvoiceSms($invoice, $tenant, $account);
            }

            $this->sendLandlordSummary($account, $property, $generated, $omitted, $monthName);

            $this->info('Property ' . $property->name . ': ' . $generated . ' invoices generated, '
                . count($omitted) . ' with omitted charges.');
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
                'body'       => 'Invoice ' . $invoice->reference . ' was generated but SMS could not be sent to '
                    . $tenant->full_name . ' because you have no SMS credits. Please top up.',
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

    private function sendLandlordSummary(Account $account, Property $property, int $generated, array $omitted, string $monthName): void
    {
        $body = $generated . ' ' . Str::plural('invoice', $generated) . ' generated and sent for '
            . $property->name . ' — ' . $monthName . '.';

        if (!empty($omitted)) {
            $body .= ' ' . count($omitted) . ' ' . Str::plural('invoice', count($omitted))
                . ' were billed without some utility charges due to missing readings: ';
            $body .= implode('; ', array_map(
                fn($o) => 'Unit ' . $o['unit'] . ' — missing: ' . $o['missing'],
                $omitted
            )) . '. Please enter the readings and add these charges manually if needed.';
        }

        \App\Models\Notification::create([
            'account_id' => $account->id,
            'type'       => 'invoice_generated',
            'title'      => $generated . ' invoices generated for ' . $property->name . ' (' . $monthName . ')',
            'body'       => $body,
            'data'       => [
                'property'  => $property->name,
                'generated' => $generated,
                'omitted'   => $omitted,
                'month'     => $monthName,
            ],
        ]);

        $owner = \App\Models\User::where('account_id', $account->id)
            ->where('role', 'owner')
            ->first();

        if (!$owner || !$owner->phone) return;

        $smsBody = $property->name . ': ' . $generated . ' invoices generated for ' . $monthName . '.';

        if (!empty($omitted)) {
            $smsBody .= ' ' . count($omitted) . ' billed without some charges (missing readings): ';
            $smsBody .= implode(', ', array_map(fn($o) => 'Unit ' . $o['unit'], $omitted)) . '.';
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
        if (str_starts_with($phone, '0'))   return '+254' . substr($phone, 1);
        if (str_starts_with($phone, '254') && !str_starts_with($phone, '+')) return '+' . $phone;
        return $phone;
    }
}