<?php

declare(strict_types=1);

namespace Spine\Activators;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Nwidart\Modules\Module;
use Nwidart\Modules\Contracts\ActivatorInterface;

class DatabaseActivator implements ActivatorInterface
{
    private string $table = 'modules';

    public function hasStatus(Module|string $module, bool $status): bool
    {
        if (! $this->tableExists()) {
            return false;
        }

        $name = is_string($module) ? $module : $module->getName();

        $record = DB::table($this->table)
            ->where('name', $name)
            ->first();

        if (!$record) {
            return false;
        }

        return (bool) $record->enabled === $status;
    }

    public function enable(Module $module): void
    {
        $this->setStatus($module->getName(), true);
    }

    public function disable(Module $module): void
    {
        $this->setStatus($module->getName(), false);
    }

    public function setActive(Module $module, bool $active = true): void
    {
        $this->setStatus($module->getName(), $active);
    }

    public function setActiveByName(string $name, bool $active = true): void
    {
        $this->setStatus($name, $active);
    }

    public function setInactive(Module $module): void
    {
        $this->disable($module);
    }

    public function setInactiveByName(string $name): void
    {
        $this->setStatus($name, false);
    }

    public function delete(Module $module): void
    {
        DB::table($this->table)
            ->where('name', $module->getName())
            ->delete();
    }

    public function getAllActive(): array
    {
        if (! $this->tableExists()) {
            return [];
        }

        return DB::table($this->table)
            ->where('enabled', true)
            ->pluck('name')
            ->toArray();
    }

    public function getAllInactive(): array
    {
        if (! $this->tableExists()) {
            return [];
        }

        return DB::table($this->table)
            ->where('enabled', false)
            ->pluck('name')
            ->toArray();
    }

    public function countActive(): int
    {
        if (! $this->tableExists()) {
            return 0;
        }

        return (int) DB::table($this->table)
            ->where('enabled', true)
            ->count();
    }

    public function countInactive(): int
    {
        if (! $this->tableExists()) {
            return 0;
        }

        return (int) DB::table($this->table)
            ->where('enabled', false)
            ->count();
    }

    public function reset(): void
    {
        DB::table($this->table)->delete();
    }

    private function setStatus(string $name, bool $enabled): void
    {
        DB::table($this->table)
            ->updateOrInsert(
                ['name' => $name],
                [
                    'enabled' => $enabled,
                    'updated_at' => now(),
                ]
            );
    }

    /**
     * Read paths run while the framework is still booting, so the table may
     * legitimately not exist yet. That happens on a fresh install, where
     * nwidart's ModuleManifest asks for module status before any migration
     * has run, which otherwise deadlocks `php artisan migrate` with
     * "no such table: modules". Write paths deliberately do not use this:
     * failing loudly on a missing table is correct there.
     *
     * Not memoised on purpose. In tests the provider boots once and
     * RefreshDatabase creates the table afterwards, so a cached false would
     * hide real module state for the rest of the process.
     */
    private function tableExists(): bool
    {
        return Schema::hasTable($this->table);
    }
}
