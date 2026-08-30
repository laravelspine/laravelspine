<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Modul diaktifkan (Perfex: module_activated).
 */
class ModuleActivated
{
    use Dispatchable;

    public function __construct(
        public string $name,
    ) {}
}
