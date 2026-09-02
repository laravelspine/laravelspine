<?php

declare(strict_types=1);

namespace Spine\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Scaffold modul Spine lengkap (padanan module:make tapi sesuai konvensi
 * project): manifest + routes api/v1 + HasLifecycleHooks + HasUlids +
 * konstanta status + listener activity + migration.
 *
 *   php artisan module:make-spine Blog
 *   php artisan module:make-spine SampleTasks --path=/www/wwwroot/laravelspine/modules
 *
 * Menghasilkan struktur siap-pakai di Modules/<Name>/.
 */
class MakeSpineModule extends Command
{
    protected $signature = 'module:make-spine
        {name : Nama modul (StudlyCase, contoh: Blog, Inspection)}
        {--path= : Path target (default base_path("Modules"))}';

    protected $description = 'Scaffold modul Spine lengkap (manifest + hooks + ulid + status)';

    public function handle(): int
    {
        $name = $this->argument('name');
        $studly = Str::studly($name);
        $studlyLower = Str::lower($studly);
        $entity = Str::singular($studly);
        $entityLower = Str::lower($entity);
        $table = Str::snake(Str::plural($entity));
        $route = Str::kebab(Str::plural($entity));
        $label = Str::headline(Str::plural($entity));

        $base = $this->option('path') ?: base_path('Modules');
        $dest = rtrim($base, '/') . '/' . $studly;

        if (is_dir($dest)) {
            $this->error("Module directory already exists: {$dest}");
            return self::FAILURE;
        }

        $repl = [
            '{{Studly}}' => $studly,
            '{{studly}}' => $studlyLower,
            '{{Entity}}' => $entity,
            '{{entity}}' => $entityLower,
            '{{table}}'  => $table,
            '{{route}}'  => $route,
            '{{label}}'  => $label,
        ];

        $stubs = __DIR__ . '/../stubs';
        $this->copyTree($stubs, $dest, $repl);

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

    /**
     * Salin tree stub ke dest, replace placeholder di konten + nama file.
     */
    private function copyTree(string $src, string $dest, array $repl): void
    {
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($src, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($it as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $rel = substr($file->getPathname(), strlen($src) + 1);
            $rel = str_replace(array_keys($repl), array_values($repl), $rel);
            $out = $dest . '/' . $rel;

            if (! is_dir(dirname($out))) {
                mkdir(dirname($out), 0775, true);
            }

            $content = str_replace(array_keys($repl), array_values($repl), (string) file_get_contents($file->getPathname()));
            file_put_contents($out, $content);
        }
    }
}
