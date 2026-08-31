<?php

namespace App\Models;

use App\Models\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentEvent extends Model
{
    use HasFactory, BelongsToAccount;

    // Explicit, closed state machine, build spec §4.4. No transition is
    // valid unless listed in ALLOWED_TRANSITIONS below. Callers must use
    // transitionTo(), never ->update(['status' => ...]) directly.
    public const STATUS_RECEIVED             = 'RECEIVED';
    public const STATUS_VERIFIED             = 'VERIFIED';
    public const STATUS_VERIFICATION_FAILED  = 'VERIFICATION_FAILED';
    public const STATUS_QUEUED               = 'QUEUED';
    public const STATUS_PROCESSING           = 'PROCESSING';
    public const STATUS_RECONCILED           = 'RECONCILED';
    public const STATUS_UNMATCHED            = 'UNMATCHED';
    public const STATUS_REQUIRES_REVIEW      = 'REQUIRES_REVIEW';
    public const STATUS_FAILED               = 'FAILED';
    public const STATUS_REVERSED             = 'REVERSED';

    private const ALLOWED_TRANSITIONS = [
        self::STATUS_RECEIVED            => [self::STATUS_VERIFIED, self::STATUS_VERIFICATION_FAILED],
        self::STATUS_VERIFIED            => [self::STATUS_QUEUED],
        self::STATUS_QUEUED              => [self::STATUS_PROCESSING],
        self::STATUS_PROCESSING          => [self::STATUS_RECONCILED, self::STATUS_UNMATCHED, self::STATUS_FAILED],
        self::STATUS_UNMATCHED           => [self::STATUS_REQUIRES_REVIEW],
        self::STATUS_REQUIRES_REVIEW     => [self::STATUS_RECONCILED],
        self::STATUS_FAILED              => [self::STATUS_PROCESSING, self::STATUS_REQUIRES_REVIEW],
        self::STATUS_RECONCILED          => [self::STATUS_REVERSED],
        self::STATUS_VERIFICATION_FAILED => [],
        self::STATUS_REVERSED            => [],
    ];

    protected $fillable = [
        'account_id',
        'property_id',
        'provider',
        'channel',
        'provider_transaction_id',
        'amount',
        'currency',
        'raw_payload',
        'signature_valid',
        'received_at',
        'status',
    ];

    /**
     * PHP-level defaults mirroring the DB-level ones in the migration.
     * Without these, a freshly ->create()'d instance has these attributes
     * as null in memory until the model is re-fetched (fresh()/refresh()),
     * because Eloquent doesn't pull DB-computed column defaults back into
     * the in-memory object after an insert — only the auto-increment id.
     * A null status would make the very first transitionTo() call on a
     * brand-new event fail as "invalid transition from null".
     */
    protected $attributes = [
        'status'          => self::STATUS_RECEIVED,
        'currency'        => 'KES',
        'signature_valid' => false,
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'signature_valid' => 'boolean',
        'received_at'     => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Move this event to a new status, enforcing the state machine in
     * build spec §4.4. Throws if the transition isn't explicitly allowed,
     * callers must handle that rather than letting an invalid status
     * through silently.
     */
    public function transitionTo(string $newStatus): void
    {
        $allowed = self::ALLOWED_TRANSITIONS[$this->status] ?? [];

        if (!in_array($newStatus, $allowed, true)) {
            throw new \LogicException(
                "PaymentEvent #{$this->id}: invalid transition from '{$this->status}' to '{$newStatus}'."
            );
        }

        $this->update(['status' => $newStatus]);
    }
}