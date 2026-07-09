<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Tenant;

class PaymentController extends Controller
{
    public function index()
    {
        $tenant = Tenant::with(['activeLease.unit.property'])
            ->findOrFail(session('portal_tenant_id'));

        $lease    = $tenant->activeLease;
        $unit     = $lease?->unit;
        $property = $unit?->property;

        $lease?->load(['invoices', 'payments']);

        $totalCharged = floatval($lease?->invoices->sum('total_amount') ?? 0);
        $totalPaid    = floatval(
            $lease?->payments->where('payment_type', '!=', 'deposit')->sum('amount') ?? 0
        );
        $balance = $totalCharged - $totalPaid;

        $depositPaid     = floatval($lease?->deposit_paid ?? 0);
        $depositRequired = floatval($lease?->deposit_required ?? 0);

        return view('portal.payment', compact(
            'tenant', 'lease', 'unit', 'property', 'balance', 'depositPaid', 'depositRequired'
        ));
    }
}