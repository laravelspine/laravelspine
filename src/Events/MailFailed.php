<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired AFTER email template fails to send.
 */
class MailFailed
{
    use Dispatchable;

    public function __construct(
        public array $mailData,
        public string $error,
    ) {}
}