<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Unit;
use App\Services\AuditService;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function store(Request $request, Property $property)
    {
        abort_unless(in_array($property->id, $this->filteredPropertyIds()), 403);

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'type'           => ['required', 'string', 'max:100'],
            'rent_amount'    => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['required', 'numeric', 'min:0'],
        ]);

        // Unit limit check
        $account      = auth()->user()->account;
        $currentCount = Unit::whereIn('property_id',
            Property::where('account_id', $account->id)->pluck('id')
        )->count();

        if ($currentCount >= $account->unit_limit) {
            return redirect()->back()
                ->with('error',
                    'You have reached your unit limit of ' . $account->unit_limit . ' units. '
                    . 'Upgrade your plan to add more units. '
                    . 'Contact us on WhatsApp: +254705056343'
                );
        }

        $unit = $property->units()->create($validated);

        AuditService::log(
            'unit.added',
            'Unit ' . $unit->name . ' added to ' . $property->name . ' (' . $unit->type . ')',
            $unit,
            [
                'rent'    => $validated['rent_amount'],
                'deposit' => $validated['deposit_amount'],
                'type'    => $unit->type,
            ]
        );

        return redirect()->route('properties.show', $property)
            ->with('success', 'Unit added successfully.');
    }

    /**
     * Sets a unit's payment_reference — the landlord's own code for bank
     * integrations (e.g. Pesalink) that need a reference beyond the plain
     * unit number. Left blank, matching falls back to the unit's name.
     * Deliberately scoped to this one field — there's no general unit
     * edit yet.
     */
    public function updateReference(Request $request, Unit $unit)
    {
        abort_unless(in_array($unit->property_id, $this->filteredPropertyIds()), 403);

        $validated = $request->validate([
            'payment_reference' => ['nullable', 'string', 'max:100'],
        ]);

        $newReference = $validated['payment_reference'] ?: null;

        if ($newReference) {
            $collision = $unit->property->bank_code === 'pesalink_central'
                ? \App\Services\UnitMatcher::findCollisionInCentralCollection($newReference, excludeUnitId: $unit->id)
                : \App\Services\UnitMatcher::findCollisionWithinProperty($unit->property, $newReference, excludeUnitId: $unit->id);

            if ($collision) {
                return redirect()->back()->with('error',
                    'That payment reference is already used by unit "' . $collision->name . '"'
                    . ($collision->property_id !== $unit->property_id ? ' at "' . $collision->property->name . '"' : '')
                    . '. Choose a different one.'
                );
            }
        }

        $unit->update(['payment_reference' => $newReference]);

        AuditService::log(
            'unit.payment_reference_updated',
            'Payment reference for unit ' . $unit->name . ' set to "' . ($unit->payment_reference ?? $unit->name) . '"',
            $unit
        );

        return redirect()->back()->with('success', 'Payment reference updated.');
    }
}