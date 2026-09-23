<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Filter event: fired AFTER parsing email template message.
 *
 * Listener can modify $message string.
 */
class AfterParseEmailTemplateMessage
{
    use Dispatchable;

    public string $message;

    public function __construct(
        string $message,
        public array $data = [],
    ) {
        $this->message = $message;
    }
}