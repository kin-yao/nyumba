<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Support\Money;
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

        $lease?->load(['payments']);

        $totalChargedSafe = Money::normalize($lease?->invoices()->sum('total_amount') ?? 0);
        $totalPaidSafe    = $lease
            ? $lease->payments->where('payment_type', '!=', 'deposit')->reduce(fn($carry, $payment) => Money::add($carry, $payment->amount), '0.00')
            : '0.00';
        $balance = (float) Money::sub($totalChargedSafe, $totalPaidSafe);

        $documents = $lease
            ? $lease->documents()->latest()->get()
            : collect();

        return view('portal.dashboard', compact(
            'tenant', 'lease', 'unit', 'property', 'balance', 'documents'
        ));
    }
}