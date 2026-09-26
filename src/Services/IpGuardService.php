<?php

declare(strict_types=1);

namespace Spine\Services;

use Illuminate\Support\Facades\Cache;
use Spine\Models\IpBan;

/**
 * IpGuardService — track register failures per IP, ban when the threshold is
 * exceeded.
 *
 * The `ip_bans` table is the source of truth so a ban survives a Redis flush or
 * a cache store swap. Cache stays on the hot path to avoid a query per request;
 * on a cache miss the row is rehydrated from the database. Failure counters use
 * the cache only — losing them on a flush is acceptable, losing a ban is not.
 */
class IpGuardService
{
    private const MAX_FAILURES = 5;
    private const FAIL_WINDOW = 3600; // 1 hour
    private const BAN_TTL = 86400; // 24 hours

    /**
     * Check if the IP currently has an active ban.
     */
    public function isBanned(string $ip): bool
    {
        return $this->getBanInfo($ip) !== null;
    }

    /**
     * Register a failed attempt for an IP.
     *
     * Bans the IP once the failure count reaches MAX_FAILURES inside the
     * failure window.
     */
    public function registerFailure(string $ip): void
    {
        $key = $this->failureKey($ip);

        // add() applies the TTL only when the counter is created, so the window
        // is fixed instead of sliding on every increment. Then the first
        // increment turns the seeded 0 into 1.
        Cache::add($key, 0, now()->addSeconds(self::FAIL_WINDOW));
        $count = (int) Cache::increment($key);

        if ($count >= self::MAX_FAILURES) {
            $this->banIp($ip, 'register_failures_exceeded');
        }
    }

    /**
     * Reset the failure counter for an IP, called after a successful attempt.
     */
    public function clearFailures(string $ip): void
    {
        Cache::forget($this->failureKey($ip));
    }

    /**
     * Ban an IP for BAN_TTL and persist it.
     */
    public function banIp(string $ip, string $reason = 'manual', ?int $ttlSeconds = null): IpBan
    {
        $ttl = $ttlSeconds ?? self::BAN_TTL;
        $expiresAt = now()->addSeconds($ttl);

        $ban = IpBan::updateOrCreate(
            ['ip' => $ip],
            [
                'reason' => $reason,
                'banned_at' => now(),
                'expires_at' => $expiresAt,
            ],
        );

        Cache::put($this->banKey($ip), [
            'reason' => $reason,
            'banned_at' => $ban->banned_at?->toDateTimeString(),
            'expires_at' => $ban->expires_at?->toDateTimeString(),
        ], $ttl);

        $this->clearFailures($ip);

        return $ban;
    }

    /**
     * Ban an IP permanently (no expiry).
     */
    public function banIpForever(string $ip, string $reason = 'manual'): IpBan
    {
        $ban = IpBan::updateOrCreate(
            ['ip' => $ip],
            [
                'reason' => $reason,
                'banned_at' => now(),
                'expires_at' => null,
            ],
        );

        Cache::forever($this->banKey($ip), [
            'reason' => $reason,
            'banned_at' => $ban->banned_at?->toDateTimeString(),
            'expires_at' => null,
        ]);

        $this->clearFailures($ip);

        return $ban;
    }

    /**
     * Lift every ban on an IP, in cache and in the database.
     */
    public function unbanIp(string $ip): void
    {
        Cache::forget($this->banKey($ip));
        Cache::forget($this->failureKey($ip));

        IpBan::query()->where('ip', $ip)->delete();
    }

    /**
     * Active ban info for an IP, or null when the IP is not banned.
     *
     * @return array<string, mixed>|null
     */
    public function getBanInfo(string $ip): ?array
    {
        $cached = Cache::get($this->banKey($ip));

        if (is_array($cached)) {
            return $cached;
        }

        $ban = IpBan::query()->forIp($ip)->active()->latest('banned_at')->first();

        if (! $ban) {
            return null;
        }

        $info = [
            'reason' => $ban->reason,
            'banned_at' => $ban->banned_at?->toDateTimeString(),
            'expires_at' => $ban->expires_at?->toDateTimeString(),
        ];

        $ttl = $ban->expires_at ? max(1, now()->diffInSeconds($ban->expires_at)) : null;
        Cache::put($this->banKey($ip), $info, $ttl);

        return $info;
    }

    /**
     * Delete ban rows whose TTL has elapsed. Meant for scheduled cleanup.
     */
    public function purgeExpired(): int
    {
        return IpBan::query()->expired()->delete();
    }

    private function banKey(string $ip): string
    {
        return "ip_ban:{$ip}";
    }

    private function failureKey(string $ip): string
    {
        return "ip_fail:{$ip}";
    }
}
