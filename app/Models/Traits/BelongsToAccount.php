<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToAccount
{
    protected static function bootBelongsToAccount(): void
    {
        // Automatically filter all queries by the logged-in user's account_id.
        //
        // ⚠️  ADMIN CONTEXT WARNING:
        // This global scope fires on EVERY query for models using this trait.
        // In admin controllers (AdminController, MpesaC2BController, etc.)
        // where you are deliberately querying another account's data, you MUST
        // bypass this scope or you will silently get 0 results / wrong data.
        //
        // Always use ::withoutGlobalScopes() in admin queries. Examples:
        //   Property::withoutGlobalScopes()->where('account_id', $account->id)->get()
        //   $account->load(['properties' => fn($q) => $q->withoutGlobalScopes()])
        //
        // Models using this trait: Property, Unit, Tenant, Lease, Invoice,
        // Payment, Expense, UtilityReading, UtilityRate, MaintenanceRequest,
        // Message, MessageTemplate.
        static::addGlobalScope('account', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where(
                    (new static)->getTable() . '.account_id',
                    auth()->user()->account_id
                );
            }
        });

        // Automatically set account_id when creating a record.
        // In admin contexts, always set account_id explicitly on the model
        // before saving, or this will assign the admin's own account_id.
        static::creating(function ($model) {
            if (auth()->check() && empty($model->account_id)) {
                $model->account_id = auth()->user()->account_id;
            }
        });
    }
}