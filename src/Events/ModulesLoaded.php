<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fired AFTER Nwidart modules are loaded and booted.
 *
 * Modules can register menu items, permissions, settings tabs, cron tasks
 * via listeners on this event.
 */
class ModulesLoaded
{
    use Dispatchable;

    public function __construct(
        public array $modules = [],
    ) {}
}