<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Modul di-uninstall (Perfex: module_uninstalled).
 */
class ModuleUninstalled
{
    use Dispatchable;

    public function __construct(
        public string $name,
        public bool $purge = false,
    ) {}
}
