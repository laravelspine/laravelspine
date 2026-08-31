<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired after a module has been uninstalled.
 */
class ModuleUninstalled
{
    use Dispatchable;

    public function __construct(
        public string $name,
        public bool $purge = false,
    ) {}
}
