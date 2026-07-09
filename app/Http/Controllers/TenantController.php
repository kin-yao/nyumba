<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                if ($payment->payment_type === 'deposit') {
                    $ledger->push([
                        'date'        => $payment->payment_date,
                        'description' => 'Security deposit received'
                            . ($payment->reference ? ' - ' . $payment->reference : ''),
                        'charged'     => null,
                        'paid'        => null,
                        'type'        => 'deposit',
                        'invoice_ref' => null,
                    ]);
                    continue;
                }

                $ledger->push([
                    'date'        => $payment->payment_date,
                    'description' => 'Payment received'
                        . ($payment->reference ? ' - ' . $payment->reference : ''),
                    'charged'     => null,
                    'paid'        => $payment->amount,
                    'type'        => 'payment',
                    'invoice_ref' => null,
                ]);
            }
        }

        $ledger = $ledger->sortBy('date')->values();

        $totalCharged = floatval($activeLease?->invoices->sum('total_amount') ?? 0);

        $totalPaid = floatval(
            $activeLease?->payments
                ->where('payment_type', '!=', 'deposit')
                ->sum('amount') ?? 0
        );

        $balance = $totalCharged - $totalPaid;

        // Load vacant units in the same property for the transfer modal
        $vacantUnits = collect();
        if ($activeLease?->unit?->property_id) {
            $vacantUnits = Unit::where('property_id', $activeLease->unit->property_id)
                ->where('status', 'vacant')
                ->where('id', '!=', $activeLease->unit_id)
                ->orderBy('name')
                ->get();
        }

        return view('tenants.show', compact(
            'tenant', 'activeLease', 'ledger', 'balance', 'vacantUnits'
        ));
    }

    public function create()
    {
        $propertyIds = $this->filteredPropertyIds();
        $properties  = Property::whereIn('id', $propertyIds)
            ->with(['units' => fn($q) => $q->whereIn('status', ['vacant', 'reserved'])])
            ->get();

        // Surface any accepted referral bookings so the form can pre-fill
        // the new tenant's name/phone when moving them into a reserved unit.
        $reservedBookings = \App\Models\MoveOutRequest::with('unit')
            ->whereIn('unit_id', $properties->flatMap(fn($p) => $p->units->pluck('id')))
            ->where('referral_status', 'accepted')
            ->get()
            ->keyBy('unit_id');

        return view('tenants.create', compact('properties', 'reservedBookings'));
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

        // If this unit was being held for an accepted referral booking,
        // close out that request now that someone has moved in.
        \App\Models\MoveOutRequest::where('unit_id', $unit->id)
            ->where('referral_status', 'accepted')
            ->where('status', '!=', 'completed')
            ->update(['status' => 'completed']);

        AuditService::log(
            'tenant.moved_in',
            $tenant->full_name . ' moved into Unit ' . $unit->name,
            $tenant,
            ['rent' => $validated['monthly_rent'], 'unit' => $unit->name]
        );

        return redirect()->route('tenants.index')
            ->with('success', $tenant->first_name . ' ' . $tenant->last_name . ' has been moved in successfully.');
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'phone'      => ['required', 'string', 'max:20'],
            'alt_phone'  => ['nullable', 'string', 'max:20'],
            'id_number'  => ['nullable', 'string', 'max:50'],
            'email'      => ['nullable', 'email', 'max:255'],
        ]);

        $labels = [
            'first_name' => 'First name',
            'last_name'  => 'Last name',
            'phone'      => 'Phone',
            'alt_phone'  => 'Alt phone',
            'id_number'  => 'ID number',
            'email'      => 'Email',
        ];

        $changes = [];
        foreach ($validated as $field => $newValue) {
            $oldValue = $tenant->{$field};
            $newValue = $newValue === '' ? null : $newValue;

            if ((string) $oldValue !== (string) $newValue) {
                $changes[$labels[$field]] = [
                    'from' => $oldValue ?: '(empty)',
                    'to'   => $newValue ?: '(empty)',
                ];
            }
        }

        $tenant->update($validated);

        if (!empty($changes)) {
            $summary = collect($changes)
                ->map(fn($c, $field) => $field . ': "' . $c['from'] . '" → "' . $c['to'] . '"')
                ->implode('; ');

            AuditService::log(
                'tenant.updated',
                'Tenant details updated for ' . $tenant->full_name . ' — ' . $summary,
                $tenant,
                ['changes' => $changes]
            );
        }

        return redirect()->route('tenants.show', $tenant)
            ->with('success', 'Tenant details updated successfully.');
    }

    // ── Transfer tenant to another unit in the same property ─────────────
    public function transfer(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'new_unit_id'      => ['required', 'exists:units,id'],
            'transfer_date'    => ['required', 'date'],
            'new_monthly_rent' => ['required', 'numeric', 'min:0'],
            'deposit_action'   => ['required', 'in:carry_forward,keep,refund'],
            'new_deposit'      => ['nullable', 'numeric', 'min:0'],
            'notes'            => ['nullable', 'string'],
        ]);

        $activeLease = $tenant->leases()->where('status', 'active')->first();

        if (!$activeLease) {
            return back()->with('error', 'This tenant has no active lease to transfer.');
        }

        $oldUnit = $activeLease->unit;
        $newUnit = Unit::find($validated['new_unit_id']);

        // Verify new unit is in same property
        if ($newUnit->property_id !== $oldUnit->property_id) {
            return back()->with('error', 'Transfer is only allowed within the same property.');
        }

        // Verify new unit is vacant
        if ($newUnit->status !== 'vacant') {
            return back()->with('error', 'Unit ' . $newUnit->name . ' is not vacant. Cannot transfer.');
        }

        DB::transaction(function () use (
            $tenant, $activeLease, $oldUnit, $newUnit, $validated
        ) {
            $depositToCarry = 0;

            if ($validated['deposit_action'] === 'carry_forward') {
                $depositToCarry = floatval($activeLease->deposit_paid);
            } elseif ($validated['deposit_action'] === 'keep') {
                $depositToCarry = 0;
            } elseif ($validated['deposit_action'] === 'refund') {
                $depositToCarry = 0;
            }

            // Use custom new deposit if provided, otherwise use carried amount
            $newDepositPaid     = floatval($validated['new_deposit'] ?? $depositToCarry);
            $newDepositRequired = $newUnit->deposit_amount > 0
                ? $newUnit->deposit_amount
                : $newDepositPaid;

            // End the old lease
            $activeLease->update([
                'status'        => 'transferred',
                'move_out_date' => $validated['transfer_date'],
                'notes'         => trim(($activeLease->notes ?? '') . "\nTransferred to Unit " . $newUnit->name . ' on ' . $validated['transfer_date']
                    . ($validated['notes'] ? '. ' . $validated['notes'] : '')),
            ]);

            // Mark old unit vacant
            $oldUnit->update(['status' => 'vacant']);

            // Create new lease on the new unit
            $newLease = Lease::create([
                'unit_id'               => $newUnit->id,
                'tenant_id'             => $tenant->id,
                'move_in_date'          => $validated['transfer_date'],
                'lease_end_date'        => $activeLease->lease_end_date,
                'monthly_rent'          => $validated['new_monthly_rent'],
                'deposit_required'      => $newDepositRequired,
                'deposit_paid'          => $newDepositPaid,
                'escalation_percentage' => $activeLease->escalation_percentage,
                'status'                => 'active',
                'notes'                 => 'Transferred from Unit ' . $oldUnit->name
                    . ' on ' . $validated['transfer_date']
                    . ($validated['notes'] ? '. ' . $validated['notes'] : ''),
            ]);

            // If deposit is carried forward, create a deposit payment on the new lease
            if ($validated['deposit_action'] === 'carry_forward' && $depositToCarry > 0) {
                Payment::create([
                    'account_id'   => auth()->user()->account_id,
                    'tenant_id'    => $tenant->id,
                    'lease_id'     => $newLease->id,
                    'amount'       => $depositToCarry,
                    'payment_type' => 'deposit',
                    'payment_date' => $validated['transfer_date'],
                    'method'       => 'bank', // internal transfer, no actual payment
                    'reference'    => null,
                    'notes'        => 'Deposit carried forward from Unit ' . $oldUnit->name,
                    'is_allocated' => true,
                ]);
            }

            // Mark new unit occupied
            $newUnit->update(['status' => 'occupied']);
        });

        AuditService::log(
            'tenant.transferred',
            $tenant->full_name . ' transferred from Unit ' . $oldUnit->name . ' to Unit ' . $newUnit->name,
            $tenant,
            [
                'from_unit'      => $oldUnit->name,
                'to_unit'        => $newUnit->name,
                'transfer_date'  => $validated['transfer_date'],
                'new_rent'       => $validated['new_monthly_rent'],
                'deposit_action' => $validated['deposit_action'],
            ]
        );

        return redirect()->route('tenants.show', $tenant)
            ->with('success', $tenant->first_name . ' has been transferred from Unit ' . $oldUnit->name . ' to Unit ' . $newUnit->name . ' successfully.');
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

            // If a referral booking was accepted for this lease, hold the unit
            // for that referral instead of leaving it plain vacant.
            $acceptedBooking = \App\Models\MoveOutRequest::where('lease_id', $activeLease->id)
                ->where('referral_status', 'accepted')
                ->latest()
                ->first();

            $activeLease->unit->update([
                'status' => $acceptedBooking ? 'reserved' : 'vacant',
            ]);

            AuditService::log(
                'tenant.moved_out',
                $tenant->full_name . ' moved out of Unit ' . $unitName
                    . ($acceptedBooking ? ' — held as reserved for ' . $acceptedBooking->referral_name : ''),
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