<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication policy
    |--------------------------------------------------------------------------
    | Kebijakan auth yang bisa di-override per konsumen.
    | Konsumen nonaktifkan registrasi publik: config/spine.php
    |   'auth' => ['allow_register' => false],
    */
    'auth' => [
        // Izinkan pendaftaran publik via POST /api/v1/auth/register.
        'allow_register' => true,
        // Role yang dianggap super admin (AuthController::formatUser -> is_admin).
        'super_admin_role' => 'admin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Bootstrap admin (AdminUserSeeder)
    |--------------------------------------------------------------------------
    | Credential TIDAK pernah disimpan di repo. Seeder membaca .env consumer:
    |   ADMIN_EMAIL     (default: admin@spine.test)
    |   ADMIN_PASSWORD  (opsional — bila kosong, password acak dicetak sekali)
    | Nilai di bawah hanya fallback supaya seeder tetap jalan di consumer yang
    | belum menambah key itu ke .env-nya.
    */
    'admin' => [
        'email' => env('ADMIN_EMAIL', 'admin@spine.test'),
        'name' => env('ADMIN_NAME', 'Administrator'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Settings access policy
    |--------------------------------------------------------------------------
    | Saat true, endpoint /api/v1/settings/* hanya bisa diakses konsumen yang
    | punya permission settings:view (baca) / settings:edit (tulis) — konsumen
    | wajib men-seed feature 'settings' (spatie). Konsumen tanpa RBAC (mis.
    | spine.lan demo) biarkan false: cukup auth:sanctum.
    */
    'settings' => [
        'restrict' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | RBAC (spatie/laravel-permission)
    |--------------------------------------------------------------------------
    | Guard yang dipakai saat spine:rbac:sync membuat role/permission.
    | Null = ikut config('permission.defaults.guard') → guard auth default.
    | Konsumen API-only (Sanctum) set 'sanctum' — konsisten dgn guard_name
    | model User & role yang dibuat seeder.
    */
    'rbac' => [
        'guard' => null,
        'super_admin_role' => 'admin',
    ],
];
