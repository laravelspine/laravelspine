<?php

declare(strict_types=1);

namespace Spine\Support\Helpers;

use Illuminate\Support\Carbon;

/**
 * Time helper utilities.
 *
 */
class Time
{
    /**
     * Convert seconds to a human-readable time format (English).
     *
     * Example:
     *   Time::seconds_to_time_format(3600)  → "1 hour"
     *   Time::seconds_to_time_format(90)    → "1 minute 30 seconds"
     *   Time::seconds_to_time_format(0)     → "0 seconds"
     *   Time::seconds_to_time_format(3725)  → "1 hour 2 minutes 5 seconds"
     */
    public static function seconds_to_time_format(int $seconds): string
    {
        if ($seconds < 0) {
            $seconds = 0;
        }

        $hours = (int) floor($seconds / 3600);
        $minutes = (int) floor(($seconds % 3600) / 60);
        $secs = (int) ($seconds % 60);

        $parts = [];

        if ($hours > 0) {
            $parts[] = $hours . ' ' . ($hours === 1 ? 'hour' : 'hours');
        }

        if ($minutes > 0) {
            $parts[] = $minutes . ' ' . ($minutes === 1 ? 'minute' : 'minutes');
        }

        if ($secs > 0 || empty($parts)) {
            $parts[] = $secs . ' ' . ($secs === 1 ? 'second' : 'seconds');
        }

        return implode(' ', $parts);
    }

    /**
     * Convert hours to seconds.
     *
     * Example: Time::hours_to_seconds_format(2) → 7200
     */
    public static function hours_to_seconds_format(float|int $hours): int
    {
        return (int) ($hours * 3600);
    }

    /**
     * Format a relative timestamp (time ago) — "2 hours ago", "5 minutes ago".
     *
     * Wrapper around Carbon::diffForHumans().
     *
     * Example: Time::time_ago('2026-08-27 10:00:00') → "2 hours ago"
     */
    public static function time_ago(mixed $timestamp): string
    {
        $carbon = Carbon::parse($timestamp);

        return $carbon->diffForHumans();
    }

    /**
     * Check whether the timestamp is in the past (already "ago").
     *
     * Uses Carbon::isPast().
     */
    public static function isPast(mixed $timestamp): bool
    {
        return Carbon::parse($timestamp)->isPast();
    }

    /**
     * Check whether the timestamp is still in the future.
     */
    public static function isFuture(mixed $timestamp): bool
    {
        return Carbon::parse($timestamp)->isFuture();
    }

    /**
     * Get the difference in seconds between two timestamps.
     *
     * Example: Time::diffInSeconds('2026-08-27 12:00:00', '2026-08-27 10:00:00') → 7200
     */
    public static function diffInSeconds(mixed $from, mixed $to): int
    {
        $fromCarbon = Carbon::parse($from);
        $toCarbon = Carbon::parse($to);

        return (int) $fromCarbon->diffInSeconds($toCarbon, false);
    }
}
