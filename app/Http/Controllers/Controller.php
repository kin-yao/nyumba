<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\Property;
use App\Models\Unit;

abstract class Controller
{
    // Request-level cache — avoids re-running the same queries
    // when multiple methods are called in the same request
    private ?array $cachedPropertyIds = null;
    private ?array $cachedUnitIds     = null;
    private ?array $cachedLeaseIds    = null;

    protected function filteredProperty(): ?Property
    {
        $propertyId = session('filter_property_id');
        if (!$propertyId) return null;

        return Property::where('id', $propertyId)
            ->where('account_id', auth()->user()->account_id)
            ->first();
    }

    protected function filteredPropertyIds(): array
    {
        if ($this->cachedPropertyIds !== null) {
            return $this->cachedPropertyIds;
        }

        $propertyId = session('filter_property_id');

        $this->cachedPropertyIds = $propertyId
            ? [$propertyId]
            : Property::where('account_id', auth()->user()->account_id)
                ->pluck('id')
                ->toArray();

        return $this->cachedPropertyIds;
    }

    protected function filteredUnitIds(): array
    {
        if ($this->cachedUnitIds !== null) {
            return $this->cachedUnitIds;
        }

        $this->cachedUnitIds = Unit::whereIn('property_id', $this->filteredPropertyIds())
            ->pluck('id')
            ->toArray();

        return $this->cachedUnitIds;
    }

    protected function filteredLeaseIds(bool $activeOnly = false): array
    {
        // Active-only leases can't be cached the same way so run fresh
        if ($activeOnly) {
            return Lease::whereIn('unit_id', $this->filteredUnitIds())
                ->where('status', 'active')
                ->pluck('id')
                ->toArray();
        }

        if ($this->cachedLeaseIds !== null) {
            return $this->cachedLeaseIds;
        }

        $this->cachedLeaseIds = Lease::whereIn('unit_id', $this->filteredUnitIds())
            ->pluck('id')
            ->toArray();

        return $this->cachedLeaseIds;
    }
}