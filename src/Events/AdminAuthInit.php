<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired at admin login page load (before auth attempt).
 */
class AdminAuthInit
{
    use Dispatchable;

    public function __construct(
        public array $data = [],
    ) {}
}