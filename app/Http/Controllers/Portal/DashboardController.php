<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected function tenant(): Tenant
    {
        return Tenant::with(['activeLease.unit.property'])
            ->findOrFail(session('portal_tenant_id'));
    }

    public function index()
    {
        $tenant = $this->tenant();
        $lease  = $tenant->activeLease;
        $unit   = $lease?->unit;
        $property = $unit?->property;

        $lease?->load(['invoices.lineItems', 'payments']);

        $ledger = collect();

        if ($lease) {
            foreach ($lease->invoices as $invoice) {
                foreach ($invoice->lineItems as $item) {
                    $ledger->push([
                        'date'        => $invoice->invoice_date,
                        'description' => $item->description,
                        'charged'     => floatval($item->amount),
                        'paid'        => null,
                        'reference'   => $invoice->reference,
                    ]);
                }
            }

            foreach ($lease->payments as $payment) {
                $ledger->push([
                    'date'        => $payment->payment_date,
                    'description' => $payment->payment_type === 'deposit'
                        ? 'Security deposit received'
                        : 'Payment received',
                    'charged'     => null,
                    'paid'        => floatval($payment->amount),
                    'reference'   => $payment->reference ?? strtoupper($payment->method),
                ]);
            }
        }

        $ledger = $ledger->sortBy('date')->values();

        // Group ledger entries by month for the "as they appear each month" view
        $ledgerByMonth = $ledger->groupBy(fn($row) => $row['date']->format('F Y'));

        $totalCharged = floatval($lease?->invoices->sum('total_amount') ?? 0);
        $totalPaid    = floatval(
            $lease?->payments->where('payment_type', '!=', 'deposit')->sum('amount') ?? 0
        );
        $balance = $totalCharged - $totalPaid;

        return view('portal.dashboard', compact(
            'tenant', 'lease', 'unit', 'property', 'ledgerByMonth', 'balance'
        ));
    }
}