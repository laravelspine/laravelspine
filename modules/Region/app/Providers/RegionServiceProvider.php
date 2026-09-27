<?php

declare(strict_types=1);

namespace Modules\Region\app\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Region\app\Listeners\LogProvinceActivity;
use Spine\Events\ModuleActivated;

class RegionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Http/routes/api.php');

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // HOOK — entity lifecycle generic (HasLifecycleHooks):
        // EntityCreated/Updated/Deleted untuk Province & Regency.
        Event::listen(\Spine\Events\EntityCreated::class, LogProvinceActivity::class . '@created');
        Event::listen(\Spine\Events\EntityUpdated::class, LogProvinceActivity::class . '@updated');
        Event::listen(\Spine\Events\EntityDeleted::class, LogProvinceActivity::class . '@deleted');

        // HOOK — auto-seed saat module diaktifkan (hanya sekali).
        Event::listen(ModuleActivated::class, function ($event) {
            if (strtolower($event->name ?? '') === 'region') {
                $alreadySeeded = \Modules\Region\app\Models\Province::count() > 0;
                if (! $alreadySeeded) {
                    \Illuminate\Support\Facades\Artisan::call('db:seed', [
                        '--class' => \Modules\Region\database\seeders\RegionSeeder::class,
                    ]);
                }
            }
        });
    }
}