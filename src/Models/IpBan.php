<?php

declare(strict_types=1);

namespace Spine\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Persistent IP ban record.
 *
 * IpGuardService keeps the hot path in cache (Redis when available) and uses
 * this table as the durable fallback plus the audit trail. A null expires_at
 * means a permanent ban.
 */
class IpBan extends Model
{
    protected $fillable = ['ip', 'reason', 'banned_at', 'expires_at'];

    protected $casts = [
        'banned_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Only bans that have not expired yet.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Bans whose TTL has already elapsed.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    public function scopeForIp(Builder $query, string $ip): Builder
    {
        return $query->where('ip', $ip);
    }

    public function isActive(): bool
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }
}
