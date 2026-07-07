<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Services\AuditService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = Property::withCount('units')
            ->withCount(['units as occupied_units_count' => function ($query) {
                $query->where('status', 'occupied');
            }])
            ->latest()
            ->get();

        return view('properties.index', compact('properties'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'type'            => ['required', 'in:residential,commercial,mixed'],
            'address'         => ['nullable', 'string', 'max:255'],
            'county'          => ['nullable', 'string', 'max:100'],
            'area'            => ['nullable', 'string', 'max:100'],
            'caretaker_name'  => ['nullable', 'string', 'max:255'],
            'caretaker_phone' => ['nullable', 'string', 'max:20'],
            'notes'           => ['nullable', 'string'],
            'payment_type'    => ['required', 'in:paybill,till'],
            'business_number' => ['nullable', 'string', 'max:20'],
            'till_number'     => ['nullable', 'string', 'max:20'],
            'account_format'  => ['nullable', 'in:unit_number,tenant_name,phone_number'],
        ]);

        $validated['account_id'] = auth()->user()->account_id;

        if ($validated['payment_type'] === 'paybill') {
            $validated['till_number'] = null;
        } elseif ($validated['payment_type'] === 'till') {
            $validated['business_number'] = null;
            $validated['account_format']  = null;
        }

        $property = Property::create($validated);

        cache()->forget('props_list_' . auth()->user()->account_id);

        AuditService::log(
            'property.created',
            'Property "' . $property->name . '" added (' . ucfirst($property->type) . ', ' . ($property->county ?? 'no county') . ')',
            $property,
            [
                'type'         => $property->type,
                'county'       => $property->county,
                'payment_type' => $property->payment_type,
            ]
        );

        return redirect()->route('properties.index')
            ->with('success', 'Property added successfully.');
    }

    public function show(Property $property)
    {
        $property->load(['units.activeLease.tenant']);

        $units         = $property->units;
        $occupiedCount = $units->where('status', 'occupied')->count();
        $vacantCount   = $units->where('status', 'vacant')->count();
        $totalUnits    = $units->count();
        $occupancyRate = $totalUnits > 0
            ? round(($occupiedCount / $totalUnits) * 100)
            : 0;

        return view('properties.show', compact(
            'property', 'units',
            'occupiedCount', 'vacantCount',
            'totalUnits', 'occupancyRate'
        ));
    }

    public function updateInvoiceSchedule(Request $request, Property $property)
    {
        $validated = $request->validate([
            'auto_invoice_enabled' => ['nullable', 'boolean'],
            'invoice_send_day'     => ['required', 'integer', 'min:1', 'max:28'],
        ]);

        $enabled = $request->boolean('auto_invoice_enabled');

        $property->update([
            'auto_invoice_enabled' => $enabled,
            'invoice_send_day'     => $validated['invoice_send_day'],
        ]);

        AuditService::log(
            'property.invoice_schedule_updated',
            'Invoice schedule updated for "' . $property->name . '" — day: ' . $validated['invoice_send_day'] . ', auto: ' . ($enabled ? 'yes' : 'no'),
            $property,
            ['send_day' => $validated['invoice_send_day'], 'auto_enabled' => $enabled]
        );

        return redirect()->route('properties.show', $property)
            ->with('success', 'Invoice schedule saved.');
    }

    public function destroy(Property $property)
    {
        $name = $property->name;

        AuditService::log(
            'property.deleted',
            'Property "' . $name . '" was deleted',
            null,
            ['name' => $name, 'type' => $property->type]
        );

        $property->delete();

        cache()->forget('props_list_' . auth()->user()->account_id);

        return redirect()->route('properties.index')
            ->with('success', 'Property removed.');
    }
}