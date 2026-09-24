<?php

declare(strict_types=1);

namespace Modules\SampleTasks\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\SampleTasks\Listeners\LogTaskActivity;

class SampleTasksServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Http/routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // ============================================================
        // HOOK — entity lifecycle generic (HasLifecycleHooks) untuk
        // SampleTask (child entity): created/updated/deleted + status-change.
        // Satu set event, listener filter entityType/instanceof.
        // ============================================================
        Event::listen(\Spine\Events\EntityCreated::class, LogTaskActivity::class . '@created');
        Event::listen(\Spine\Events\EntityUpdated::class, LogTaskActivity::class . '@updated');
        Event::listen(\Spine\Events\EntityDeleted::class, LogTaskActivity::class . '@deleted');
    }
}
