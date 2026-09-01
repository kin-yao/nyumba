<?php

namespace App\Models;

use App\Models\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Model;

/**
 * A ledger entry for money Nyumba is holding on behalf of a landlord,
 * collected via Pesalink central collection. Credits only for now —
 * disbursement (debits) is explicitly out of scope until IPSL provides
 * outbound-transfer documentation. Never edit a row after creation;
 * corrections happen via a new entry, same rule as every other financial
 * record in this app.
 */
class WalletTransaction extends Model
{
    use BelongsToAccount;

    protected $fillable = [
        'account_id',
        'property_id',
        'payment_id',
        'payment_event_id',
        'type',
        'amount',
        'balance_after',
        'description',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function paymentEvent()
    {
        return $this->belongsTo(PaymentEvent::class);
    }

    /**
     * Credits a landlord's wallet for a central-collection payment. The
     * only entry point that should ever create a 'credit' row — keeps the
     * balance-computation logic in one place.
     */
    public static function credit(int $accountId, string $amount, ?int $propertyId, ?int $paymentId, ?int $paymentEventId, string $description): self
    {
        $currentBalance = self::currentBalance($accountId);
        $newBalance     = \App\Support\Money::add($currentBalance, $amount);

        return self::create([
            'account_id'       => $accountId,
            'property_id'      => $propertyId,
            'payment_id'       => $paymentId,
            'payment_event_id' => $paymentEventId,
            'type'             => 'credit',
            'amount'           => $amount,
            'balance_after'    => $newBalance,
            'description'      => $description,
        ]);
    }

    /**
     * The authoritative balance — sums every entry rather than trusting
     * the cached balance_after on the latest row, so it's self-verifying.
     */
    public static function currentBalance(int $accountId): string
    {
        $credits = self::withoutGlobalScopes()->where('account_id', $accountId)->where('type', 'credit')->sum('amount');
        $debits  = self::withoutGlobalScopes()->where('account_id', $accountId)->where('type', 'debit')->sum('amount');

        return \App\Support\Money::sub($credits, $debits);
    }
}