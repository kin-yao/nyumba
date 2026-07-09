<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class TenantOtpVerification extends Model
{
    protected $fillable = [
        'phone',
        'code',
        'expires_at',
        'attempts',
        'verified_at',
    ];

    protected $casts = [
        'expires_at'  => 'datetime',
        'verified_at' => 'datetime',
        'attempts'    => 'integer',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function isLocked(): bool
    {
        return $this->attempts >= 5;
    }

    public function checkCode(string $code): bool
    {
        return Hash::check($code, $this->code);
    }

    public static function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function createForPhone(string $phone): array
    {
        static::where('phone', $phone)
            ->whereNull('verified_at')
            ->delete();

        $code = static::generateCode();

        $otp = static::create([
            'phone'      => $phone,
            'code'       => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
            'attempts'   => 0,
        ]);

        return ['otp' => $otp, 'code' => $code];
    }
}