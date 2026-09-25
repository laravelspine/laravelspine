<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired BEFORE sending simple email (no template).
 */
class BeforeSendSimpleEmail
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