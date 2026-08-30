<?php

declare(strict_types=1);

namespace Spine;

use Illuminate\Support\ServiceProvider;

class SpineServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadMigrations();
    }

    /**
     * Routes generik (settings, activity-logs, meta, files, relations, mail,
     * pdf, sms, qr-code, excel, tags, modules, system, payment, number-to-word,
     * gdpr) — dimuat otomatis dari package saat ada.
     */
    private function loadRoutes(): void
    {
        $routes = __DIR__ . '/../routes/api.php';
        if (is_file($routes)) {
            $this->loadRoutesFrom($routes);
        }
    }

    private function loadMigrations(): void
    {
        $migrations = __DIR__ . '/../database/migrations';
        if (is_dir($migrations)) {
            $this->loadMigrationsFrom($migrations);
        }
    }
}
