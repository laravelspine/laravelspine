<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Filter event: resolve email headers (from, reply-to, etc.).
 *
 * Listener returns array of headers.
 */
class EmailTemplateFromHeaders
{
    use Dispatchable;

    public ?array $headers = null;

    public function __construct(
        public array $templateData = [],
    ) {}
}