<?php

declare(strict_types=1);

namespace Spine\Console\Commands;

/**
 * Scaffold modul Spine lengkap (padanan module:make tapi sesuai konvensi
 * project): manifest + routes api/v1 + HasLifecycleHooks + HasUlids +
 * konstanta status + listener activity + migration.
 *
 *   php artisan module:make-spine Blog
 *   php artisan module:make-spine Sample --entity=SampleItem   (entity beda module)
 *   php artisan module:make-spine SampleTasks --path=/www/wwwroot/laravelspine/modules
 *
 * Menghasilkan struktur siap-pakai di Modules/<Name>/.
 */
class MakeSpineModule extends SpineScaffoldCommand
{
    protected $signature = 'module:make-spine
        {name : Nama modul (StudlyCase, contoh: Blog, Inspection)}
        {--entity= : Nama entity (default: singular nama modul; contoh: SampleItem)}
        {--path= : Path target (default base_path("Modules"))}';

    protected $description = 'Scaffold modul Spine lengkap (manifest + hooks + ulid + status)';

    public function handle(): int
    {
        $name = $this->argument('name');
        $repl = $this->replacements($name, $this->option('entity'));

        $studly = $repl['{{Studly}}'];
        $entity = $repl['{{Entity}}'];
        $table = $repl['{{table}}'];
        $route = $repl['{{route}}'];

        $base = $this->option('path') ?: base_path('Modules');
        $dest = rtrim($base, '/') . '/' . $studly;

        if (is_dir($dest)) {
            $this->error("Module directory already exists: {$dest}");
            return self::FAILURE;
        }

        $this->copyTree($this->stubsDir(), $dest, $repl);

        // Migration filename: prefix tanggal (format: Y_m_d_000000_).
        $stubMig = "{$dest}/database/migrations/create_{$table}_table.php";
        if (is_file($stubMig)) {
            rename(
                $stubMig,
                "{$dest}/database/migrations/" . date('Y_m_d') . "_000000_create_{$table}_table.php"
            );
        }

        $this->info("Module '{$studly}' scaffolded at {$dest}");
        $this->line("  Entity : {$entity}");
        $this->line("  Table  : {$table}");
        $this->line("  Route  : /api/v1/{$route}");
        $this->line('Next: cd Modules/' . $studly . ' && git init && git add -A && git commit -m "..."');

        return self::SUCCESS;
    }
}
