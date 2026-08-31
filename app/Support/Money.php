<?php

namespace App\Support;

/**
 * Decimal-safe money arithmetic. Every monetary comparison or sum in the
 * payment-allocation and reconciliation paths must go through this, never
 * through floatval()/(float) — see build spec §5.1 and §1.3.
 *
 * Amounts are handled as fixed-scale (2dp) decimal strings via bcmath.
 * Every currency this app supports (KES, TZS, UGX, USD) uses 2 decimal
 * places, so a single fixed scale is safe here.
 */
class Money
{
    private const SCALE = 2;

    public static function add(mixed $a, mixed $b): string
    {
        return bcadd(self::normalize($a), self::normalize($b), self::SCALE);
    }

    public static function sub(mixed $a, mixed $b): string
    {
        return bcsub(self::normalize($a), self::normalize($b), self::SCALE);
    }

    /**
     * Multiplication for computing a money amount from a quantity and a
     * rate (e.g. utility units consumed x rate per unit). Rounds to SCALE
     * like every other money operation here.
     */
    public static function mul(mixed $a, mixed $b): string
    {
        return bcmul(self::normalize($a), self::normalize($b), self::SCALE);
    }

    /**
     * Division at higher precision than SCALE, for deriving a discrete
     * count (e.g. months covered by a payment) from two money amounts.
     * Not for money amounts themselves — money stays at SCALE everywhere
     * else in this class.
     */
    public static function div(mixed $a, mixed $b, int $decimals = 10): string
    {
        return bcdiv(self::normalize($a), self::normalize($b), $decimals);
    }

    public static function min(mixed $a, mixed $b): string
    {
        return self::lte($a, $b) ? self::normalize($a) : self::normalize($b);
    }

    public static function max(mixed $a, mixed $b): string
    {
        return self::gte($a, $b) ? self::normalize($a) : self::normalize($b);
    }

    /** $a <= $b */
    public static function lte(mixed $a, mixed $b): bool
    {
        return bccomp(self::normalize($a), self::normalize($b), self::SCALE) <= 0;
    }

    /** $a >= $b */
    public static function gte(mixed $a, mixed $b): bool
    {
        return bccomp(self::normalize($a), self::normalize($b), self::SCALE) >= 0;
    }

    /** $a > 0 */
    public static function isPositive(mixed $a): bool
    {
        return bccomp(self::normalize($a), '0.00', self::SCALE) > 0;
    }

    /**
     * Coerce a raw value (an Eloquent decimal:2 cast, request input, a
     * literal) into a well-formed 2dp decimal string bcmath can operate on
     * safely. Throws rather than silently producing 0 for garbage input,
     * since a silent 0 on a money field is exactly the kind of mistake
     * this class exists to prevent.
     */
    public static function normalize(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '0.00';
        }

        $string = is_string($value) ? trim($value) : (string) $value;

        if (!is_numeric($string)) {
            throw new \InvalidArgumentException(
                'Money::normalize() received a non-numeric value: ' . var_export($value, true)
            );
        }

        // bcadd with 0 pads/truncates to SCALE without any float rounding.
        return bcadd($string, '0', self::SCALE);
    }
}