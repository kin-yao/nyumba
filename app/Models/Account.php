<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'county',
        'logo_path',
        'currency',
        'plan',
        'billing_cycle',
        'unit_limit',
        'plan_expires_at',
        'grace_period_ends_at',
        'trial_ends_at',
        'subscribed_at',
        'sms_credits',
        'sms_credits_monthly',
        'notes',
        'use_case',
        'unit_count_range',
        'recommended_plan',
        'firebase_uid',
        'auth_provider',
    ];

    protected $casts = [
        'plan_expires_at'        => 'datetime',
        'grace_period_ends_at'   => 'datetime',
        'trial_ends_at'          => 'datetime',
        'subscribed_at'          => 'datetime',
        'sms_credits'            => 'integer',
        'sms_credits_monthly'    => 'integer',
        'unit_limit'             => 'integer',
    ];

    const PLANS = [
        'explore' => [
            'name'                => 'Explore',
            'unit_limit'          => 3,
            'sms_credits_monthly' => 10,
            'price_monthly'       => 0,
            'price_yearly'        => 0,
        ],
        'starter' => [
            'name'                => 'Starter',
            'unit_limit'          => 20,
            'sms_credits_monthly' => 80,
            'price_monthly'       => 2300,
            'price_yearly'        => 19300,
        ],
        'growth' => [
            'name'                => 'Growth',
            'unit_limit'          => 50,
            'sms_credits_monthly' => 200,
            'price_monthly'       => 4600,
            'price_yearly'        => 38600,
        ],
        'pro' => [
            'name'                => 'Pro',
            'unit_limit'          => 100,
            'sms_credits_monthly' => 400,
            'price_monthly'       => 7500,
            'price_yearly'        => 63000,
        ],
        'enterprise' => [
            'name'                => 'Enterprise',
            'unit_limit'          => 999999,
            'sms_credits_monthly' => 500,
            'price_monthly'       => 0,
            'price_yearly'        => 0,
        ],
    ];

    // ─── Status checks ────────────────────────────────────────────────────

    public function isOnTrial(): bool
    {
        if ($this->plan !== 'explore') return false;
        // No trial end date set — treat as active trial (new account)
        if (!$this->trial_ends_at) return true;
        return $this->trial_ends_at->isFuture();
    }

    public function isTrialExpired(): bool
    {
        return $this->plan === 'explore'
            && $this->trial_ends_at
            && $this->trial_ends_at->isPast();
    }

    public function isActive(): bool
    {
        if ($this->plan === 'explore') {
            return $this->isOnTrial();
        }
        return $this->plan_expires_at && $this->plan_expires_at->isFuture();
    }

    public function isInGracePeriod(): bool
    {
        return !$this->isActive()
            && $this->grace_period_ends_at
            && $this->grace_period_ends_at->isFuture();
    }

    public function isExpired(): bool
    {
        return !$this->isActive() && !$this->isInGracePeriod();
    }

    public function trialDaysRemaining(): int
    {
        if (!$this->isOnTrial()) return 0;
        // No trial_ends_at means unlimited trial — show 30 as default
        if (!$this->trial_ends_at) return 30;
        return max(0, (int) now()->diffInDays($this->trial_ends_at));
    }

    public function subscriptionDaysRemaining(): int
    {
        if (!$this->plan_expires_at) return 0;
        return max(0, (int) now()->diffInDays($this->plan_expires_at));
    }

    public function graceDaysRemaining(): int
    {
        if (!$this->isInGracePeriod()) return 0;
        return max(0, (int) now()->diffInDays($this->grace_period_ends_at));
    }

    // ─── Plan helpers ──────────────────────────────────────────────────────

    public function planName(): string
    {
        return self::PLANS[$this->plan]['name'] ?? ucfirst($this->plan);
    }

    public function isWithinUnitLimit(): bool
    {
        return $this->currentUnitCount() < $this->unit_limit;
    }

    public function currentUnitCount(): int
    {
        return $this->properties()
            ->withCount('units')
            ->get()
            ->sum('units_count');
    }

    public function canAccessFeature(string $feature): bool
    {
        $exploreBlocked = ['bulk_invoices', 'pdf_download', 'auto_invoice'];
        if ($this->plan === 'explore' && in_array($feature, $exploreBlocked)) {
            return false;
        }
        return true;
    }

    // ─── SMS helpers ───────────────────────────────────────────────────────

    public function hasSmsCredits(int $needed = 1): bool
    {
        return $this->sms_credits >= $needed;
    }

    public function topUpMonthlyCredits(): void
    {
        $monthly = self::PLANS[$this->plan]['sms_credits_monthly'] ?? 0;
        if ($monthly > 0) {
            $this->increment('sms_credits', $monthly);
        }
    }

    // ─── Relationships ─────────────────────────────────────────────────────

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}