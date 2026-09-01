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
        $normalized = self::normalize($billRef);

        if ($normalized === '') {
            return null;
        }

        return Unit::withoutGlobalScopes()
            ->where('property_id', $property->id)
            ->get()
            ->first(fn($unit) => self::normalize($unit->payment_reference ?: $unit->name) === $normalized);
    }

    /**
     * Matches a payment reference across EVERY property that has opted into
     * Pesalink central collection (bank_code = 'pesalink_central'), not
     * just one. This is the one case where the reference has to carry the
     * full weight of disambiguation — uniqueness across all of these units
     * is enforced at write time by findCollisionInCentralCollection(),
     * not here.
     */
    public static function matchCentralCollection(string $billRef): ?Unit
    {
        $normalized = self::normalize($billRef);

        if ($normalized === '') {
            return null;
        }

        return Unit::withoutGlobalScopes()
            ->whereHas('property', fn($q) => $q->withoutGlobalScopes()->where('bank_code', 'pesalink_central'))
            ->get()
            ->first(fn($unit) => self::normalize($unit->payment_reference ?: $unit->name) === $normalized);
    }

    /**
     * Finds another unit within the SAME property whose normalized
     * reference would collide with $billRef. For properties not on central
     * collection, the reference only needs to be unique within the
     * property (matching already only ever searches within one property
     * for these), so this is the right scope to validate against.
     */
    public static function findCollisionWithinProperty(Property $property, string $billRef, ?int $excludeUnitId = null): ?Unit
    {
        $normalized = self::normalize($billRef);

        if ($normalized === '') {
            return null;
        }

        $query = Unit::withoutGlobalScopes()->where('property_id', $property->id);

        if ($excludeUnitId) {
            $query->where('id', '!=', $excludeUnitId);
        }

        return $query->get()
            ->first(fn($unit) => self::normalize($unit->payment_reference ?: $unit->name) === $normalized);
    }

    /**
     * Finds another unit ANYWHERE on the platform, belonging to a property
     * already on central collection, whose normalized reference would
     * collide with $billRef. Used both when editing a reference on a unit
     * already in central collection, and when a property is about to opt
     * into it (excludePropertyId lets that property's own not-yet-saved
     * units be checked without colliding with themselves).
     */
    public static function findCollisionInCentralCollection(string $billRef, ?int $excludeUnitId = null, ?int $excludePropertyId = null): ?Unit
    {
        $normalized = self::normalize($billRef);

        if ($normalized === '') {
            return null;
        }

        $query = Unit::withoutGlobalScopes()
            ->whereHas('property', function ($q) use ($excludePropertyId) {
                $q->withoutGlobalScopes()->where('bank_code', 'pesalink_central');
                if ($excludePropertyId) {
                    $q->where('id', '!=', $excludePropertyId);
                }
            });

        if ($excludeUnitId) {
            $query->where('id', '!=', $excludeUnitId);
        }

        return $query->get()
            ->first(fn($unit) => self::normalize($unit->payment_reference ?: $unit->name) === $normalized);
    }

    private static function normalize(string $value): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $value));
    }
}