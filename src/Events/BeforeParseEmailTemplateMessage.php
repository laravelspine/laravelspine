<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Filter event: fired BEFORE parsing email template message.
 *
 * Listener can modify $template string.
 */
class BeforeParseEmailTemplateMessage
{
    use Dispatchable;

    public string $template;

    public function __construct(
        string $template,
        public array $data = [],
    ) {
        $this->template = $template;
    }
}