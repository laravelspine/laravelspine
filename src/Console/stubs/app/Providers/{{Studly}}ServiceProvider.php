<?php

declare(strict_types=1);

namespace Modules\{{Studly}}\app\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\{{Studly}}\app\Listeners\Log{{Entity}}Activity;
use Spine\Events\ModuleActivated;

class {{Studly}}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::middleware('api')->prefix('api')->group(function () {
            $this->loadRoutesFrom(__DIR__ . '/../Http/routes/api.php');
        });

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // HOOK — entity lifecycle generic (HasLifecycleHooks):
        // EntityCreated/Updated/Deleted untuk {{Entity}} (entity modul ini).
        Event::listen(\Spine\Events\EntityCreated::class, Log{{Entity}}Activity::class . '@created');
        Event::listen(\Spine\Events\EntityUpdated::class, Log{{Entity}}Activity::class . '@updated');
        Event::listen(\Spine\Events\EntityDeleted::class, Log{{Entity}}Activity::class . '@deleted');

        // HOOK — auto-seed data when module is enabled (only once).
        Event::listen(ModuleActivated::class, function ($event) {
            if (strtolower($event->name ?? '') === '{{studly}}') {
                $alreadySeeded = \Modules\{{Studly}}\app\Models\{{Entity}}::count() > 0;
                if (! $alreadySeeded) {
                    \Illuminate\Support\Facades\Artisan::call('db:seed', [
                        '--class' => \Modules\{{Studly}}\database\seeders\{{Studly}}Seeder::class,
                    ]);
                }
            }
        });
    }
}