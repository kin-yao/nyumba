<?php

namespace App\Models;

use App\Models\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory, BelongsToAccount;

    protected $fillable = [
        'account_id',
        'lease_id',
        'reference',
        'period_month',
        'period_year',
        'invoice_date',
        'due_date',
        'total_amount',
        'amount_paid',
        'status',
        'sent_at',
        'reminder_3day_sent_at',
        'reminder_due_sent_at',
    ];

    protected $casts = [
        'invoice_date'          => 'date',
        'due_date'              => 'date',
        'sent_at'               => 'datetime',
        'total_amount'          => 'decimal:2',
        'amount_paid'           => 'decimal:2',
        'reminder_3day_sent_at' => 'datetime',
        'reminder_due_sent_at'  => 'datetime',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function lineItems()
    {
        return $this->hasMany(InvoiceLineItem::class);
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function getBalanceAttribute(): float
    {
        return floatval($this->total_amount) - floatval($this->amount_paid);
    }

    public function isOverdue(): bool
    {
        return $this->due_date->isPast() && $this->status !== 'paid';
    }
}