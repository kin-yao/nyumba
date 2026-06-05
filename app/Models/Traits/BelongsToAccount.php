<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToAccount
{
    protected static function bootBelongsToAccount(): void
    {
        // Automatically filter all queries by account_id
        static::addGlobalScope('account', function (Builder $builder) {
            if (auth()->check()) {
                $builder->where(
                    (new static)->getTable() . '.account_id',
                    auth()->user()->account_id
                );
            }
        });

        // Automatically set account_id when creating a record
        static::creating(function ($model) {
            if (auth()->check() && empty($model->account_id)) {
                $model->account_id = auth()->user()->account_id;
            }
        });
    }
}