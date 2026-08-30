<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * SMS terkirim (Perfex: sms_trigger_triggered / SMS provider events).
 *
 * Dispatch di SmsService::send() setelah driver selesai.
 */
class SmsSent
{
    use Dispatchable;

    public function __construct(
        public string $to,
        public string $body,
        public ?string $driver = null,
        public array $result = [],
    ) {}
}
