<?php

namespace App\Models;

use App\Models\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Model;

class MoveOutRequest extends Model
{
    use BelongsToAccount;

    protected $fillable = [
        'account_id',
        'lease_id',
        'tenant_id',
        'unit_id',
        'requested_move_out_date',
        'reason',
        'status',
        'referral_name',
        'referral_phone',
        'referral_status',
        'landlord_notes',
        'read_at',
    ];

    protected $casts = [
        'requested_move_out_date' => 'date',
        'read_at'                 => 'datetime',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function hasReferral(): bool
    {
        return !empty($this->referral_name) && !empty($this->referral_phone);
    }
}