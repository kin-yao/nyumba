<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'account_id',
        'firebase_uid',
        'auth_provider',
        'check_firebase_at',
        'name',
        'email',
        'phone',
        'email_verified_at',
        'phone_verified_at',
        'onboarding_complete',
        'role',
        'is_admin',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at'   => 'datetime',
        'phone_verified_at'   => 'datetime',
        'check_firebase_at'   => 'datetime',
        'onboarding_complete' => 'boolean',
        'is_admin'            => 'boolean',
        'password'            => 'hashed',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function isPhoneVerified(): bool
    {
        return $this->phone_verified_at !== null;
    }

    public function isOnboardingComplete(): bool
    {
        return $this->onboarding_complete;
    }

    // ── Role helpers ──────────────────────────────────────────────────────

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isCaretaker(): bool
    {
        return $this->role === 'caretaker';
    }

    public function canAccessFinancials(): bool
    {
        return in_array($this->role, ['owner', 'manager']);
    }

    public function canManageSettings(): bool
    {
        return $this->role === 'owner';
    }

    public function canManageTenants(): bool
    {
        return in_array($this->role, ['owner', 'manager']);
    }
}