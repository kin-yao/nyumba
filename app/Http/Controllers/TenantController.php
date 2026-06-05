<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Services\AuditService;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index()
    {
        $unitIds = $this->filteredUnitIds();

        $tenants = Tenant::with(['leases' => function ($query) {
                $query->where('status', 'active')->with('unit.property');
            }])
            ->whereHas('leases', function ($q) use ($unitIds) {
                $q->whereIn('unit_id', $unitIds)->where('status', 'active');
            })
            ->latest()
            ->get();

        $archivedTenants = Tenant::onlyTrashed()
            ->with(['leases' => function ($query) {
                $query->latest()->with('unit.property');
            }])
            ->whereHas('leases', function ($q) use ($unitIds) {
                $q->whereIn('unit_id', $unitIds);
            })
            ->latest()
            ->get();

        return view('tenants.index', compact('tenants', 'archivedTenants'));
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['leases.unit.property']);
        $activeLease = $tenant->leases->where('status', 'active')->first();
        $ledger      = collect();

        if ($activeLease) {
            $activeLease->load(['invoices.lineItems', 'payments']);

            foreach ($activeLease->invoices as $invoice) {
                foreach ($invoice->lineItems as $item) {
                    $ledger->push([
                        'date'        => $invoice->invoice_date,
                        'description' => $item->description,
                        'charged'     => $item->amount,
                        'paid'        => null,
                        'type'        => 'charge',
                        'invoice_ref' => $invoice->reference,
                    ]);
                }
            }

            foreach ($activeLease->payments as $payment) {
                $ledger->push([
                    'date'        => $payment->payment_date,
                    'description' => 'Payment received' . ($payment->reference ? ' - ' . $payment->reference : ''),
                    'charged'     => null,
                    'paid'        => $payment->amount,
                    'type'        => 'payment',
                    'invoice_ref' => null,
                ]);
            }
        }

        $ledger       = $ledger->sortBy('date')->values();
        $totalCharged = $activeLease?->invoices->sum('total_amount') ?? 0;
        $totalPaid    = $activeLease?->payments->sum('amount') ?? 0;
        $balance      = $totalCharged - $totalPaid;

        return view('tenants.show', compact('tenant', 'activeLease', 'ledger', 'balance'));
    }

    public function create()
    {
        $propertyIds = $this->filteredPropertyIds();
        $properties  = Property::whereIn('id', $propertyIds)
            ->with(['units' => fn($q) => $q->where('status', 'vacant')])
            ->get();

        return view('tenants.create', compact('properties'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'            => ['required', 'string', 'max:255'],
            'last_name'             => ['required', 'string', 'max:255'],
            'phone'                 => ['required', 'string', 'max:20'],
            'alt_phone'             => ['nullable', 'string', 'max:20'],
            'id_number'             => ['nullable', 'string', 'max:50'],
            'email'                 => ['nullable', 'email', 'max:255'],
            'unit_id'               => ['required', 'exists:units,id'],
            'move_in_date'          => ['required', 'date'],
            'lease_end_date'        => ['nullable', 'date', 'after:move_in_date'],
            'monthly_rent'          => ['required', 'numeric', 'min:0'],
            'deposit_required'      => ['required', 'numeric', 'min:0'],
            'deposit_paid'          => ['required', 'numeric', 'min:0'],
            'deposit_method'        => ['required', 'in:mpesa,cash,bank,cheque'],
            'escalation_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes'                 => ['nullable', 'string'],
        ]);

        $tenant = Tenant::create([
            'account_id' => auth()->user()->account_id,
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'phone'      => $validated['phone'],
            'alt_phone'  => $validated['alt_phone'] ?? null,
            'id_number'  => $validated['id_number'] ?? null,
            'email'      => $validated['email'] ?? null,
        ]);

        $unit = Unit::find($validated['unit_id']);

        Lease::create([
            'unit_id'               => $validated['unit_id'],
            'tenant_id'             => $tenant->id,
            'move_in_date'          => $validated['move_in_date'],
            'lease_end_date'        => $validated['lease_end_date'] ?? null,
            'monthly_rent'          => $validated['monthly_rent'],
            'deposit_required'      => $validated['deposit_required'],
            'deposit_paid'          => $validated['deposit_paid'],
            'escalation_percentage' => $validated['escalation_percentage'] ?? null,
            'status'                => 'active',
            'notes'                 => $validated['notes'] ?? null,
        ]);

        $unit->update(['status' => 'occupied']);

        AuditService::log(
            'tenant.moved_in',
            $tenant->full_name . ' moved into Unit ' . $unit->name,
            $tenant,
            ['rent' => $validated['monthly_rent'], 'unit' => $unit->name]
        );

        return redirect()->route('tenants.index')
            ->with('success', $tenant->first_name . ' ' . $tenant->last_name . ' has been moved in successfully.');
    }

    public function moveOut(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'move_out_date'  => ['required', 'date'],
            'final_charges'  => ['nullable', 'numeric', 'min:0'],
            'deposit_action' => ['required', 'in:apply_and_refund,full_refund,forfeit,partial_refund'],
            'notes'          => ['nullable', 'string'],
        ]);

        $activeLease = $tenant->leases()->where('status', 'active')->first();

        if ($activeLease) {
            $unitName = $activeLease->unit->name;

            $activeLease->update([
                'move_out_date' => $validated['move_out_date'],
                'status'        => 'ended',
                'notes'         => $validated['notes'] ?? $activeLease->notes,
            ]);

            $activeLease->unit->update(['status' => 'vacant']);

            AuditService::log(
                'tenant.moved_out',
                $tenant->full_name . ' moved out of Unit ' . $unitName,
                $tenant,
                ['move_out_date' => $validated['move_out_date'], 'deposit_action' => $validated['deposit_action']]
            );
        }

        return redirect()->route('tenants.index')
            ->with('success', $tenant->first_name . ' has been moved out successfully.');
    }

    public function destroy(Tenant $tenant)
    {
        if ($tenant->leases()->where('status', 'active')->exists()) {
            return redirect()->route('tenants.index')
                ->with('error', 'Cannot archive a tenant with an active lease. Move them out first.');
        }

        AuditService::log(
            'tenant.archived',
            $tenant->full_name . ' was archived',
            $tenant
        );

        $tenant->delete();

        return redirect()->route('tenants.index')
            ->with('success', $tenant->first_name . ' ' . $tenant->last_name . ' has been archived.');
    }

    public function restore(int $id)
    {
        $tenant = Tenant::onlyTrashed()
            ->where('account_id', auth()->user()->account_id)
            ->findOrFail($id);

        $tenant->restore();

        AuditService::log(
            'tenant.restored',
            $tenant->full_name . ' was restored from archive',
            $tenant
        );

        return redirect()->route('tenants.index')
            ->with('success', $tenant->first_name . ' ' . $tenant->last_name . ' has been restored.');
    }
}