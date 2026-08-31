<?php

declare(strict_types=1);

namespace Spine\Services;

/**
 * DateService — date/datetime formatting helpers.
 *
 * Adopted functions:
 *   - get_current_date_format → dateFormat()
 *   - _d / _dt                → format() / formatDateTime()
 *   - to_sql_date             → toSql()
 *
 * The format is resolved from the 'dateformat' setting ("php|picker" pair).
 * Every method dispatches a mutable DateFormatting event so listeners can
 * adjust the output (equivalent of the legacy date-format filters).
 */
class DateService
{
    /**
     * Resolve the active date format pair from the 'dateformat' setting.
     *
     * @return array{0: string, 1: string} [phpFormat, pickerFormat]
     */
    public function dateFormat(): array
    {
        $format = app(\Spine\Services\SettingService::class)->get('dateformat')
            ?? config('app.date_format', 'd/m/Y|d/m/Y');

        $parts = explode('|', (string) $format);
        $parts = array_pad($parts, 2, $parts[0] ?? 'd/m/Y');

        $payload = ['format' => $parts, 'php' => false];
        $creating = new \Spine\Events\DateFormatting($payload);
        event($creating);
        $payload = $creating->payload;

        return $payload['format'];
    }

    /**
     * Format a date string using the active date format.
     *
     * @param  string|null  $date
     * @return string
     */
    public function format(?string $date): string
    {
        if ($date === '' || $date === null || $date === '0000-00-00') {
            return '';
        }

        if (str_contains($date, ' ')) {
            return $this->formatDateTime($date);
        }

        [$php] = $this->dateFormat();
        $formatted = date(str_replace('%', '', $php), strtotime($date));

        $payload = ['formatted' => $formatted, 'date' => $date];
        $creating = new \Spine\Events\DateFormatting($payload);
        event($creating);
        $payload = $creating->payload;

        return $payload['formatted'];
    }

    /**
     * Format a datetime string using the active date format + time.
     *
     * @param  string|null  $date
     * @return string
     */
    public function formatDateTime(?string $date): string
    {
        if ($date === '' || $date === null || $date === '0000-00-00 00:00:00') {
            return '';
        }

        [$php] = $this->dateFormat();
        $hour24 = (int) app(\Spine\Services\SettingService::class)->get('time_format', 24) === 24;
        $timeFormat = $hour24 ? 'H:i' : 'h:i A';
        $formatted = date(str_replace('%', '', $php) . ' ' . $timeFormat, strtotime($date));

        $payload = ['formatted' => $formatted, 'date' => $date];
        $creating = new \Spine\Events\DateFormatting($payload);
        event($creating);
        $payload = $creating->payload;

        return $payload['formatted'];
    }

    /**
     * Convert a date string to SQL format (Y-m-d or Y-m-d H:i:s).
     *
     * @param  string|null  $date
     * @param  bool  $datetime
     * @return string|null
     */
    public function toSql(?string $date, bool $datetime = false): ?string
    {
        if ($date === '' || $date === null) {
            return null;
        }

        [$php] = $this->dateFormat();
        $fromFormat = str_replace('%', '', $php);

        $payload = ['value' => $date, 'from_format' => $fromFormat, 'is_datetime' => $datetime];
        $creating = new \Spine\Events\DateFormatting($payload);
        event($creating);
        $payload = $creating->payload;

        $parsed = \DateTime::createFromFormat($payload['from_format'], $payload['value']);
        if (! $parsed && $datetime) {
            // d/m/Y + time: normalize to Y-m-d H:i:s before parsing.
            $normalized = preg_replace('#(\d{2})/(\d{2})/(\d{4})\s(.*)#', '$3-$2-$1 $4', $payload['value']);
            $ts = strtotime($normalized);
            if ($ts !== false) {
                $sql = date('Y-m-d H:i:s', $ts);
            } else {
                return null;
            }
        } elseif (! $parsed) {
            return null;
        } else {
            $sql = $parsed->format($datetime ? 'Y-m-d H:i:s' : 'Y-m-d');
        }

        $payload2 = ['sql' => $sql];
        $creating2 = new \Spine\Events\DateFormatting($payload2);
        event($creating2);
        $payload2 = $creating2->payload;

        return $payload2['sql'];
    }
}
