<?php

declare(strict_types=1);

namespace Spine;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SpineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // config('pdf.*') = reset defaults; published copy can override.
        $this->mergeConfigFrom(__DIR__.'/../config/pdf.php', 'pdf');
        $this->mergeConfigFrom(__DIR__.'/../config/sms.php', 'sms');
    }

    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadMigrations();
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
        $routes = __DIR__ . '/../routes/api.php';
        if (! is_file($routes)) {
            return;
        }

        Route::middleware('api')->prefix('api')->group(function () use ($routes) {
            $this->loadRoutesFrom($routes);
        });
    }

    private function loadMigrations(): void
    {
        $migrations = __DIR__ . '/../database/migrations';
        if (is_dir($migrations)) {
            $this->loadMigrationsFrom($migrations);
        }
    }
}
