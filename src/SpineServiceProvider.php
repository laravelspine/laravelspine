<?php

declare(strict_types=1);

namespace Spine;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Spine\Console\Commands\MakeSpineEntity;
use Spine\Console\Commands\MakeSpineModule;
use Spine\Console\Commands\SyncCoreRbacCommand;
use Spine\Console\Commands\SyncRbacCommand;

class SpineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // config('pdf.*') = reset defaults; published copy can override.
        $this->mergeConfigFrom(__DIR__.'/../config/pdf.php', 'pdf');
        $this->mergeConfigFrom(__DIR__.'/../config/sms.php', 'sms');
        $this->mergeConfigFrom(__DIR__.'/../config/spine.php', 'spine');

        $this->commands([
            MakeSpineModule::class,
            MakeSpineEntity::class,
            SyncCoreRbacCommand::class,
            SyncRbacCommand::class,
        ]);
    }

    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadMigrations();
        $this->loadViews();
        $this->registerBroadcast();
        $this->registerSpatieMiddleware();
    }

    /**
     * Generic routes (settings, activity-logs, meta, files, relations, mail,
     * pdf, sms, qr-code, excel, tags, modules, system, payment, number-to-word,
     * gdpr) — loaded automatically from the package.
     *
     * Wrapped in the 'api' middleware group with an 'api' prefix (Laravel
     * default); the route file adds a 'v1' prefix so endpoints become /api/v1/*.
     */
    private function loadRoutes(): void
    {
        $routes = __DIR__.'/../routes/api.php';
        if (! is_file($routes)) {
            return;
        }

        Route::middleware('api')->prefix('api')->group(function () use ($routes) {
            $this->loadRoutesFrom($routes);
        });
    }

    /**
     * Realtime — daftarkan auth broadcast (Sanctum) + private channel
     * `user.{id}` untuk event NotificationSent. Endpoint auth jadinya
     * `POST /api/v1/broadcasting/auth` (app API-only; default='web' tak cocok).
     */
    private function registerBroadcast(): void
    {
        Broadcast::routes([
            'middleware' => ['api', 'auth:sanctum'],
            'prefix' => 'api/v1',
        ]);

        $channels = __DIR__.'/../routes/channels.php';
        if (is_file($channels)) {
            require $channels;
        }
    }

    /**
     * Register Spatie permission middleware aliases.
     */
    private function registerSpatieMiddleware(): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('permission', PermissionMiddleware::class);
        $router->aliasMiddleware('role', RoleMiddleware::class);
        $router->aliasMiddleware('role_or_permission', RoleOrPermissionMiddleware::class);
    }

    /**
     * Load package migrations.
     */
    private function loadMigrations(): void
    {
        $migrations = __DIR__.'/../database/migrations';
        if (is_dir($migrations)) {
            $this->loadMigrationsFrom($migrations);
        }
    }

    /**
     * Load package views.
     */
    private function loadViews(): void
    {
        $views = __DIR__.'/../src/Views';
        if (is_dir($views)) {
            $this->loadViewsFrom($views, 'spine');
        }
    }
}
