<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Modul dinonaktifkan (Perfex: module_deactivated).
 */
class ModuleDeactivated
{
    use Dispatchable;

    public function __construct(
        public string $name,
    ) {}
}
