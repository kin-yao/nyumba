<?php

namespace App\Models;

use App\Models\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasFactory, BelongsToAccount;

    protected $fillable = [
        'account_id',
        'unit_id',
        'tenant_id',
        'description',
        'priority',
        'status',
        'resolution_notes',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isTenantSubmitted(): bool
    {
        return $this->tenant_id !== null;
    }
}