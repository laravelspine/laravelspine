<?php

declare(strict_types=1);

namespace Spine\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;
use Spine\Services\ModuleService;
use Spine\Services\RbacService;

/**
 * Sinkronkan RBAC deklaratif semua modul aktif ke database (idempotent).
 *
 *   php artisan spine:rbac:sync                 semua modul aktif
 *   php artisan spine:rbac:sync --module=Region  satu modul saja
 *
 * Sumber: key 'rbac' di manifest.php tiap modul (kontrak: docs/rbac.md).
 * Jalankan setelah modul baru dipasang / manifest rbac-nya berubah.
 */
class SyncRbacCommand extends Command
{
    protected $signature = 'spine:rbac:sync
        {--module= : Sinkronkan hanya modul ini (default: semua modul aktif)}';

    protected $description = 'Sinkronkan RBAC deklaratif (key rbac di manifest.php) dari modul aktif';

    public function handle(ModuleService $modules, RbacService $rbac): int
    {
        $rolesTable = config('permission.table_names.roles', 'roles');
        if (! Schema::hasTable($rolesTable)) {
            $this->error('Tabel RBAC belum ada — jalankan migrasi spatie dulu (lihat docs/rbac.md).');

            return self::FAILURE;
        }

        $specs = $modules->rbacSpecs($this->option('module'));

        if ($specs === []) {
            $this->info('Tidak ada modul yang mendeklarasikan key "rbac" di manifest.php.');

            return self::SUCCESS;
        }

        $rows = [];
        foreach ($specs as $spec) {
            $stats = $rbac->sync($spec);
            $rows[] = [$spec['module'], $stats['permissions'], $stats['roles'], $stats['grants']];

            foreach ($stats['skipped'] as $skip) {
                $this->warn("  [{$spec['module']}] {$skip}");
            }
        }

        $this->table(['Modul', 'Permissions', 'Roles', 'Grants'], $rows);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->info('RBAC tersinkron. Cache permission dibersihkan.');

        return self::SUCCESS;
    }
}
