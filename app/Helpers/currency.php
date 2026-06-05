<?php

use App\Helpers\CurrencyHelper;

if (!function_exists('currency')) {
    function currency(float $amount, ?string $curr = null): string
    {
        return CurrencyHelper::format($amount, $curr);
    }
}

if (!function_exists('currency_symbol')) {
    function currency_symbol(?string $curr = null): string
    {
        return CurrencyHelper::symbol($curr);
    }
}