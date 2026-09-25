<?php

declare(strict_types=1);

namespace Modules\{{Studly}}\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\{{Studly}}\Listeners\Log{{Entity}}Activity;

class {{Studly}}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Http/routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // HOOK — entity lifecycle generic (HasLifecycleHooks):
        // EntityCreated/Updated/Deleted untuk {{Entity}} (entity modul ini).
        Event::listen(\Spine\Events\EntityCreated::class, Log{{Entity}}Activity::class . '@created');
        Event::listen(\Spine\Events\EntityUpdated::class, Log{{Entity}}Activity::class . '@updated');
        Event::listen(\Spine\Events\EntityDeleted::class, Log{{Entity}}Activity::class . '@deleted');
    }
}
