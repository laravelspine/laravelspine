<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired BEFORE dispatching email to recipient.
 *
 * Listener can modify $to, $cc, $bcc arrays.
 */
class SendEmailTemplateTo
{
    use Dispatchable;

    public bool $prevented = false;

    public function __construct(
        public array $to = [],
        public array $cc = [],
        public array $bcc = [],
    ) {}

    public function prevent(): void
    {
        $this->prevented = true;
    }
}