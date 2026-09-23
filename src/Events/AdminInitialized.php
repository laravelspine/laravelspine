<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired AFTER admin request boots successfully.
 *
 * Generic hook for modules to register menu items, settings tabs, etc.
 */
class AdminInitialized
{
    use Dispatchable;

    public function __construct(
        public mixed $user = null,
        public array $context = [],
    ) {}
}