<?php

declare(strict_types=1);

namespace Modules\Sample\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Sample\Listeners\LogEntityActivity;
use Modules\Sample\Listeners\LogFileActivity;
use Modules\Sample\Listeners\LogSettingChange;
use Modules\Sample\Listeners\SyncSampleStatusFromTasks;

class SampleServiceProvider extends ServiceProvider
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
        // CONTOH HOOK #1 — dengarkan event Spine: FileUploading/FileUploaded
        // (dipicu dari FileService::storeUpload / deleteUpload)
        // ============================================================
        Event::listen(\Spine\Events\FileUploading::class, LogFileActivity::class . '@uploading');
        Event::listen(\Spine\Events\FileUploaded::class, LogFileActivity::class . '@uploaded');
        Event::listen(\Spine\Events\FileDeleting::class, LogFileActivity::class . '@deleting');
        Event::listen(\Spine\Events\FileDeleted::class, LogFileActivity::class . '@deleted');

        // ============================================================
        // CONTOH HOOK #2 — dengarkan event Spine: SettingUpdated
        // (dipicu dari SettingService::set)
        // ============================================================
        Event::listen(\Spine\Events\SettingUpdated::class, LogSettingChange::class);

        // ============================================================
        // CONTOH HOOK #3 — event lifecycle generic (HasLifecycleHooks):
        // EntityCreated/EntityUpdated/EntityDeleted dengan entityType + changes.
        // Satu listener untuk SEMUA entity (bukan 1 hook per entity seperti legacy).
        // ============================================================
        Event::listen(\Spine\Events\EntityCreated::class, LogEntityActivity::class . '@created');
        Event::listen(\Spine\Events\EntityUpdated::class, LogEntityActivity::class . '@updated');
        Event::listen(\Spine\Events\EntityDeleted::class, LogEntityActivity::class . '@deleted');

        // ============================================================
        // CONTOH HOOK #4 — LINTAS MODUL: parent mengikuti status child.
        // Listen event SampleTask (modul SampleTasks) dari dalam modul Sample:
        // semua child done -> parent SampleItem ikut done.
        // Guard class_exists: Sample tetap jalan tanpa SampleTasks ter-install.
        // ============================================================
        if (class_exists(\Modules\SampleTasks\Models\SampleTask::class)) {
            Event::listen(
                [\Spine\Events\EntityCreated::class, \Spine\Events\EntityUpdated::class, \Spine\Events\EntityDeleted::class],
                SyncSampleStatusFromTasks::class . '@sync'
            );
        }
    }
}
