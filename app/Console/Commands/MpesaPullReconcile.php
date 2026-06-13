<?php

namespace App\Console\Commands;

use App\Http\Controllers\MpesaC2BController;
use App\Models\Property;
use App\Services\MpesaService;
use Illuminate\Console\Command;

class MpesaPullReconcile extends Command
{
    protected $signature = 'mpesa:pull-reconcile';
    protected $description = 'Pull missed M-Pesa C2B transactions for properties with Pull API registered, and reconcile them.';

    public function handle(MpesaService $mpesa, MpesaC2BController $c2b)
    {
        $properties = Property::whereNotNull('mpesa_pull_registered_at')
            ->whereNotNull('mpesa_shortcode')
            ->whereNotNull('mpesa_consumer_key')
            ->whereNotNull('mpesa_consumer_secret')
            ->get();

        if ($properties->isEmpty()) {
            $this->info('No properties registered for Pull reconciliation.');
            return;
        }

        $endDate   = now()->format('Y-m-d H:i:s');
        $startDate = now()->subHours(48)->format('Y-m-d H:i:s');

        foreach ($properties as $property) {
            $this->info("Checking property #{$property->id} ({$property->name})...");

            $result = $mpesa->pullTransactions(
                shortcode: $property->mpesa_shortcode,
                consumerKey: $property->mpesa_consumer_key,
                consumerSecret: $property->mpesa_consumer_secret,
                startDate: $startDate,
                endDate: $endDate,
            );

            if (!$result['success']) {
                $this->error("  Failed: {$result['error']}");
                continue;
            }

            $matched = $unmatched = $duplicate = 0;

            foreach ($result['transactions'] as $txn) {
                // Only Paybill debit transactions are relevant
                if (!str_starts_with($txn['transactiontype'] ?? '', 'c2b-pay-bill')) {
                    continue;
                }

                $status = $c2b->processTransaction($property, [
                    'TransID'           => $txn['transactionId'] ?? null,
                    'TransAmount'       => $txn['amount'] ?? null,
                    'BillRefNumber'     => $txn['billreference'] ?? null,
                    'MSISDN'            => $txn['msisdn'] ?? null,
                    'TransTime'         => isset($txn['trxDate'])
                        ? \Carbon\Carbon::parse($txn['trxDate'])->format('YmdHis')
                        : null,
                    'BusinessShortCode' => $property->mpesa_shortcode,
                ]);

                match ($status) {
                    'matched'   => $matched++,
                    'unmatched' => $unmatched++,
                    'duplicate' => $duplicate++,
                    default     => null,
                };
            }

            $this->info("  Matched: {$matched}, Unmatched: {$unmatched}, Already recorded: {$duplicate}");
        }
    }
}