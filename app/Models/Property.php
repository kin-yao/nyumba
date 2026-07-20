<?php

namespace App\Models;

use App\Models\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory, BelongsToAccount;

    protected $fillable = [
        'account_id',
        'name',
        'type',
        'address',
        'county',
        'area',
        'caretaker_name',
        'caretaker_phone',
        'notes',
        'payment_type',
        'business_number',
        'till_number',
        'account_format',
        'mpesa_shortcode',
        'mpesa_consumer_key',
        'mpesa_consumer_secret',
        'mpesa_nominated_number',
        'mpesa_c2b_registered_at',
        'mpesa_pull_registered_at',
        'kcb_account_number',
        'kcb_ipn_registered_at',
        'auto_invoice_enabled',
        'invoice_send_day',
    ];

    protected $casts = [
        'mpesa_consumer_secret'    => 'encrypted',
        'mpesa_c2b_registered_at'  => 'datetime',
        'mpesa_pull_registered_at' => 'datetime',
        'kcb_ipn_registered_at'    => 'datetime',
        'auto_invoice_enabled'     => 'boolean',
        'invoice_send_day'         => 'integer',
    ];

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'property_user');
    }

    public function utilityRates()
    {
        return $this->hasMany(UtilityRate::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function occupiedUnitsCount(): int
    {
        return $this->units()->where('status', 'occupied')->count();
    }

    public function occupancyPercentage(): float
    {
        $total = $this->units()->count();
        if ($total === 0) return 0;
        return round(($this->occupiedUnitsCount() / $total) * 100, 1);
    }

    public function paymentConfig(): array
    {
        return [
            'payment_type'    => $this->payment_type,
            'business_number' => $this->business_number,
            'till_number'     => $this->till_number,
            'account_format'  => $this->account_format ?? 'unit_number',
        ];
    }

    public function hasPaymentConfig(): bool
    {
        return !empty($this->payment_type);
    }

    public function hasMpesaCredentials(): bool
    {
        return !empty($this->mpesa_shortcode)
            && !empty($this->mpesa_consumer_key)
            && !empty($this->mpesa_consumer_secret);
    }
}