<?php

declare(strict_types=1);

namespace Spine\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spine\Database\Factories\UserFactory;
use Spine\Traits\HasMetaData;

/**
 * App User model — extends the core Spine User base.
 *
 * Consumed by spine/laravel-spine. Includes ULID generation and language
 * preference. Override in your app if you need additional fields.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, HasMetaData;

    /**
     * The guard name used by spatie/laravel-permission.
     */
    protected $guard_name = 'sanctum';

    protected $fillable = [
        'name',
        'email',
        'password',
        'ulid',
        'language',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'ulid',
    ];

    /**
     * Resolve the model factory from this package.
     *
     * Without this override Laravel guesses a factory namespace from the model
     * (Database\Factories\Spine\Models\UserFactory) and fails, because the model
     * lives in Spine\Models while the factory ships with the package.
     */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (User $user): void {
            if (! $user->ulid) {
                $user->ulid = static::generateUlid();
                $user->saveQuietly();
            }
        });
    }

    /**
     * Generate a 26-char ULID.
     */
    public static function generateUlid(): string
    {
        $timestamp = (int) (microtime(true) * 1000);

        $encoded = '';
        $ts = $timestamp;
        $chars = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
        for ($i = 0; $i < 10; $i++) {
            $encoded = $chars[$ts & 0x1f] . $encoded;
            $ts >>= 5;
        }

        $random = '';
        for ($i = 0; $i < 16; $i++) {
            $random .= $chars[random_int(0, 31)];
        }

        return $encoded . $random;
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
