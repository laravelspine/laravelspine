<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired AFTER email template is parsed (before send).
 */
class EmailTemplateParsed
{
    use Dispatchable;

    public function __construct(
        public string $message,
        public array $data = [],
    ) {}
}