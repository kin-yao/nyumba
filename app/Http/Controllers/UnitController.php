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
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'type'           => ['required', 'string', 'max:100'],
            'rent_amount'    => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['required', 'numeric', 'min:0'],
        ]);

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