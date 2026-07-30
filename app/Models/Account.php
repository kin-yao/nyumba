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
    ];

    // Trial: 7 days, capped at 3 units — the only plan with a real unit cap.
    const EXPLORE_TRIAL_DAYS = 7;

    // Paid pricing is a function of actual unit count, not a menu of fixed
    // tiers — see priceForUnitCount(). Bands:
    //   Starter (1–75):    flat fee, flat SMS allowance
    //   Growth (76–150):   per-unit rate
    //   Enterprise (151+): lower per-unit rate
    // Paying accounts have no unit_limit cap (set to 999999 on upgrade) —
    // cost simply scales with however many units they add.
    const PRICING_BANDS = [
        'starter' => [
            'name'       => 'Starter',
            'max_units'  => 75,
            'flat_price' => 3750,
            'flat_sms'   => 300,
        ],
        'growth' => [
            'name'         => 'Growth',
            'max_units'    => 150,
            'per_unit'     => 50,
            'sms_per_unit' => 4,
        ],
        'enterprise' => [
            'name'         => 'Enterprise',
            'per_unit'     => 40,
            'sms_per_unit' => 4,
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

    /**
     * The actual pricing engine. Returns the plan label, monthly price,
     * yearly price (pay 12 months, get 1 free → 11× monthly), and SMS
     * allowance for a given unit count. This is the single source of truth
     * for what a landlord pays — nothing else should compute price directly.
     */
    public static function priceForUnitCount(int $units): array
    {
        $units = max(1, $units);
        $bands = self::PRICING_BANDS;

        if ($units <= $bands['starter']['max_units']) {
            $planKey = 'starter';
            $monthly = $bands['starter']['flat_price'];
            $sms     = $bands['starter']['flat_sms'];
        } elseif ($units <= $bands['growth']['max_units']) {
            $planKey = 'growth';
            $monthly = $units * $bands['growth']['per_unit'];
            $sms     = $units * $bands['growth']['sms_per_unit'];
        } else {
            $planKey = 'enterprise';
            $monthly = $units * $bands['enterprise']['per_unit'];
            $sms     = $units * $bands['enterprise']['sms_per_unit'];
        }

        return [
            'plan_key' => $planKey,
            'name'     => $bands[$planKey]['name'],
            'units'    => $units,
            'monthly'  => $monthly,
            'yearly'   => $monthly * 11, // pay 12 months, get 1 free
            'sms'      => $sms,
        ];
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
        if (!$this->trial_ends_at) return self::EXPLORE_TRIAL_DAYS;
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