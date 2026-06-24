<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Property;
use App\Models\Unit;
use App\Models\UtilityReading;
use App\Models\UtilityRate;
use App\Services\AuditService;
use Illuminate\Http\Request;

class UtilityController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        $lastMonth = $month === 1 ? 12 : $month - 1;
        $lastYear  = $month === 1 ? $year - 1 : $year;

        $propertyIds = $this->filteredPropertyIds();

        $properties = Property::whereIn('id', $propertyIds)
            ->with([
                'units.activeLease.tenant',
                'utilityRates' => fn($q) => $q->where('active', true)->orderBy('type')
            ])->get();

        $unitIds = Unit::whereIn('property_id', $propertyIds)->pluck('id')->toArray();

        $readings = UtilityReading::with('unit')
            ->whereIn('unit_id', $unitIds)
            ->where('reading_month', $month)
            ->where('reading_year', $year)
            ->get()
            ->groupBy(fn($r) => $r->unit_id . '_' . $r->utility_type);

        $lastReadings = UtilityReading::whereIn('unit_id', $unitIds)
            ->where('reading_month', $lastMonth)
            ->where('reading_year', $lastYear)
            ->get()
            ->groupBy(fn($r) => $r->unit_id . '_' . $r->utility_type);

        return view('utilities.index', compact(
            'properties', 'readings', 'lastReadings', 'month', 'year'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id'          => ['required', 'exists:units,id'],
            'utility_type'     => ['required', 'string'],
            'reading_month'    => ['required', 'integer', 'min:1', 'max:12'],
            'reading_year'     => ['required', 'integer'],
            'previous_reading' => ['required', 'numeric', 'min:0'],
            'current_reading'  => ['required', 'numeric', 'min:0', 'gte:previous_reading'],
        ]);

        $unit = Unit::with('property.utilityRates')->find($validated['unit_id']);

        $configuredRate = $unit->property->utilityRates
            ->where('type', $validated['utility_type'])
            ->where('active', true)
            ->first();

        $ratePerUnit   = $configuredRate ? floatval($configuredRate->amount) : 0;
        $unitsConsumed = $validated['current_reading'] - $validated['previous_reading'];
        $chargeAmount  = $unitsConsumed * $ratePerUnit;

        $reading = UtilityReading::updateOrCreate(
            [
                'unit_id'       => $validated['unit_id'],
                'utility_type'  => $validated['utility_type'],
                'reading_month' => $validated['reading_month'],
                'reading_year'  => $validated['reading_year'],
                'account_id'    => auth()->user()->account_id,
            ],
            [
                'previous_reading' => $validated['previous_reading'],
                'current_reading'  => $validated['current_reading'],
                'units_consumed'   => $unitsConsumed,
                'rate_per_unit'    => $ratePerUnit,
                'charge_amount'    => $chargeAmount,
            ]
        );

        $period = \Carbon\Carbon::createFromDate(
            $validated['reading_year'], $validated['reading_month'], 1
        )->format('M Y');

        AuditService::log(
            'utility.reading_entered',
            ucfirst($validated['utility_type']) . ' reading entered for Unit ' . $unit->name
                . ' — ' . $unitsConsumed . ' units @ KES ' . $ratePerUnit . ' = ' . currency($chargeAmount) . ' (' . $period . ')',
            $reading,
            [
                'unit'           => $unit->name,
                'utility_type'   => $validated['utility_type'],
                'units_consumed' => $unitsConsumed,
                'rate_per_unit'  => $ratePerUnit,
                'charge_amount'  => $chargeAmount,
                'period'         => $period,
            ]
        );

        return redirect()->route('utilities.index', [
            'month' => $validated['reading_month'],
            'year'  => $validated['reading_year'],
        ])->with('success', 'Reading saved successfully.');
    }

    public function rates()
    {
        $propertyIds = $this->filteredPropertyIds();

        $properties = Property::whereIn('id', $propertyIds)
            ->with('utilityRates')
            ->get();

        return view('utilities.rates', compact('properties'));
    }

    public function storeRate(Request $request)
    {
        $validated = $request->validate([
            'property_id'  => ['required', 'exists:properties,id'],
            'name'         => ['required', 'string', 'max:100'],
            'type'         => ['required', 'string', 'max:50'],
            'amount'       => ['required', 'numeric', 'min:0'],
            'billing_type' => ['required', 'in:per_unit,flat_fee,per_meter_reading'],
            'auto_bill'    => ['nullable', 'boolean'],
        ]);

        $rate = UtilityRate::create([
            'property_id'  => $validated['property_id'],
            'name'         => $validated['name'],
            'type'         => $validated['type'],
            'amount'       => $validated['amount'],
            'billing_type' => $validated['billing_type'],
            'auto_bill'    => $request->boolean('auto_bill'),
            'active'       => true,
        ]);

        $property = Property::find($validated['property_id']);

        AuditService::log(
            'utility.rate_added',
            'Utility rate "' . $rate->name . '" added for ' . $property->name
                . ' — ' . currency($rate->amount) . ' (' . str_replace('_', ' ', $rate->billing_type) . ')',
            $rate,
            [
                'property'     => $property->name,
                'billing_type' => $rate->billing_type,
                'amount'       => $rate->amount,
            ],
            $property->id
        );

        return redirect()->route('utilities.rates')
            ->with('success', 'Rate configured successfully.');
    }

    public function destroyRate(UtilityRate $utilityRate)
    {
        $propertyId = $utilityRate->property_id;
        $name       = $utilityRate->name;

        AuditService::log(
            'utility.rate_removed',
            'Utility rate "' . $name . '" removed',
            null,
            ['name' => $name],
            $propertyId
        );

        $utilityRate->delete();

        return redirect()->route('utilities.rates')
            ->with('success', 'Rate removed.');
    }

    public function chargesForLease(Request $request)
    {
        $leaseId = $request->input('lease_id');
        $month   = $request->input('month', now()->month);
        $year    = $request->input('year', now()->year);

        $lease = Lease::with('unit.property.utilityRates')->find($leaseId);
        if (!$lease) return response()->json([]);

        $unit = $lease->unit;

        $readings = UtilityReading::where('unit_id', $unit->id)
            ->where('reading_month', $month)
            ->where('reading_year', $year)
            ->get();

        $charges = [];

        foreach ($readings as $reading) {
            $rate = $unit->property->utilityRates
                ->where('type', $reading->utility_type)
                ->first();

            $charges[] = [
                'description' => ($rate ? $rate->name : ucfirst($reading->utility_type)) . ' charges',
                'amount'      => floatval($reading->charge_amount),
                'type'        => $reading->utility_type,
            ];
        }

        foreach ($unit->property->utilityRates->where('billing_type', 'flat_fee')->where('active', true) as $rate) {
            $charges[] = [
                'description' => $rate->name,
                'amount'      => floatval($rate->amount),
                'type'        => $rate->type,
            ];
        }

        return response()->json($charges);
    }
}