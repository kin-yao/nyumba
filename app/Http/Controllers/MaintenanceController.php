<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Unit;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MaintenanceController extends Controller
{
    public function index()
    {
        $unitIds = $this->filteredUnitIds();

        $requests = MaintenanceRequest::with('unit.property')
            ->whereIn('unit_id', $unitIds)
            ->latest()
            ->get();

        $openCount       = $requests->where('status', 'open')->count();
        $inProgressCount = $requests->where('status', 'in_progress')->count();
        $resolvedCount   = $requests->where('status', 'resolved')->count();
        $urgentCount     = $requests->where('priority', 'urgent')
                                    ->whereIn('status', ['open', 'in_progress'])
                                    ->count();

        $properties = Property::whereIn('id', $this->filteredPropertyIds())
            ->with('units')
            ->get();

        return view('maintenance.index', compact(
            'requests', 'openCount', 'inProgressCount',
            'resolvedCount', 'urgentCount', 'properties'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id'     => ['required', 'exists:units,id'],
            'description' => ['required', 'string'],
            'priority'    => ['required', 'in:urgent,normal,low'],
            'notes'       => ['nullable', 'string'],
        ]);

        $accountId = auth()->user()->account_id;

        // Server-side duplicate prevention — block identical submission within 10 seconds
        $recent = MaintenanceRequest::where('account_id', $accountId)
            ->where('unit_id', $validated['unit_id'])
            ->where('description', $validated['description'])
            ->where('created_at', '>=', now()->subSeconds(10))
            ->first();

        if ($recent) {
            return redirect()->route('maintenance.index')
                ->with('success', 'Maintenance request logged.');
        }

        $maintenance = MaintenanceRequest::create([
            'account_id'  => $accountId,
            'unit_id'     => $validated['unit_id'],
            'description' => $validated['description'],
            'priority'    => $validated['priority'],
            'notes'       => $validated['notes'] ?? null,
            'status'      => 'open',
        ]);

        $unit = Unit::find($validated['unit_id']);

        try {
            AuditService::log(
                'maintenance.created',
                'Maintenance request logged for Unit ' . $unit->name . ' — ' . Str::limit($validated['description'], 60),
                $maintenance,
                ['priority' => $validated['priority'], 'unit' => $unit->name]
            );
        } catch (\Exception $e) {
            \Log::warning('Audit log failed: ' . $e->getMessage());
        }

        return redirect()->route('maintenance.index')
            ->with('success', 'Maintenance request logged.');
    }

    public function update(Request $request, MaintenanceRequest $maintenance)
    {
        $validated = $request->validate([
            'status'           => ['required', 'in:open,in_progress,resolved'],
            'resolution_notes' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'resolved') {
            $validated['resolved_at'] = now();
        }

        $maintenance->update($validated);

        try {
            AuditService::log(
                'maintenance.updated',
                'Maintenance request marked as ' . $validated['status'] . ' for Unit ' . $maintenance->unit->name,
                $maintenance,
                ['status' => $validated['status']]
            );
        } catch (\Exception $e) {
            \Log::warning('Audit log failed: ' . $e->getMessage());
        }

        return redirect()->route('maintenance.index')
            ->with('success', 'Request updated.');
    }

    public function destroy(MaintenanceRequest $maintenance)
    {
        try {
            AuditService::log(
                'maintenance.deleted',
                'Maintenance request deleted for Unit ' . $maintenance->unit->name,
                null,
                ['description' => Str::limit($maintenance->description, 60)]
            );
        } catch (\Exception $e) {
            \Log::warning('Audit log failed: ' . $e->getMessage());
        }

        $maintenance->delete();

        return redirect()->route('maintenance.index')
            ->with('success', 'Request deleted.');
    }
}
