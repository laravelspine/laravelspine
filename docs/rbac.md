# RBAC — Role & Permission

Kebijakan akses platform berbasis **permission** (spatie/laravel-permission),
dideklarasikan **oleh modul** lewat manifest — bukan hardcoded di konsumen.

## Kebijakan

1. **Gate = permission, bukan role.** Menu (key `permission` item menu),
   halaman, dan route memakai permission (`permission:feature:capability`
   middleware / `$user->can()`). Role hanya wadah permission.
2. **Permission diberikan ke role.** User mendapat akses lewat role-nya
   (`assignRole`). Direct permission ke user (`givePermissionTo` pada User)
   hanya untuk kasus khusus — jangan jadi pola.
3. **Role `admin` = super-admin.** Konsumen memakai
   `Gate::before` → `hasRole(config('spine.auth.super_admin_role', 'admin'))`
   (lolos semua check) — lihat pola `AppServiceProvider` produk Wasnaker.
4. **Modul mendeklarasikan RBAC-nya sendiri** di `manifest.php` (key `rbac`).
   Konsumen tidak menambah daftar permission modul ke seeder inti.
5. **Permission = hak fungsi; scope data di luar RBAC.** Dua user sama-sama
   `customer:view` bisa melihat himpunan data berbeda karena *environment*
   (relasi user → customer/branch). Mekanisme scope data bukan urusan
   spatie — lihat [Scope & environment](#scope--environment-data) di bawah.
   (Warisan pola ini di Perfex: `customer_admins`, capability `view` vs
   `view_own`, filter relasi per-baris di `relation_helper.php`.)

## Setup konsumen

`spine/laravel-spine` me-require spatie/laravel-permission — konsumen cukup:

```bash
composer update spine/laravel-spine
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"  # config + migration
php artisan migrate
```

1. Model `User`: `use HasRoles` + `protected $guard_name = 'sanctum'`
   (konsumen API-only). Role/permission dibuat dengan guard sama.
2. `config/spine.php` konsumen:
   ```php
   'rbac' => ['guard' => 'sanctum'],   // dipakai spine:rbac:sync
   ```
3. (Opsional) role platform & seeder dasar — contoh `RolePermissionSeeder`:
   buat role `admin` + `staff`, `admin` disinkron semua permission
   (`syncPermissions(Permission::all())`) — jalankan sekali setelah modul
   RBAC pertama di-sync agar admin kebagian permission baru.
4. Middleware `permission:...` / `role:...` terdaftar otomatis oleh spatie.

> Konsumen TANPA RBAC tidak terpengaruh: modul yang manifest-nya memakai
> permission hanya efektif di konsumen ber-spatie. Konsumen yang tidak
> mengaktifkan RBAC sebaiknya tidak memasang modul ber-gate permission.

## Kontrak manifest — key `rbac`

```php
// manifest.php modul Region (contoh minimal — read-only)
'rbac' => [
    'permissions' => ['region:view'],
    'roles'       => [],
    'grants'      => ['staff' => ['region:view']],
],
```

```php
// manifest.php modul Customers (contoh lengkap)
'rbac' => [
    'permissions' => [
        'customer:view', 'customer:create', 'customer:edit', 'customer:delete',
    ],
    'roles' => [
        ['name' => 'customer',             'label' => 'Customer',
         'permissions' => ['customer:view']],
        ['name' => 'customer-branch-admin','label' => 'Customer Branch Admin',
         'permissions' => ['customer:*']],
        ['name' => 'customer-admin',       'label' => 'Customer Admin',
         'permissions' => ['customer:*']],
    ],
    // Link ke role platform yang SUDAH ADA (staff internal lihat semua customer).
    'grants' => ['staff' => ['customer:view']],
],
```

Aturan:

| Key           | Isi                                                                 |
|---------------|---------------------------------------------------------------------|
| `permissions` | Daftar permission modul, format `feature:capability`                |
| `roles`       | Role baru modul. `name` = identifier spatie (slug); `label` = tampilan UI (kolom opsional `roles.label`, abaikan bila tidak ada). `permissions`: `'*'` = semua permission modul, `'feature:*'` = semua ber-prefix, selain itu literal (boleh permission modul lain). |
| `grants`      | `roleYangSudahAda => [permission...]`. Role yang belum ada **di-skip dengan peringatan** — jangan jadikan grants sebagai tempat membuat role. |

Nama permission & role bersifat **global** (satu registry per database):
prefix `feature:` milik modul = namespace modul. Jangan mendeklarasikan
permission modul lain.

## Sinkronisasi

```bash
php artisan spine:rbac:sync                 # semua modul aktif
php artisan spine:rbac:sync --module=Region # satu modul
```

Idempotent (`findOrCreate` + `syncPermissions`). Jalankan saat:
modul baru dipasang, manifest `rbac` berubah, atau sehabis `migrate:fresh`.
Role `admin` super-admin tidak disentuh command ini (urusan seeder konsumen).

## Scope & environment (data)

Permission tidak membedakan "semua customer" vs "customer sendiri" — yang
membedakan adalah data relasi user:

- user internal (staff) dengan `customer:view` → seluruh customer;
- user customer (`customer-admin` / `customer-branch-admin`) terikat
  `customer_id` (+ `branch_id`) → query otomatis dibatasi environment-nya.

Implementasi scope data dibahas terpisah (multitenancy/environment layer);
RBAC hanya menjamin *hak fungsi*. Asal-usul pola: Perfex `customer_admins`
(staff ↔ customer) + `has_permission(feature, '', 'view_own')` + filter
relasi di `application/helpers/relation_helper.php` (`init_relation_options`).

## Catatan operasional

- Perubahan RBAC via command langsung di DB (mis. tinker) **tidak tercatat
  di kode** — utamakan deklarasi di manifest + `spine:rbac:sync`.
- Audit "user dapat direct permission" (di luar kebijakan):
  `User::with('permissions')->get()` / `$user->getDirectPermissions()`.
- Cache spatie di-forget otomatis oleh `spine:rbac:sync`.
