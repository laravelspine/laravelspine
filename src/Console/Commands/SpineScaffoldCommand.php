<?php

declare(strict_types=1);

namespace Spine\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Base class scaffold Spine — shared logic untuk module:make-spine dan
 * entity:make-spine (stubs yang sama, pilihan file berbeda).
 *
 * Placeholder di stubs:
 *   {{Studly}}/{{studly}}  nama module (folder + namespace)
 *   {{Entity}}/{{entity}}  entity (bisa beda dari module, mis. Sample -> SampleItem)
 *   {{table}}/{{route}}    snake/kebab plural dari ENTITY (bukan module)
 *   {{parent_*}}           opsi child (Pola B: FK di entity baru)
 */
abstract class SpineScaffoldCommand extends Command
{
    /**
     * Hitung replacements dari module + entity (opsional override) + parent.
     *
     * @return array<string, string>
     */
    protected function replacements(
        string $module,
        ?string $entity = null,
        ?string $parent = null,
    ): array {
        $studly = Str::studly($module);
        $entity = $entity ? Str::studly($entity) : Str::singular($studly);
        $parentStudly = $parent ? Str::studly($parent) : null;
        $parentSnake = $parentStudly ? Str::snake($parentStudly) : null;
        $parentTable = $parentStudly ? Str::snake(Str::plural($parentStudly)) : null;

        return [
            '{{Studly}}' => $studly,
            '{{studly}}' => Str::lower($studly),
            '{{Entity}}' => $entity,
            '{{entity}}' => Str::lower($entity),
            '{{table}}'  => Str::snake(Str::plural($entity)),
            '{{route}}'  => Str::kebab(Str::plural($entity)),
            '{{label}}'  => Str::headline(Str::plural($entity)),

            // Child (Pola B): FK di entity BARU -> migration, model, controller.
            '{{parent_fk}}'        => $parentSnake
                ? "            \$table->foreignId('{$parentSnake}_id')->constrained('{$parentTable}');"
                : '',
            '{{parent_fillable}}'  => $parentSnake ? ", '{$parentSnake}_id'" : '',
            '{{parent_relation}}'  => $parentSnake
                ? "\n    public function {$parentSnake}(): \\Illuminate\\Database\\Eloquent\\Relations\\BelongsTo\n    {\n        return \$this->belongsTo(\\Modules\\{$studly}\\Models\\{$parentStudly}::class);\n    }"
                : '',
            '{{parent_filter}}'    => $parentSnake
                ? "        if (\$request->has('{$parentSnake}_id')) {\n            \$query->where('{$parentSnake}_id', (int) \$request->query('{$parentSnake}_id'));\n        }"
                : '',
            '{{parent_validation}}' => $parentSnake
                ? "            '{$parentSnake}_id' => ['sometimes', 'integer', 'exists:{$parentTable},id'],"
                : '',
        ];
    }

    protected function stubsDir(): string
    {
        return __DIR__ . '/../stubs';
    }

    /**
     * Salin tree stub ke dest, replace placeholder di konten + nama file.
     */
    protected function copyTree(string $src, string $dest, array $repl): void
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
