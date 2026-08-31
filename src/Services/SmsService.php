<?php

declare(strict_types=1);

namespace Spine\Services;

use Spine\Services\Sms\LogSmsDriver;
use Spine\Services\Sms\SmsDriver;
use Spine\Services\Sms\TwilioSmsDriver;
use Spine\Events\SmsSent;
use Illuminate\Support\Facades\Config;

/**
 * SmsService — registry and factory for SMS providers.
 *
 * The active driver is selected from config (SMS_DRIVER).
 */
class SmsService
{
    /**
     * @var array<string, SmsDriver>
     */
    private array $drivers = [];

    public function __construct()
    {
        $this->registerDrivers();
    }

    private function registerDrivers(): void
    {
        $drivers = Config::get('sms.drivers', []);

        foreach ($drivers as $name => $cfg) {
            $class = $cfg['driver'] ?? null;
            if (! $class || ! class_exists($class)) {
                continue;
            }

            $this->drivers[$name] = $this->resolve($class, $cfg);
        }
    }

    private function resolve(string $class, array $cfg): SmsDriver
    {
        // Drivers that take credentials in the constructor
        if ($class === TwilioSmsDriver::class) {
            return new $class(
                (string) ($cfg['account_sid'] ?? ''),
                (string) ($cfg['auth_token'] ?? ''),
                (string) ($cfg['from'] ?? '')
            );
        }

        return new $class;
    }

    public function driver(?string $name = null): SmsDriver
    {
        $name = $name ?: (string) Config::get('sms.default', 'log');

        return $this->drivers[$name] ?? $this->drivers['log'] ?? new LogSmsDriver;
    }

    /**
     * List of configured drivers.
     *
     * @return list<string>
     */
    public function availableDrivers(): array
    {
        return array_values(array_filter($this->drivers, fn (SmsDriver $d): bool => $d->isConfigured()));
    }

    /**
     * Send an SMS.
     *
     * @param  array{to: string, body: string, driver?: string|null}  $payload
     */
    public function send(array $payload): array
    {
        $to = $payload['to'] ?? '';
        $body = $payload['body'] ?? '';
        $driver = $payload['driver'] ?? null;

        if ($to === '' || $body === '') {
            return ['success' => false, 'message' => "'to' and 'body' are required"];
        }

        $result = $this->driver($driver)->send($to, $body);

        SmsSent::dispatch($to, $body, $driver, $result);

        return $result;
    }
}
