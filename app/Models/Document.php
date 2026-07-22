<?php

namespace App\Models;

use App\Models\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use BelongsToAccount;

    protected $fillable = [
        'account_id',
        'lease_id',
        'uploaded_by',
        'label',
        'path',
        'original_filename',
        'file_size',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}