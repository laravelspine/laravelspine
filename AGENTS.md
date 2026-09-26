# AGENTS.md — laravelspine (package `spine/laravel-spine`)

> Catatan operasional untuk agent AI. Diperbarui: 2026-09-26.

## Project
- **Repo**: `https://github.com/laravelspine/laravelspine.git`
- **Nama paket**: `spine/laravel-spine`
- **Fungsi**: package API-only (library) — core platform untuk CRM.
- **Namespace**: `Spine\` → `src/` (PSR-4)
- **Provider**: `Spine\SpineServiceProvider` (auto-discovered via `extra.laravel`)
- **Consumer aktif**: `/www/wwwroot/crm-api.lan` (host Laravel, satu-satunya)

## 🚨 ATURAN PALING PENTING

**Direktori ini adalah SATU-SUMBER package. Tidak boleh ada salinan kedua.**

`.env`-style file non-repo milik pengguna boleh diletakkan di
`/www/wwwroot/` (satu level di atas), **bukan** di dalam repo ini.

Riwayat September 2026 — jangan diulang:
- Pernah ada `/www/wwwroot/laravelspine/public_html/` sebagai clone git terpisah
  (tercatat sebagai gitlink di repo root).
- Host `crm-api.lan` consume symlink ke `public_html/`, bukan ke root.
- Akibatnya **semua edit di root tidak pernah sampai ke host** — harus dicopy
  manual satu per satu, dan sempat ada job yang menulis file ke lokasi yang
  salah.
- Clone tersebut sudah dipindahkan ke `/www/wwwroot/old-laravelspine/` (arsip)
  dan gitlink-nya sudah dilepas dari index repo root.

**Verifikasi cepat** (host harus memuat provider dari direktori ini):
```bash
cd /www/wwwroot/crm-api.lan
php artisan tinker --execute='echo (new ReflectionClass("Spine\SpineServiceProvider"))->getFileName(), PHP_EOL;'
# harus print: /www/wwwroot/laravelspine/src/SpineServiceProvider.php
```

## Tidak ada `artisan` di sini
Ini package, bukan aplikasi. Tidak ada `artisan`, tidak ada `.env`, tidak ada
`config/app.php`. Semua perintah dijalankan dari host:
```bash
cd /www/wwwroot/crm-api.lan
php artisan migrate
php artisan route:list
php artisan tinker
```

## Struktur
| Path | Isi |
|---|---|
| `src/Http/` | Controllers, Middleware, Requests |
| `src/Services/` | Logika domain (TwoFactor, RegisterOtp, IpGuard, Setting, Rbac, dll) |
| `src/Models/` | Model Eloquent inti |
| `src/Events/` | Event class (Laravel Events, bukan custom Hook layer) |
| `src/Traits/` | `HasLifecycleHooks` dan trait reusable |
| `src/Modules/` | Registry & lifecycle modul |
| `src/Console/Commands/` | Artisan command yang di-register package |
| `routes/api.php` | Route API core, dimuat provider dengan prefix `api/v1` |
| `database/migrations/` | Migrasi inti, auto-loaded via `loadMigrationsFrom()` |
| `config/` | Default config (`mergeConfigFrom`) |
| `docs/` | Dokumentasi (`hook.md` = registry event) |
| `modules/` | Modul-sample (`boilerplates`, `sampletasks`) |
| `graphify-out/` | Knowledge graph — pakai `graphify query` untuk pertanyaan codebase |

## Konvensi penulisan
- **Naming**: diamakan dengan repo ini. Contoh yang sudah ada:
  `IpGuardService`, `RegisterOtpService`, `HasLifecycleHooks`, `SpineServiceProvider`.
- **Tipe**: `declare(strict_types=1);` di setiap file.
- **PHPDoc**: Javadoc English di atas property & method publik. Paragraf
  penjelasan memakai Bahasa Indonesia.
- **Hook**: pakai Laravel Events. Custom `Hook` layer dibatalkan — lihat PRD
  `10-hooks-helpers-porting.md`.
- **Redis**: belum di_andalkan. Service yang butuh counter/ban harus punya
  fallback ke database (lihat `IpGuardService` → tabel `ip_bans`).

## Testing & lint
- Dev dependency host **belum terpasang** (`pint`, `phpunit` tidak ada di
  `vendor/bin`) dan jaringan ke packagist timeout. Untuk sementara verifikasi
  pakai `php -l` + `php artisan tinker --execute='...'`.
- JanganClaim test "lulus" tanpa menjalankannya.

## Git
```bash
cd /www/wwwroot/laravelspine
git status
git log --oneline -5
```
Commit & push ke `laravelspine` setelah fitur core selesai.
