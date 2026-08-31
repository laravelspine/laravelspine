<?php

declare(strict_types=1);

namespace Spine\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Contracts\RepositoryInterface;
use Nwidart\Modules\Module;
use ZipArchive;

/**
 * ModuleService — a wrapper around nwidart/laravel-modules.
 *
 * Provides a simple API to:
 * - List modules with status (enabled/disabled/installed)
 * - Get module details
 * - Check whether a module is enabled
 * - Install a module from a zip upload / uninstall (optional purge)
 */
class ModuleService
{
    public function __construct(
        private RepositoryInterface $modules,
        private ActivatorInterface $activator
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        /** @var array<int, Module> $items */
        $items = $this->modules->all();

        return array_map(function (Module $module): array {
            return $this->mapModule($module);
        }, $items);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function enabled(): array
    {
        /** @var array<int, Module> $items */
        $items = $this->modules->allEnabled();

        return array_map(function (Module $module): array {
            return $this->mapModule($module);
        }, $items);
    }

    public function find(string $name): ?array
    {
        $module = $this->modules->find($name);

        if (!$module) {
            return null;
        }

        return $this->mapModule($module);
    }

    public function isEnabled(string $name): bool
    {
        return $this->modules->isEnabled($name);
    }

    public function isDisabled(string $name): bool
    {
        return $this->modules->isDisabled($name);
    }

    public function enable(string $name): bool
    {
        $module = $this->modules->find($name);

        if (!$module) {
            return false;
        }

        $module->enable();

        return true;
    }

    public function disable(string $name): bool
    {
        $module = $this->modules->find($name);

        if (!$module) {
            return false;
        }

        $module->disable();
        return true;
    }

    public function getPath(string $name): ?string
    {
        $module = $this->modules->find($name);

        return $module?->getPath();
    }

    /**
     * Install a module from a zip file.
     *
     * The zip must contain a `module.json` (at the zip root or inside a single
     * top-level folder). The module is extracted to `modules/<Name>/`, enabled
     * right away, then the module migrations are run (if any).
     *
     * @return array<string, mixed>|null module details, null on failure
     */
    public function installFromZip(string $zipPath): ?array
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            return null;
        }

