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

    /*
    |--------------------------------------------------------------------------
    | Upload files
    |--------------------------------------------------------------------------
    | FileService::storeUpload() menolak extension yang tidak ada di
    | 'allowed_extensions' (default-deny). Konsumen yang butuh tipe lain
    | overriding list ini, atau menambahkannya lewat listener Spine:FileUploading
    | (dilempar SESUDAH validasi core, jadi listener hanya bisa memperketat).
    |
    | 'blocked_extensions' selalu menang atas 'allowed_extensions' — dipakai
    | untuk tipe yang berbahaya kalau-kalau ikut ter-allow karena salah
    | ketik, atau yang charakter-nya skrip regardless of interpreter.
    |
    | Catatan: nama file di-generate ulang oleh unique_filename(), jadi
    | double-extension seperti "x.php.jpg" tidak bisa terbentuk di disk.
    | Extension tetap dicek per-segmen supaya penolakan terjadi di boundary,
    | bukan karena kebetulan penamaan ulang.
    */
    'files' => [
        /*
        | Cross-check the sniffed content type for image uploads. Hanya
        | berlaku untuk extension gambar — sniffed type untuk dokumen/arsip
        | terlalu sering berbeda legitimately (CSV dari Excel bisa
        | application/vnd.ms-excel, text/plain, atau application/octet-stream;
        | .odt/.docx itu zip), sehingga assertion menyeluruh akan menimbulkan
        | false rejection, bukan menutup lubang.
        |
        | Yang penting justru kasus gambar: file bernama avatar.jpg tapi
        | isinya SVG atau HTML akan dikirim balik ke browser dan dieksekusi
        | (stored XSS), sedangkan allow-list tidak bisa melihatnya karena
        | hanya memeriksa nama yang dipilih client.
        |
        | Set false hanya kalau ada consumer yang memang.sniff tidak bisa
        | dipercaya (mis. file dibuat on-the-fly tanpa header).
        */
        'verify_image_mime' => true,

        'allowed_extensions' => [
            // dokumen
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp',
            'rtf', 'txt', 'csv', 'md',
            // gambar
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'ico', 'tif', 'tiff',
            // arsip
            'zip',
            // data
            'json', 'xml',
        ],

        'blocked_extensions' => [
            // skrip server-side — tidak boleh masuk storage meski disk private
            'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phps', 'pht', 'phtm',
            'phtml', 'phar', 'inc', 'cgi', 'pl', 'py', 'rb', 'sh', 'bash',
            // konfigurasi server
            'htaccess', 'htpasswd', 'ini', 'env',
            // native binary
            'exe', 'dll', 'so', 'bat', 'cmd', 'com', 'msi', 'scr', 'jar',
            // shortcut / aktif
            'js', 'html', 'htm', 'xhtml', 'svg', 'swf',
        ],
    ],
];
