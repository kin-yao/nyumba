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

        $lease?->load(['payments']);

        $totalCharged = floatval($lease?->invoices()->sum('total_amount') ?? 0);
        $totalPaid    = floatval(
            $lease?->payments->where('payment_type', '!=', 'deposit')->sum('amount') ?? 0
        );
        $balance = $totalCharged - $totalPaid;

        $documents = $lease
            ? $lease->documents()->latest()->get()
            : collect();

        return view('portal.dashboard', compact(
            'tenant', 'lease', 'unit', 'property', 'balance', 'documents'
        ));
    }
}