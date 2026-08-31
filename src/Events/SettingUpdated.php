<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Spine\Models\Setting;

/**
 * Fired when a setting is created or updated.
 *
 * Dispatched from SettingService::set() so listeners can react
 * (cache invalidation, downstream sync, auditing) without touching the service.
 */
class SettingUpdated
{
    use Dispatchable;

    public function __construct(
        public Setting $setting,
        public bool $created = false,
    ) {}
}
