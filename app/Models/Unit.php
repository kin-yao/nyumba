<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'name',
        'type',
        'rent_amount',
        'deposit_amount',
        'status',
    ];

    protected $casts = [
        'rent_amount'    => 'decimal:2',
        'deposit_amount' => 'decimal:2',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function leases()
    {
        return $this->hasMany(Lease::class);
    }

    public function activeLease()
    {
        return $this->hasOne(Lease::class)->where('status', 'active');
    }

    public function currentTenant()
    {
        return $this->activeLease?->tenant;
    }

    public function utilityReadings()
    {
        return $this->hasMany(UtilityReading::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function isVacant(): bool
    {
        return $this->status === 'vacant';
    }

    public function isReserved(): bool
    {
        return $this->status === 'reserved';
    }
}