        $moduleJsonEntry = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if (str_ends_with($entry, 'module.json')) {
                $moduleJsonEntry = $entry;
                break;
            }
        }

        if ($moduleJsonEntry === null) {
            $zip->close();

            return null;
        }

        $meta = json_decode((string) $zip->getFromName($moduleJsonEntry), true);
        $moduleName = is_array($meta) ? ($meta['name'] ?? null) : null;

        if (! is_string($moduleName) || $moduleName === '') {
            $zip->close();

            return null;
        }

        $targetDir = base_path('modules') . DIRECTORY_SEPARATOR . $moduleName;

        if (is_dir($targetDir)) {
            $zip->close();

            return null; // already installed
        }

        // Stage INSIDE modules/ (same filesystem → moveDirectory/rename is safe;
        // /tmp may be a different FS → EXDEV). modules/ must be writable by the FPM user.
        $staging = base_path('modules') . DIRECTORY_SEPARATOR . '.staging-' . uniqid('mod_', true);
        if (! File::makeDirectory($staging, 0755, true)) {
            $zip->close();

            return null;
        }

        $zip->extractTo($staging);
        $zip->close();

        // If the zip contains a single top-level folder, move its contents;
        // otherwise move everything straight to the target.
        $entries = array_values(array_diff(scandir($staging), ['.', '..']));
        if (count($entries) === 1 && is_dir($staging . '/' . $entries[0])) {
            File::moveDirectory($staging . '/' . $entries[0], $targetDir);
        } else {
            File::moveDirectory($staging, $targetDir);
        }
        File::deleteDirectory($staging, true);

        if (! is_file($targetDir . '/module.json')) {
            File::deleteDirectory($targetDir, true);

            return null;
        }

        // nwidart v12: Module::getPriority() is strict-typed → TypeError if
        // 'priority' is missing from module.json (a broken module could 500
        // every list). Normalize to a default of '0' so any zip can be installed.
        $moduleJsonPath = $targetDir . '/module.json';
        $moduleMeta = json_decode((string) file_get_contents($moduleJsonPath), true);
        if (is_array($moduleMeta) && ! isset($moduleMeta['priority'])) {
            $moduleMeta['priority'] = '0';
            file_put_contents($moduleJsonPath, json_encode($moduleMeta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
        }

        // Reset nwidart's static cache FIRST, then setEnabled — otherwise
        // find() inside setEnabled still sees the old module list (without
        // the new module) → enable becomes a no-op.
        $this->clearModuleCache();
        $this->setEnabled($moduleName, true);

        try {
            Artisan::call('module:migrate', ['module' => $moduleName]);
        } catch (\Throwable) {
            // a module without migrations / failed migrations must not fail the install
        }

        return $this->find($moduleName);
    }

    /**
     * Uninstall a module: disable it; with $purge=true the directory is removed too.
     */
    public function uninstall(string $name, bool $purge = false): bool
    {
        $module = $this->modules->find($name);

        if (! $module) {
            return false;
        }

        $this->setEnabled($name, false);

        if ($purge) {
            $path = $module->getPath();
            if (is_dir($path)) {
                File::deleteDirectory($path);
            }
            $this->removeFromStatuses($name);
        }

        $this->clearModuleCache();

        return true;
    }

    /**
     * nwidart v12 caches the module list in the STATIC FileRepository::$modules
     * property (used by scan() across requests) plus the bootstrap/cache/modules.php
     * file. After install/uninstall BOTH must be cleared, or the module list goes
     * stale: find() → null (422), list contains deleted modules.
     */
    private function clearModuleCache(): void
    {
        @unlink(base_path('bootstrap/cache/modules.php'));

        try {
            $prop = new \ReflectionProperty(\Nwidart\Modules\FileRepository::class, 'modules');
            $prop->setValue(null, []); // static property → object arg is ignored
        } catch (\Throwable) {
            // other nwidart versions — ignore
        }
    }

    /**
     * Set enabled/disabled status via the nwidart activator (in-memory + file).
     * Do NOT write modules_statuses.json directly — FileActivator keeps status
     * in per-process memory; writing manually makes hasStatus() stale.
     */
    private function setEnabled(string $name, bool $enabled): void
    {
        $module = $this->modules->find($name);
        if (! $module) {
            return;
        }

        $enabled ? $this->activator->enable($module) : $this->activator->disable($module);
    }

    private function removeFromStatuses(string $name): void
    {
        $module = $this->modules->find($name);
        if ($module) {
            $this->activator->delete($module);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function mapModule(Module $module): array
    {
        // nwidart v12: Module::json() THROWS FileNotFoundException if the file
        // is missing (instead of returning null) — malformed modules must not 500.
        try {
            $composerJson = $module->json('composer.json');
            $composer = $composerJson ? $composerJson->all() : [];
        } catch (\Throwable) {
            $composer = [];
        }
        $namespace = null;
        if (is_array($composer) && isset($composer['autoload']['psr-4'])) {
            $namespace = array_keys($composer['autoload']['psr-4'])[0] ?? null;
        }

        // nwidart v12 is strict-typed: getPriority() TypeErrors if the key is missing.
        $priority = '0';
        try {
            $priority = $module->getPriority();
        } catch (\Throwable) {
        }
        $priority = is_string($priority) ? $priority : '0';

        return [
            'name'       => $module->getName(),
            'studly'     => $module->getStudlyName(),
            'lower'      => $module->getLowerName(),
            'path'       => $module->getPath(),
            'namespace'  => $namespace,
            'enabled'    => $module->isEnabled(),
            'description' => $module->getDescription(),
            'priority'   => $module->getPriority(),
            'providers'  => $module->get('providers', []),
            'aliases'    => $module->get('aliases', []),
        ];
    }
}
