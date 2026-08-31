<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired after a module has been installed from a zip archive.
 */
class ModuleInstalled
{
    use Dispatchable;

    public function __construct(
        public string $name,
        public array $data = [],
    ) {}
}
