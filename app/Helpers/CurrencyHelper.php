<?php

namespace App\Helpers;

class CurrencyHelper
{
    const CURRENCIES = [
        'KES' => [
            'name'     => 'Kenyan Shilling',
            'symbol'   => 'KES',
            'decimals' => 0,
            'flag'     => '🇰🇪',
        ],
        'TZS' => [
            'name'     => 'Tanzanian Shilling',
            'symbol'   => 'TZS',
            'decimals' => 0,
            'flag'     => '🇹🇿',
        ],
        'UGX' => [
            'name'     => 'Ugandan Shilling',
            'symbol'   => 'UGX',
            'decimals' => 0,
            'flag'     => '🇺🇬',
        ],
        'USD' => [
            'name'     => 'US Dollar',
            'symbol'   => 'USD',
            'decimals' => 2,
            'flag'     => '🇺🇸',
        ],
    ];

    public static function format(float $amount, ?string $currency = null): string
    {
        $currency = $currency ?? static::current();
        $config   = static::CURRENCIES[$currency] ?? static::CURRENCIES['KES'];

        return $config['symbol'] . ' ' . number_format($amount, $config['decimals']);
    }

    public static function current(): string
    {
        if (auth()->check() && auth()->user()->account) {
            return auth()->user()->account->currency ?? 'KES';
        }
        return 'KES';
    }

    public static function symbol(?string $currency = null): string
    {
        $currency = $currency ?? static::current();
        return static::CURRENCIES[$currency]['symbol'] ?? 'KES';
    }

    public static function decimals(?string $currency = null): int
    {
        $currency = $currency ?? static::current();
        return static::CURRENCIES[$currency]['decimals'] ?? 0;
    }

    public static function all(): array
    {
        return static::CURRENCIES;
    }
}