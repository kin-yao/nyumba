<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtilityRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'name',
        'type',
        'amount',
        'billing_type',
        'active',
        'auto_bill',
    ];

    protected $casts = [
        'amount'    => 'decimal:2',
        'active'    => 'boolean',
        'auto_bill' => 'boolean',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}