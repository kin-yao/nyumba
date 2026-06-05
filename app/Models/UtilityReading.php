<?php

namespace App\Models;

use App\Models\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtilityReading extends Model
{
    use HasFactory, BelongsToAccount;

    protected $fillable = [
        'unit_id',
        'account_id',
        'utility_type',
        'reading_month',
        'reading_year',
        'previous_reading',
        'current_reading',
        'units_consumed',
        'rate_per_unit',
        'charge_amount',
    ];

    protected $casts = [
        'previous_reading' => 'decimal:2',
        'current_reading'  => 'decimal:2',
        'units_consumed'   => 'decimal:2',
        'rate_per_unit'    => 'decimal:2',
        'charge_amount'    => 'decimal:2',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}