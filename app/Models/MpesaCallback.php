<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MpesaCallback extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'transaction_id',
        'phone',
        'amount',
        'account_reference',
        'result_code',
        'result_desc',
        'payload',
        'processed',
        'transaction_date',
    ];

    protected $casts = [
        'payload'          => 'array',
        'processed'        => 'boolean',
        'amount'           => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}