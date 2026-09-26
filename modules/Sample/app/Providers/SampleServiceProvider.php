<?php

declare(strict_types=1);

namespace Modules\Sample\app\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Sample\app\Listeners\LogEntityActivity;
use Modules\Sample\app\Listeners\LogFileActivity;
use Modules\Sample\app\Listeners\LogSettingChange;
use Modules\Sample\app\Listeners\SyncSampleStatusFromTasks;

class SampleServiceProvider extends ServiceProvider
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
        $this->loadRoutesFrom(__DIR__ . '/../Http/routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'sample');

        // HOOK #1 — File events
        Event::listen(\Spine\Events\FileUploading::class, LogFileActivity::class . '@uploading');
        Event::listen(\Spine\Events\FileUploaded::class, LogFileActivity::class . '@uploaded');
        Event::listen(\Spine\Events\FileDeleting::class, LogFileActivity::class . '@deleting');
        Event::listen(\Spine\Events\FileDeleted::class, LogFileActivity::class . '@deleted');

        // HOOK #2 — Setting events
        Event::listen(\Spine\Events\SettingUpdated::class, LogSettingChange::class);

        // HOOK #3 — Entity lifecycle events
        Event::listen(\Spine\Events\EntityCreated::class, LogEntityActivity::class . '@created');
        Event::listen(\Spine\Events\EntityUpdated::class, LogEntityActivity::class . '@updated');
        Event::listen(\Spine\Events\EntityDeleted::class, LogEntityActivity::class . '@deleted');

        // HOOK #4 — Cross-module status sync
        if (class_exists(\Modules\SampleTasks\app\Models\SampleTask::class)) {
            Event::listen(
                [\Spine\Events\EntityCreated::class, \Spine\Events\EntityUpdated::class, \Spine\Events\EntityDeleted::class],
                SyncSampleStatusFromTasks::class . '@sync'
            );
        }
    }
}
