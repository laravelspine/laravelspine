<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired after a module has been disabled.
 */
class ModuleDeactivated
{
    use Dispatchable;

    public function __construct(
        public string $name,
    ) {}
}
