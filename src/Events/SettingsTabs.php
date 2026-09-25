<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Filter event: collect settings tabs from core + modules.
 *
 * Listener returns array of tab definitions:
 *   ['key' => 'general', 'label' => 'General', 'module' => null, 'permission' => 'settings.view']
 *
 * Return value merged by SettingsService.
 */
class SettingsTabs
{
    use Dispatchable;

    public array $tabs = [];

    public function addTab(array $tab): void
    {
        $this->tabs[] = $tab;
    }
}