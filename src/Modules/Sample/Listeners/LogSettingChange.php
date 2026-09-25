<?php

declare(strict_types=1);

namespace Modules\Sample\Listeners;

use Illuminate\Support\Facades\Log;

/**
 * CONTOH HOOK — listener event SettingUpdated Spine.
 */
class LogSettingChange
{
    public function handle(\Spine\Events\SettingUpdated $event): void
    {
        Log::info('[Sample] setting updated', [
            'key'     => $event->setting->key,
            'value'   => $event->setting->value,
            'created' => $event->created,
            'tenant'  => $event->setting->tenant_id,
        ]);
    }
}
