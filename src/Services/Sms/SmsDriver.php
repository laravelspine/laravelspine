<?php

declare(strict_types=1);

namespace Spine\Services\Sms;

/**
 * SMS driver contract.
 */
interface SmsDriver
{
    /**
     * Send an SMS.
     *
     * @param  string  $to  destination number (international format, e.g. +6****34...)
     * @param  array<string, mixed>  $options
     * @return array{success: bool, message: string, raw?: mixed}
     */
    public function send(string $to, string $body, array $options = []): array;

    /**
     * Whether the driver is configured (credentials available).
     */
    public function isConfigured(): bool;
}
