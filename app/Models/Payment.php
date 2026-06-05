<?php

namespace App\Models;

use App\Models\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory, BelongsToAccount;

    protected $fillable = [
        'account_id',
        'lease_id',
        'tenant_id',
        'amount',
        'payment_date',
        'method',
        'reference',
        'mpesa_transaction_id',
        'mpesa_phone',
        'is_allocated',
        'notes',
    ];

    protected $casts = [
        'payment_date'  => 'date',
        'amount'        => 'decimal:2',
        'is_allocated'  => 'boolean',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class);
    }
}