<?php

declare(strict_types=1);

namespace Spine\Console\Commands;

/**
 * Scaffold ENTITY TAMBAHAN di module yang sudah ada (module multi-entity).
 *
 *   # Entity mandiri (Pola A — lookup/reference, tanpa FK):
 *   php artisan entity:make-spine Category --module=Equipments
 *
 *   # Entity child (Pola B — FK di entity baru, filter ?parent_id):
 *   php artisan entity:make-spine Branch --module=Customers --parent=Customer
 *
 * Yang di-generate: model, migration, controller, listener + APPEND routes
 * dan provider (tidak menimpa file yang sudah ada; idempoten — jalan 2x
 * tidak membuat duplikat). Manifest TIDAK disentuh (kontrak frontend di-review
 * manual).
 */
class MakeSpineEntity extends SpineScaffoldCommand
{
    protected $signature = 'entity:make-spine
        {entity : Nama entity (StudlyCase, contoh: Branch, Category)}
        {--module= : Module target (wajib, contoh: Customers, Equipments)}
        {--parent= : Parent entity utk child (Pola B; contoh: Customer)}
        {--path= : Path target (default base_path("Modules"))}';

    protected $description = 'Scaffold entity tambahan di module yang sudah ada (model + migration + controller + listener, append routes/provider)';

    public function handle(): int
    {
        $module = $this->option('module');
        if (! $module) {
            $this->error('--module= wajib (module target). Contoh: entity:make-spine Branch --module=Customers');
            return self::FAILURE;
        }

        $repl = $this->replacements($module, $this->argument('entity'), $this->option('parent'));
        $studly = $repl['{{Studly}}'];
        $entity = $repl['{{Entity}}'];
        $table = $repl['{{table}}'];

        $base = rtrim($this->option('path') ?: base_path('Modules'), '/');
        $dest = $base . '/' . $studly;

        if (! is_dir($dest)) {
            $this->error("Module not found: {$dest}");
            return self::FAILURE;
        }

        $stubs = $this->stubsDir();

        // 1) Model
        $this->copyFile("{$stubs}/Models/{{Entity}}.php", "{$dest}/Models/{$entity}.php", $repl);

        // 2) Migration (prefix tanggal)
        $migration = "{$dest}/database/migrations/" . date('Y_m_d') . "_000000_create_{$table}_table.php";
        $this->copyFile("{$stubs}/database/migrations/create_{{table}}_table.php", $migration, $repl);

        // 3) Controller
        $this->copyFile(
            "{$stubs}/Http/Controllers/{{Entity}}Controller.php",
            "{$dest}/Http/Controllers/{$entity}Controller.php",
            $repl
        );

        // 4) Listener activity (created/updated/deleted + status_changed)
        $this->copyFile(
            "{$stubs}/Listeners/Log{{Entity}}Activity.php",
            "{$dest}/Listeners/Log{$entity}Activity.php",
            $repl
        );

        // 5) APPEND routes (idempoten: skip kalau controller sudah terdaftar)
        $routesFile = "{$dest}/Http/routes/api.php";
        if (! is_file($routesFile)) {
            $this->copyFile("{$stubs}/Http/routes/api.php", $routesFile, $repl);
        } else {
            $this->appendRoutes($routesFile, $repl);
        }

        // 6) APPEND provider: Event::listen (idempoten)
        $providerFile = "{$dest}/Providers/{$studly}ServiceProvider.php";
        if (is_file($providerFile)) {
            $this->appendProviderListeners($providerFile, $repl);
        }

        $this->info("Entity '{$entity}' scaffolded in module {$studly}");
        $this->line("  Table : {$table}");
        $this->line("  Route : /api/v1/{$repl['{{route}}']}" . ($repl['{{parent_filter}}'] ? " (filter ?" . strtolower((string) preg_replace('/\s+/', '', $this->option('parent'))) . "_id)" : ''));

        return self::SUCCESS;
    }

    /** Salin satu file stub dengan replace placeholder. */
    private function copyFile(string $src, string $dest, array $repl): void
    {
        if (! is_dir(dirname($dest))) {
            mkdir(dirname($dest), 0775, true);
        }
        $content = str_replace(array_keys($repl), array_values($repl), (string) file_get_contents($src));
        file_put_contents($dest, $content);
    }

    /** Append route group baru ke api.php module (di dalam group auth:sanctum). */
    private function appendRoutes(string $file, array $repl): void
    {
        $content = (string) file_get_contents($file);
        if (str_contains($content, $repl['{{Entity}}'] . 'Controller::class')) {
            return; // sudah terdaftar — idempoten
        }

        $block = <<<'PHP'
    Route::prefix('{{route}}')->group(function () {
        Route::get('/', [{{Entity}}Controller::class, 'index']);
        Route::post('/', [{{Entity}}Controller::class, 'store']);
        Route::get('/{id}', [{{Entity}}Controller::class, 'show'])->whereNumber('id');
        Route::put('/{id}', [{{Entity}}Controller::class, 'update'])->whereNumber('id');
        Route::get('/{id}/activity-logs', [{{Entity}}Controller::class, 'activityLogs'])->whereNumber('id');
        Route::delete('/{id}', [{{Entity}}Controller::class, 'destroy'])->whereNumber('id');
    });

PHP;
        $block = str_replace(array_keys($repl), array_values($repl), $block);

        // Sisipkan sebelum penutup group terakhir ("    });" + "\n});")
        $pos = strrpos($content, "    });");
        if ($pos === false) {
            $content .= "\n" . $block;
        } else {
            $content = substr_replace($content, $block, $pos, 0);
        }
        file_put_contents($file, $content);
    }

    /** Append Event::listen di boot() provider — FQCN, tanpa perlu import. */
    private function appendProviderListeners(string $file, array $repl): void
    {
        $content = (string) file_get_contents($file);
        $entity = $repl['{{Entity}}'];
        $studly = $repl['{{Studly}}'];
        $listener = "Log{$entity}Activity";

        if (str_contains($content, $listener)) {
            return; // sudah terdaftar — idempoten
        }

        $listen = "\n        // HOOK entity {$entity} (entity:make-spine)\n"
            . "        Event::listen(\\Spine\\Events\\EntityCreated::class, \\Modules\\{$studly}\\Listeners\\{$listener}::class . '@created');\n"
            . "        Event::listen(\\Spine\\Events\\EntityUpdated::class, \\Modules\\{$studly}\\Listeners\\{$listener}::class . '@updated');\n"
            . "        Event::listen(\\Spine\\Events\\EntityDeleted::class, \\Modules\\{$studly}\\Listeners\\{$listener}::class . '@deleted');\n";

        $needle = "    public function boot(): void\n    {\n";
        $pos = strpos($content, $needle);
        if ($pos === false) {
            $this->warn("Provider boot() tidak ditemukan — tambahkan listener manual: {$listener}");
            return;
        }
        $content = substr_replace($content, $listen, $pos + strlen($needle), 0);
        file_put_contents($file, $content);
    }
}
