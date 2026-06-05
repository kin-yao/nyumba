<?php

namespace App\Models;

use App\Models\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory, BelongsToAccount;

    protected $fillable = [
        'account_id',
        'property_id',
        'category',
        'description',
        'amount',
        'vendor',
        'payment_method',
        'reference',
        'receipt_path',
        'expense_date',
        'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}