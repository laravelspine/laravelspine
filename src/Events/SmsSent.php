<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired after an SMS has been sent.
 *
 * Dispatched from SmsService::send() once the driver has completed.
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
