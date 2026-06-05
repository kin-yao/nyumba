<?php

namespace App\Models;

use App\Models\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    use HasFactory, BelongsToAccount;

    protected $fillable = ['account_id', 'name', 'channel', 'body'];

    public function messages()
    {
        return $this->hasMany(Message::class, 'template_id');
    }
}