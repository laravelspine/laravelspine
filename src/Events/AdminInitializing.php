<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired BEFORE admin request boots (after auth, before controller).
 *
 * Listener can modify $context or call prevent().
 */
class AdminInitializing
{
    use Dispatchable;

    public bool $prevented = false;

    public function __construct(
        public mixed $user = null,
        public array $context = [],
    ) {}

    public function prevent(): void
    {
        $this->prevented = true;
    }
}