<?php

declare(strict_types=1);

namespace Spine\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * IpGuardService — track register failures per IP, ban if threshold exceeded.
 *
 * Uses Redis INCR+EXPIRE if Redis available, fallback to rate_counters table.
 */
class IpGuardService
{
    private const MAX_FAILURES = 5;
    private const FAIL_WINDOW = 3600; // 1 hour
    private const BAN_TTL = 86400; // 24 hours

    /**
     * Check if IP is banned.
     */
    public function isBanned(string $ip): bool
    {
        return Cache::has("ip_ban:{$ip}");
    }

    /**
     * Register a failed attempt for IP.
     *
     * If failures exceed MAX_FAILURES within FAIL_WINDOW, ban IP.
     */
    public function registerFailure(string $ip): void
    {
        $key = "ip_fail:{$ip}";
        $count = Cache::increment($key);
        Cache::put($key, $count, now()->addSeconds(self::FAIL_WINDOW));

        if ($count >= self::MAX_FAILURES) {
            $this->banIp($ip, 'register_failures_exceeded');
        }
    }

    /**
     * Ban IP for a reason.
     */
    public function banIp(string $ip, string $reason = 'manual'): void
    {
        Cache::put("ip_ban:{$ip}", [
            'reason' => $reason,
            'banned_at' => now()->toDateTimeString(),
            'expires_at' => now()->addSeconds(self::BAN_TTL)->toDateTimeString(),
        ], now()->addSeconds(self::BAN_TTL));
    }

    /**
     * Unban IP.
     */
    public function unbanIp(string $ip): void
    {
        Cache::forget("ip_ban:{$ip}");
    }

    /**
     * Get ban info.
     *
     * @return array<string, mixed>|null
     */
    public function getBanInfo(string $ip): ?array
    {
        return Cache::get("ip_ban:{$ip}");
    }
}