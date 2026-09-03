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
];
