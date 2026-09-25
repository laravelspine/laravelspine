<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Filter event: fired BEFORE email template is sent.
 *
 * Listener can modify $data or call prevent().
 */
class BeforeEmailTemplateSend
{
    use Dispatchable;

    public bool $prevented = false;

    public function __construct(
        public array $data = [],
    ) {}

    public function prevent(): void
    {
        $this->prevented = true;
    }
}