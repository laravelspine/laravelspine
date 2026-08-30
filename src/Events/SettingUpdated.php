<?php

namespace Spine\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Spine\Models\Setting;

/**
 * Setting disimpan/diperbarui (Perfex: settings save hooks).
 *
 * Dispatch di SettingService::set() — listener bisa bereaksi
 * (invalidasi cache, sinkronisasi, audit) tanpa mengubah service.
 */
class SettingUpdated
{
    use Dispatchable;

    public function __construct(
        public Setting $setting,
        public bool $created = false,
    ) {}
}
