<?php

declare(strict_types=1);

namespace Modules\SampleTasks\app\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\SampleTasks\app\Listeners\LogTaskActivity;

class SampleTasksServiceProvider extends ServiceProvider
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
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // HOOK — entity lifecycle generic (HasLifecycleHooks):
        // EntityCreated/EntityUpdated/EntityDeleted dengan entityType + changes.
        Event::listen(\Spine\Events\EntityCreated::class, LogTaskActivity::class . '@created');
        Event::listen(\Spine\Events\EntityUpdated::class, LogTaskActivity::class . '@updated');
        Event::listen(\Spine\Events\EntityDeleted::class, LogTaskActivity::class . '@deleted');
    }
}
