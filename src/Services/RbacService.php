<?php

declare(strict_types=1);

namespace Spine\Services;

use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Sinkronisasi RBAC deklaratif dari manifest modul (key 'rbac').
 *
 * Kontrak (lihat docs/rbac.md):
 *   'permissions' => list<string>            permission feature:capability modul ini
 *   'roles'       => list<{name, label?, permissions[]}>
 *                                            permissions boleh '*' (semua milik modul)
 *                                            atau 'feature:*' (prefix) — di-resolve ke
 *                                            permission konkret; selain itu literal.
 *   'grants'      => array<string, list<string>>   beri permission ke role YANG SUDAH ADA
 *                                            (role platform mis. 'staff'); role tak ada = skip.
 *
 * Idempotent: findOrCreate + syncPermissions. Kolom 'label' di tabel roles opsional
 * (konsumen boleh menambahkannya; kalau tidak ada, label diabaikan).
 */
class RbacService
{
    /**
     * @param array{permissions?: list<string>, roles?: list<array{name: string, label?: string, permissions?: list<string>}>, grants?: array<string, list<string>>} $spec
     * @return array{permissions: int, roles: int, grants: int, skipped: list<string>}
     */
    public function sync(array $spec): array
    {
        $stats = ['permissions' => 0, 'roles' => 0, 'grants' => 0, 'skipped' => []];
        $guard = $this->guardName();

        $declared = [];
        foreach ($spec['permissions'] ?? [] as $name) {
            Permission::findOrCreate($name, $guard);
            $declared[] = $name;
            $stats['permissions']++;
        }

        foreach ($spec['roles'] ?? [] as $roleSpec) {
            $role = Role::findOrCreate($roleSpec['name'], $guard);
            $this->applyLabel($role, $roleSpec['label'] ?? null);
            $role->syncPermissions($this->resolve($roleSpec['permissions'] ?? [], $declared, $guard));
            $stats['roles']++;
        }

        foreach ($spec['grants'] ?? [] as $roleName => $permissionNames) {
            try {
                $role = Role::findByName($roleName, $guard);
            } catch (RoleDoesNotExist) {
                $stats['skipped'][] = "grant ke role '{$roleName}' dibatalkan (role tidak ada)";
                continue;
            }
            $role->givePermissionTo($this->resolve($permissionNames, $declared, $guard));
            $stats['grants']++;
        }

        return $stats;
    }

    protected function guardName(): string
    {
        return config('spine.rbac.guard')
            ?: config('permission.defaults.guard')
            ?: config('auth.defaults.guard', 'web');
    }

    /**
     * Resolve daftar permission: '*' = semua permission deklarasi modul;
     * 'feature:*' = semua yang ber-prefix 'feature:'; selain itu literal
     * (findOrCreate — boleh merujuk permission modul lain).
     *
     * @param list<string> $names
     * @param list<string> $declared
     * @return list<PermissionContract>
     */
    protected function resolve(array $names, array $declared, string $guard): array
    {
        $byName = [];

        foreach ($names as $name) {
            $matches = [];
            if ($name === '*') {
                $matches = $declared;
            } elseif (str_ends_with($name, ':*')) {
                $prefix = substr($name, 0, -1); // "customer:"
                $matches = array_values(array_filter(
                    $declared,
                    fn (string $d): bool => str_starts_with($d, $prefix)
                ));
            } else {
                $matches = [$name];
            }

            foreach ($matches as $match) {
                $byName[$match] = Permission::findOrCreate($match, $guard);
            }
        }

        return array_values($byName);
    }

    protected function applyLabel(Role $role, ?string $label): void
    {
        if ($label === null || ! $this->hasLabelColumn()) {
            return;
        }

        if ($role->label !== $label) {
            $role->forceFill(['label' => $label])->save();
        }
    }

    protected function hasLabelColumn(): bool
    {
        return Schema::hasColumn(config('permission.table_names.roles', 'roles'), 'label');
    }
}
