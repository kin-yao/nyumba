<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Models\Notification;
use App\Models\Tenant;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
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

        return view('portal.maintenance', compact('tenant', 'lease', 'unit', 'maintenanceRequests'));
    }

    public function store(Request $request)
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
}