# Laravel Spine

**The modular core for building business applications.**

API-first, modular core untuk Laravel. Infrastruktur lintas-modul (settings,
activity log, meta, files, relations, mail, pdf, sms, qr-code, excel, tags,
gdpr, payment gateway, module manager) + API versioning v1 + Sanctum + Scribe
docs + list API query-builder + realtime Reverb. Siap dipasangi modul bisnis
via nwidart/laravel-modules.

Core TIDAK pernah berisi kode modul — modul hidup terpisah dan di-mount.

## Install

```bash
composer require spine/laravel-spine
```

## Struktur

```
src/
├── SpineServiceProvider.php
├── Services/          # SettingService, ActivityLogService, FileService, ...
├── Support/Helpers/   # Str, Number, Time
├── Traits/            # HasMetaData
├── Http/Controllers/  # 17 controller API
└── Models/            # Setting, ActivityLog, CustomMeta, Attachment, ...
routes/api.php         # endpoint generik /api/v1/*
database/migrations/   # settings, activity_logs, custom_meta, attachments, tag_tables
```

## Lisensi

MIT
