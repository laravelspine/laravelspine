<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Modul berhasil di-install (Perfex: module_installed).
 */
class ModuleInstalled
{
    use Dispatchable;

    public function __construct(
        public string $name,
        public array $data = [],
    ) {}
}
