<?php

declare(strict_types=1);

namespace Spine\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * RegisterOtpService — generate/verify email OTP for staff self-register.
 *
 * Flow:
 *   start(email, ip, locale) → send OTP email, return verificationId
 *   verify(verificationId, code, ip) → check code, increment fail counter, maybe ban IP
 *
 * OTP state and the per-IP failure counter live in the cache; bans are persisted
 * by IpGuardService so they survive a cache flush. See IpGuardService for the
 * cache/Redis fallback rules.
 */
class RegisterOtpService
{
    private const TTL = 600; // 10 min
    private const MAX_ATTEMPTS = 5;

    /**
     * Start OTP flow for email + IP.
     *
     * @return array{verification_id: string, expires_at: string, attempts_left: int}
     */
    public function start(string $email, string $ip, string $locale = 'en'): array
    {
        $verificationId = (string) Str::uuid();
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        $data = [
            'email' => $email,
            'code' => hash('sha256', $code),
            'ip' => $ip,
            'locale' => $locale,
            'created_at' => now()->toDateTimeString(),
            'attempts' => 0,
        ];

        Cache::put("register_otp:{$verificationId}", $data, now()->addSeconds(self::TTL));

        // Send OTP email (via MailService)
        $this->sendOtpEmail($email, $code, $locale);

        return [
            'verification_id' => $verificationId,
            'expires_at' => now()->addSeconds(self::TTL)->toDateTimeString(),
            'attempts_left' => self::MAX_ATTEMPTS,
        ];
    }

    /**
     * Verify OTP code.
     *
     * @return array{success: bool, staff_id?: int, message?: string, banned?: bool}
     */
    public function verify(string $verificationId, string $code, string $ip): array
    {
        $key = "register_otp:{$verificationId}";
        $data = Cache::get($key);

        if (! $data) {
            return ['success' => false, 'message' => 'Verification expired or invalid.'];
        }

        // Check IP ban
        if (app(IpGuardService::class)->isBanned($ip)) {
            return ['success' => false, 'message' => 'IP banned.', 'banned' => true];
        }

        $data['attempts']++;
        $correct = hash('sha256', $code) === $data['code'];

        if (! $correct) {
            // Increment fail counter
            app(IpGuardService::class)->registerFailure($ip);

            Cache::put($key, $data, now()->addSeconds(self::TTL));

            $remaining = self::MAX_ATTEMPTS - $data['attempts'];
            if ($remaining <= 0) {
                app(IpGuardService::class)->banIp($ip, 'register_failures_exceeded');

                Cache::forget($key);
                return ['success' => false, 'message' => 'Too many failed attempts. IP banned.', 'banned' => true];
            }

            return ['success' => false, 'message' => "Invalid code. {$remaining} attempts left."];
        }

        // Success — create user
        Cache::forget($key);
        app(IpGuardService::class)->clearFailures($ip);

        return $this->createStaff($data);
    }

    protected function createStaff(array $data): array
    {
        $userClass = (string) config('auth.providers.users.model');

        $user = $userClass::create([
            'name' => $data['email'],
            'email' => $data['email'],
            'password' => Hash::make(Str::random(16)),
            'email_verified_at' => now(),
        ]);

        // Assign Employee role
        $user->assignRole('employee');

        // Dispatch event
        event(new \Spine\Events\StaffLogIn($user, null, ['register' => true]));

        return [
            'success' => true,
            'staff_id' => $user->id,
            'message' => 'Account created. Please login.',
        ];
    }

    protected function sendOtpEmail(string $email, string $code, string $locale): void
    {
        // Delegate to MailService
        app(MailService::class)->sendOtp($email, $code, $locale);
    }
}