<?php

namespace App\Services;

use App\Models\Property;
use App\Models\Unit;

/**
 * Matches a payment reference to a unit within a property. Shared by every
 * bank integration (KCB, IPSL, and whichever comes next), so the matching
 * rule only lives in one place.
 */
class UnitMatcher
{
    /**
     * Simple exact match — used by KCB, M-Pesa. Compares the raw reference
     * directly against the unit's name, case-insensitive. See build spec
     * §5.4.
     */
    public static function match(Property $property, string $billRef): ?Unit
    {
        $billRef = trim($billRef);

        if ($billRef === '') {
            return null;
        }

        return Unit::withoutGlobalScopes()
            ->where('property_id', $property->id)
            ->whereRaw('LOWER(name) = ?', [strtolower($billRef)])
            ->first();
    }

    /**
     * Normalized match — used by IPSL (Pesalink), where a tenant might
     * type "KLM-A1", "KLM#A1", "KLM A1", or "klma1" and all four must
     * match the same unit. Strips everything but letters and digits, then
     * compares uppercase, on both sides. Checks the unit's own
     * payment_reference first (the landlord's custom code), falling back
     * to the unit's name if no custom reference is set.
     */
    public static function matchNormalized(Property $property, string $billRef): ?Unit
    {
        $normalized = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $billRef));

        if ($normalized === '') {
            return null;
        }

        return Unit::withoutGlobalScopes()
            ->where('property_id', $property->id)
            ->get()
            ->first(function ($unit) use ($normalized) {
                $reference = $unit->payment_reference ?: $unit->name;
                return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $reference)) === $normalized;
            });
    }
}