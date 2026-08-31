<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired after a module has been enabled.
 */
class ModuleActivated
{
    use Dispatchable;

    public function __construct(
        public string $name,
    ) {}
}
