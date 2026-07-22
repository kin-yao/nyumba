<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\ProofOfPayment;
use App\Models\Tenant;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected function tenant(): Tenant
    {
        return Tenant::with(['activeLease.unit.property'])
            ->findOrFail(session('portal_tenant_id'));
    }

    public function index()
    {
        $tenant   = $this->tenant();
        $lease    = $tenant->activeLease;
        $unit     = $lease?->unit;
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
        $ledgerByMonth = $ledger->groupBy(fn($row) => $row['date']->format('F Y'));

        $totalCharged = floatval($lease?->invoices->sum('total_amount') ?? 0);
        $totalPaid    = floatval(
            $lease?->payments->where('payment_type', '!=', 'deposit')->sum('amount') ?? 0
        );
        $balance = $totalCharged - $totalPaid;

        $depositPaid     = floatval($lease?->deposit_paid ?? 0);
        $depositRequired = floatval($lease?->deposit_required ?? 0);

        $proofs = $lease
            ? ProofOfPayment::where('lease_id', $lease->id)->latest()->get()
            : collect();

        return view('portal.payment', compact(
            'tenant', 'lease', 'unit', 'property', 'balance', 'depositPaid', 'depositRequired', 'proofs', 'ledgerByMonth'
        ));
    }

    public function storeProof(Request $request)
    {
        $validated = $request->validate([
            'payment_for'  => ['required', 'in:rent,deposit'],
            'period_month' => ['required_if:payment_for,rent', 'nullable', 'integer', 'between:1,12'],
            'period_year'  => ['required_if:payment_for,rent', 'nullable', 'integer', 'digits:4'],
            'method'       => ['required', 'in:mpesa,cash,bank,cheque'],
            'message'      => ['required', 'string', 'max:2000'],
        ]);

        $tenant = $this->tenant();
        $lease  = $tenant->activeLease;

        if (!$lease) {
            return back()->with('error', 'No active tenancy found.');
        }

        $unit = $lease->unit;

        $proof = ProofOfPayment::create([
            'account_id'   => $unit->property->account_id,
            'tenant_id'    => $tenant->id,
            'lease_id'     => $lease->id,
            'payment_for'  => $validated['payment_for'],
            'period_month' => $validated['payment_for'] === 'rent' ? $validated['period_month'] : null,
            'period_year'  => $validated['payment_for'] === 'rent' ? $validated['period_year'] : null,
            'method'       => $validated['method'],
            'message'      => $validated['message'],
            'status'       => 'pending',
        ]);

        Notification::create([
            'account_id' => $unit->property->account_id,
            'type'       => 'proof_of_payment_submitted',
            'title'      => 'Proof of payment from ' . $tenant->full_name,
            'body'       => 'Unit ' . $unit->name . ' (' . $unit->property->name . ') — '
                . ($validated['payment_for'] === 'deposit' ? 'security deposit' : 'rent/utilities' . ($proof->periodLabel() ? ' for ' . $proof->periodLabel() : ''))
                . ' via ' . strtoupper($validated['method']) . '. Needs verification.',
            'data'       => ['unit' => $unit->name, 'proof_of_payment_id' => $proof->id],
        ]);

        return back()->with('success', 'Thanks — your landlord has been notified and will verify your payment shortly.');
    }
}