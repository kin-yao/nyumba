<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Models\MoveOutRequest;
use App\Models\Notification;
use App\Models\Tenant;
use Illuminate\Http\Request;

class CommunicationController extends Controller
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

        $maintenanceRequests = $unit
            ? MaintenanceRequest::where('unit_id', $unit->id)
                ->where('tenant_id', $tenant->id)
                ->latest()
                ->get()
            : collect();

        $moveOutRequests = MoveOutRequest::where('tenant_id', $tenant->id)
            ->latest()
            ->get();

        $hasPendingMoveOut = $moveOutRequests->whereIn('status', ['pending', 'acknowledged'])->isNotEmpty();

        return view('portal.communications', compact(
            'tenant', 'lease', 'unit', 'maintenanceRequests', 'moveOutRequests', 'hasPendingMoveOut'
        ));
    }

    public function storeMaintenance(Request $request)
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:2000'],
            'priority'    => ['required', 'in:urgent,normal,low'],
        ]);

        $tenant = $this->tenant();
        $lease  = $tenant->activeLease;

        if (!$lease) {
            return back()->with('error', 'No active tenancy found.');
        }

        $unit = $lease->unit;

        $maintenance = MaintenanceRequest::create([
            'account_id'  => $unit->property->account_id,
            'unit_id'     => $unit->id,
            'tenant_id'   => $tenant->id,
            'description' => $validated['description'],
            'priority'    => $validated['priority'],
            'status'      => 'open',
        ]);

        Notification::create([
            'account_id' => $unit->property->account_id,
            'type'       => 'maintenance_request_tenant',
            'title'      => 'New maintenance request from ' . $tenant->full_name,
            'body'       => 'Unit ' . $unit->name . ' (' . $unit->property->name . '): ' . $validated['description'],
            'data'       => ['unit' => $unit->name, 'priority' => $validated['priority']],
        ]);

        return back()->with('success', 'Your maintenance request has been submitted.');
    }

    public function storeMoveOut(Request $request)
    {
        $validated = $request->validate([
            'requested_move_out_date' => ['required', 'date', 'after_or_equal:today'],
            'reason'                  => ['nullable', 'string', 'max:2000'],
            'referral_name'           => ['nullable', 'string', 'max:255', 'required_with:referral_phone'],
            'referral_phone'          => ['nullable', 'string', 'max:20', 'required_with:referral_name'],
        ]);

        $tenant = $this->tenant();
        $lease  = $tenant->activeLease;

        if (!$lease) {
            return back()->with('error', 'No active tenancy found.');
        }

        $unit        = $lease->unit;
        $hasReferral = !empty($validated['referral_name']) && !empty($validated['referral_phone']);

        $moveOut = MoveOutRequest::create([
            'account_id'               => $unit->property->account_id,
            'lease_id'                 => $lease->id,
            'tenant_id'                => $tenant->id,
            'unit_id'                  => $unit->id,
            'requested_move_out_date'  => $validated['requested_move_out_date'],
            'reason'                   => $validated['reason'] ?? null,
            'status'                   => 'pending',
            'referral_name'            => $validated['referral_name'] ?? null,
            'referral_phone'           => $validated['referral_phone'] ?? null,
            'referral_status'          => $hasReferral ? 'pending' : 'none',
        ]);

        Notification::create([
            'account_id' => $unit->property->account_id,
            'type'       => 'move_out_request',
            'title'      => 'Move-out request from ' . $tenant->full_name,
            'body'       => 'Unit ' . $unit->name . ' (' . $unit->property->name . ') — requested move-out date: '
                . $moveOut->requested_move_out_date->format('d M Y')
                . ($hasReferral ? '. Includes a referral booking for ' . $validated['referral_name'] . '.' : '.'),
            'data'       => ['unit' => $unit->name, 'move_out_request_id' => $moveOut->id],
        ]);

        return back()->with('success', 'Your move-out request has been submitted. Your landlord will be notified.');
    }
}