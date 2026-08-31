<?php

declare(strict_types=1);

namespace Spine\Support\Helpers;

/**
 * Number and currency formatting helper.
 *
 * Business logic functions (tax calculation, total column updates, etc.) are
 * intentionally not included here — they will move to module services (e.g.
 * modules/Sales/Services/InvoiceService.php) later.
 */
class Number
{
    /**
     * Format a number with thousands separators and decimals.
     *
     * Example: Number::formatNumber(1500000, 2) → '1,500,000.00'
     *         Number::formatNumber(null) → ''
     */
    public static function formatNumber(null|float|int|string $number, ?int $decimals = null): string
    {
        if ($number === null || $number === '') {
            return '';
        }

        $number = (float) $number;

        if ($decimals === null) {
            $decimals = 2;
        }

        return number_format($number, $decimals, '.', ',');
    }

    /**
     * Format a monetary value as a currency string.
     *
     * Example: Number::formatMoney(1500000, 'IDR') → 'Rp 1.500.000,00'
     *         Number::formatMoney(0, 'IDR', true) → ''   (blank if zero)
     */
    public static function formatMoney(null|float|int|string $amount, string $currencyCode = 'IDR', bool $blankZero = false): string
    {
        if ($amount === null || $amount === '') {
            if ($blankZero) {
                return '';
            }

            return '0';
        }

        $amount = (float) $amount;
        $decimals = self::getDecimalPlaces($currencyCode);
        $formatted = number_format($amount, $decimals, ',', '.');
        $symbol = self::getCurrencySymbol($currencyCode);

        return $symbol . ' ' . $formatted;
    }

    /**
     * Get the number of decimal places for a given currency code.
     *
     * Basic rules (can be replaced by a Currency model later):
     *   JPY, KRW → 0 (no decimals)
     *   default → 2
     */
    public static function getDecimalPlaces(string $currencyCode): int
    {
        return match (strtoupper($currencyCode)) {
            'JPY', 'KRW' => 0,
            default => 2,
        };
    }

    /**
     * Get the currency symbol for an ISO code.
     *
     * Basic rules (can be replaced by a Currency model later):
     *   IDR → Rp, USD → $, EUR → €, GBP → £, JPY → ¥, KRW → ₩
     */
    public static function getCurrencySymbol(string $currencyCode): string
    {
        return match (strtoupper($currencyCode)) {
            'IDR' => 'Rp',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'KRW' => '₩',
            default => $currencyCode,
        };
    }

    /**
     * Whether the system uses multiple active currencies.
     *
     * Currently a placeholder: always returns false.
     * Will be implemented later by checking the Currency model / SettingService
     * for active currencies.
     */
    public static function isUsingMultipleCurrencies(): bool
    {
        return false;
    }

    /**
     * Format a percentage as a string.
     *
     * Example: Number::formatPercent(15.5, 1) → '15.5%'
     */
    public static function formatPercent(null|float|int|string $value, ?int $decimals = null): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $value = (float) $value;

        if ($decimals === null) {
            $decimals = 1;
        }

        return number_format($value, $decimals, '.', ',') . '%';
    }

    /**
     * Parse a currency-formatted string into a numeric value.
     *
     * Returns null if the value cannot be parsed.
     * Example: Number::parseMoney('Rp 1.500.000,00') → 1500000.00
     */
    public static function parseMoney(null|string $text): ?float
    {
        if ($text === null || $text === '') {
            return null;
        }

        // Strip common currency symbols and thousands separators
        $cleaned = preg_replace('/[Rp$€£¥₩\s]/u', '', $text);
        $cleaned = str_replace('.', '', $cleaned);
        $cleaned = str_replace(',', '.', $cleaned);

        $result = filter_var($cleaned, FILTER_VALIDATE_FLOAT);

        if ($result === false) {
            return null;
        }

        return $result;
    }
}
