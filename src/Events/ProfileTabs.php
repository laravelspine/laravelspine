<?php

declare(strict_types=1);

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Filter event: collect profile tabs from core + modules.
 *
 * Mirrors SettingsTabs but for per-user profile settings.
 * Listener returns array of tab definitions:
 *   ['key' => 'general', 'label' => 'General', 'module' => null]
 *
 * Return value merged by SettingController.
 */
class ProfileTabs
{
    use Dispatchable;

    public array $tabs = [];

    public function addTab(array $tab): void
    {
        $this->tabs[] = $tab;
    }
}