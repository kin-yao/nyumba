<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lease extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'tenant_id',
        'move_in_date',
        'move_out_date',
        'lease_end_date',
        'monthly_rent',
        'deposit_required',
        'deposit_paid',
        'escalation_percentage',
        'next_review_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'move_in_date'           => 'date',
        'move_out_date'          => 'date',
        'lease_end_date'         => 'date',
        'next_review_date'       => 'date',
        'monthly_rent'           => 'decimal:2',
        'deposit_required'       => 'decimal:2',
        'deposit_paid'           => 'decimal:2',
        'escalation_percentage'  => 'decimal:2',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function proofOfPayments()
    {
        return $this->hasMany(ProofOfPayment::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}