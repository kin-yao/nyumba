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
}