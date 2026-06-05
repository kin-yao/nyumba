<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'account_id',
        'property_id',
        'user_id',
        'event',
        'description',
        'subject_type',
        'subject_id',
        'metadata',
        'ip_address',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public function actorName(): string
    {
        return $this->user?->name ?? 'System';
    }

    public function eventIcon(): string
    {
        if (str_starts_with($this->event, 'tenant.'))      return 'person';
        if (str_starts_with($this->event, 'invoice.'))     return 'receipt';
        if (str_starts_with($this->event, 'payment.'))     return 'payment';
        if (str_starts_with($this->event, 'expense.'))     return 'expense';
        if (str_starts_with($this->event, 'maintenance.')) return 'wrench';
        if (str_starts_with($this->event, 'utility.'))     return 'utility';
        if (str_starts_with($this->event, 'sms.'))         return 'sms';
        if (str_starts_with($this->event, 'settings.'))    return 'settings';
        if (str_starts_with($this->event, 'user.'))        return 'user';
        if (str_starts_with($this->event, 'property.'))    return 'property';
        if (str_starts_with($this->event, 'unit.'))        return 'unit';
        return 'log';
    }

    public function eventColor(): string
    {
        if (str_starts_with($this->event, 'payment.'))     return '#15803d';
        if (str_starts_with($this->event, 'invoice.'))     return '#1a6b52';
        if (str_starts_with($this->event, 'tenant.'))      return '#1d4ed8';
        if (str_starts_with($this->event, 'expense.'))     return '#b91c1c';
        if (str_starts_with($this->event, 'maintenance.')) return '#d97706';
        if (str_starts_with($this->event, 'sms.'))         return '#7c3aed';
        if (str_starts_with($this->event, 'property.'))    return '#0e7490';
        if (str_starts_with($this->event, 'unit.'))        return '#0e7490';
        if (str_starts_with($this->event, 'settings.'))    return '#4b5563';
        if (str_starts_with($this->event, 'user.'))        return '#7c3aed';
        return '#8a8880';
    }
}