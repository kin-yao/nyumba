<?php

namespace App\Models;

use App\Models\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Model;

class ProofOfPayment extends Model
{
    use BelongsToAccount;

    protected $fillable = [
        'account_id',
        'tenant_id',
        'lease_id',
        'payment_for',
        'period_month',
        'period_year',
        'method',
        'message',
        'status',
        'payment_id',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function periodLabel(): ?string
    {
        if ($this->payment_for !== 'rent' || !$this->period_month || !$this->period_year) {
            return null;
        }

        return \Carbon\Carbon::createFromDate($this->period_year, $this->period_month, 1)->format('F Y');
    }
}