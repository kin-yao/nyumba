<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\Payment;
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

        // Counts used to warn before deletion
        $unitIds           = $units->pluck('id');
        $leaseIds          = Lease::whereIn('unit_id', $unitIds)->pluck('id');
        $activeTenantCount = Lease::whereIn('unit_id', $unitIds)->where('status', 'active')->count();
        $invoiceCount      = Invoice::whereIn('lease_id', $leaseIds)->count();
        $paymentCount      = Payment::whereIn('lease_id', $leaseIds)->count();
        $expenseCount      = Expense::where('property_id', $property->id)->count();

        return view('properties.show', compact(
            'property', 'units',
            'occupiedCount', 'vacantCount',
            'totalUnits', 'occupancyRate',
            'activeTenantCount', 'invoiceCount', 'paymentCount', 'expenseCount'
        ));
    }

    public function update(Request $request, Property $property)
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

        if ($validated['payment_type'] === 'paybill') {
            $validated['till_number'] = null;
        } elseif ($validated['payment_type'] === 'till') {
            $validated['business_number'] = null;
            $validated['account_format']  = null;
        }

        $property->update($validated);

        cache()->forget('props_list_' . auth()->user()->account_id);

        AuditService::log(
            'property.updated',
            'Property "' . $property->name . '" details updated',
            $property,
            ['type' => $property->type, 'county' => $property->county, 'payment_type' => $property->payment_type]
        );

        return redirect()->route('properties.show', $property)
            ->with('success', 'Property updated successfully.');
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

    public function destroy(Request $request, Property $property)
    {
        $request->validate([
            'confirmation' => ['required', 'string'],
        ]);

        if (trim($request->input('confirmation')) !== $property->name) {
            return back()->with('error', 'Property name did not match. Nothing was deleted.');
        }

        $name     = $property->name;
        $unitIds  = $property->units()->pluck('id');
        $leaseIds = Lease::whereIn('unit_id', $unitIds)->pluck('id');

        $counts = [
            'units'      => $unitIds->count(),
            'leases'     => $leaseIds->count(),
            'invoices'   => Invoice::whereIn('lease_id', $leaseIds)->count(),
            'payments'   => Payment::whereIn('lease_id', $leaseIds)->count(),
        ];

        AuditService::log(
            'property.deleted',
            'Property "' . $name . '" was deleted, along with ' . $counts['units'] . ' units, '
                . $counts['leases'] . ' leases, ' . $counts['invoices'] . ' invoices and '
                . $counts['payments'] . ' payment records',
            null,
            ['name' => $name, 'type' => $property->type] + $counts
        );

        $property->delete();

        cache()->forget('props_list_' . auth()->user()->account_id);

        return redirect()->route('properties.index')
            ->with('success', 'Property removed.');
    }
}