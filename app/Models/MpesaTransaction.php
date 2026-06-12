<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MpesaTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'type',
        'plan',
        'billing_cycle',
        'amount',
        'phone',
        'checkout_request_id',
        'merchant_request_id',
        'mpesa_receipt',
        'status',
        'result_code',
        'result_desc',
        'completed_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}