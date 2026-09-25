<?php

declare(strict_types=1);

namespace Spine\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Cache;

class TwoFactorService
{
    private const TOTP_PERIOD = 30;
    private const TOTP_DIGITS = 6;
    private const TOTP_ALGO = 'sha1';
    private const BASE32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    private const EMAIL_TTL = 600;
    private const MAX_EMAIL_ATTEMPTS = 5;

    public function generateSecret(): string
    {
        $bytes = random_bytes(20);
        $base32 = '';
        $buffer = 0;
        $bitsLeft = 0;

        for ($i = 0; $i < 20; $i++) {
            $buffer = ($buffer << 8) | ord($bytes[$i]);
            $bitsLeft += 8;

            while ($bitsLeft >= 5) {
                $bitsLeft -= 5;
                $base32 .= self::BASE32[($buffer >> $bitsLeft) & 0x1f];
            }
        }

        // Pad to multiple of 8
        while (strlen($base32) % 8 !== 0) {
            $base32 .= '=';
        }

        return $base32;
    }

    public function verifyTotp(string $secret, string $code): bool
    {
        $code = (int) trim($code);
        $key = $this->base32Decode($secret);

        if ($key === false) {
            return false;
        }

        $counter = (int) floor(time() / self::TOTP_PERIOD);

        for ($offset = -1; $offset <= 1; $offset++) {
            if ($this->generateTotp($secret, $counter + $offset) === $code) {
                return true;
            }
        }

        return false;
    }

    public function generateTotp(string $secret, int $counter): int
    {
        $key = $this->base32Decode($secret);

        if ($key === false) {
            return -1;
        }

        $binary = pack('N', 0) . pack('N', 0) . pack('N', 0) . pack('N', $counter);
        $hmac = hash_hmac(self::TOTP_ALGO, $binary, $key, true);
        $offset = ord($hmac[strlen($hmac) - 1]) & 0x0f;
        $code = unpack('N', substr($hmac, $offset, 4))[1] & 0x7fffffff;

        return $code % (10 ** self::TOTP_DIGITS);
    }

    public function qrCode(string $secret, string $issuer, string $account): string
    {
        $uri = $this->provisioningUri($secret, $issuer, $account);
        $qrCode = QrCode::create($uri);
        $result = (new SvgWriter())->write($qrCode);

        return $result->getString();
    }

    public function provisioningUri(string $secret, string $issuer, string $account): string
    {
        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=%s&digits=%d&period=%d',
            urlencode($issuer),
            urlencode($account),
            urlencode($secret),
            urlencode($issuer),
            urlencode(self::TOTP_ALGO),
            self::TOTP_DIGITS,
            self::TOTP_PERIOD,
        );
    }

    public function sendEmailCode(int $userId, string $email, string $locale = 'en'): array
    {
        $code = str_pad((string) random_int(100000, 999999), self::TOTP_DIGITS, '0', STR_PAD_LEFT);
        $data = [
            'code' => hash('sha256', $code),
            'attempts' => 0,
            'locale' => $locale,
            'created_at' => now()->toDateTimeString(),
        ];

        Cache::put("2fa_email:{$userId}", $data, now()->addSeconds(self::EMAIL_TTL));

        try {
            app(MailService::class)->send([
                'to' => $email,
                'subject' => "Two-factor verification code",
                'view' => 'emails.two_factor_code',
                'data' => [
                    'code' => $code,
                    'expires_minutes' => self::EMAIL_TTL / 60,
                ],
                'queue' => true,
            ]);
        } catch (\Throwable $e) {
            // Email send failed -- still return success so attacker cannot enumerate
        }

        return ['expires_in' => self::EMAIL_TTL];
    }

    public function verifyEmailCode(int $userId, string $code): array
    {
        $key = "2fa_email:{$userId}";
        $data = Cache::get($key);

        if (! $data) {
            return ['success' => false, 'message' => 'Code expired or invalid.'];
        }

        $data['attempts'] = ($data['attempts'] ?? 0) + 1;

        if (hash('sha256', $code) !== $data['code']) {
            if (($data['attempts'] ?? 0) >= self::MAX_EMAIL_ATTEMPTS) {
                Cache::forget($key);

                return ['success' => false, 'message' => 'Too many failed attempts.'];
            }

            Cache::put($key, $data, now()->addSeconds(self::EMAIL_TTL));

            $remaining = self::MAX_EMAIL_ATTEMPTS - $data['attempts'];

            return ['success' => false, 'message' => "Invalid code. {$remaining} attempts remaining."];
        }

        Cache::forget($key);

        return ['success' => true];
    }

    /**
     * @param object $user
     */
    public function status($user): array
    {
        $enabled = (bool) ($user->getMeta('2fa_enabled') ?? false);

        return [
            'enabled' => $enabled,
            'methods' => $enabled ? ['totp', 'email'] : [],
            'totp_secret_set' => (bool) $user->getMeta('2fa_totp_secret'),
        ];
    }

    protected function base32Decode(string $input): string|false
    {
        $input = strtoupper(str_replace('=', '', $input));

        if (! preg_match('/^[A-Z2-7]+$/', $input)) {
            return false;
        }

        $out = [];
        $buffer = 0;
        $bitsLeft = 0;

        foreach (str_split($input) as $c) {
            $idx = strpos(self::BASE32, $c);
            if ($idx === false) {
                return false;
            }
            $buffer = ($buffer << 5) | $idx;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $out[] = chr(($buffer >> $bitsLeft) & 0xff);
            }
        }

        return implode('', $out);
    }
}
