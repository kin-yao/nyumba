<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditService
{
    public static function log(
        string $event,
        string $description,
        mixed  $subject = null,
        array  $metadata = [],
        ?int   $propertyId = null
    ): void {
        try {
            // Auto-resolve property_id from subject if not explicitly provided
            if ($propertyId === null && $subject !== null) {
                $propertyId = static::resolvePropertyId($subject);
            }

            AuditLog::create([
                'account_id'   => auth()->user()?->account_id,
                'property_id'  => $propertyId,
                'user_id'      => auth()->id(),
                'event'        => $event,
                'description'  => $description,
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id'   => $subject?->id,
                'metadata'     => !empty($metadata) ? $metadata : null,
                'ip_address'   => request()->ip(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Audit log failed: ' . $e->getMessage());
        }
    }

    public static function system(
        int    $accountId,
        string $event,
        string $description,
        mixed  $subject = null,
        array  $metadata = [],
        ?int   $propertyId = null
    ): void {
        try {
            if ($propertyId === null && $subject !== null) {
                $propertyId = static::resolvePropertyId($subject);
            }

            AuditLog::create([
                'account_id'   => $accountId,
                'property_id'  => $propertyId,
                'user_id'      => null,
                'event'        => $event,
                'description'  => $description,
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id'   => $subject?->id,
                'metadata'     => !empty($metadata) ? $metadata : null,
                'ip_address'   => null,
            ]);
        } catch (\Exception $e) {
            \Log::error('Audit log failed: ' . $e->getMessage());
        }
    }

    /**
     * Attempt to resolve a property_id from the subject model.
     * Fails silently — audit logging must never break the app.
     */
    private static function resolvePropertyId(mixed $subject): ?int
    {
        try {
            return match(true) {

                $subject instanceof \App\Models\Property
                    => $subject->id,

                $subject instanceof \App\Models\Unit
                    => $subject->property_id,

                $subject instanceof \App\Models\Expense
                    => $subject->property_id,

                $subject instanceof \App\Models\MaintenanceRequest
                    => $subject->unit?->property_id
                        ?? \App\Models\Unit::find($subject->unit_id)?->property_id,

                $subject instanceof \App\Models\UtilityReading
                    => $subject->unit?->property_id
                        ?? \App\Models\Unit::find($subject->unit_id)?->property_id,

                $subject instanceof \App\Models\Invoice
                    => $subject->lease?->unit?->property_id
                        ?? \App\Models\Lease::with('unit')->find($subject->lease_id)?->unit?->property_id,

                $subject instanceof \App\Models\Payment
                    => $subject->lease?->unit?->property_id
                        ?? \App\Models\Lease::with('unit')->find($subject->lease_id)?->unit?->property_id,

                $subject instanceof \App\Models\Tenant
                    => $subject->activeLease?->unit?->property_id
                        ?? $subject->leases()->with('unit')->latest()->first()?->unit?->property_id,

                default => null,
            };
        } catch (\Exception $e) {
            return null;
        }
    }
}