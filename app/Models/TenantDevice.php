<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantDevice extends Model
{
    protected $fillable = [
        'tenant_id',
        'token_hash',
        'user_agent',
        'ip_address',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}