# Laravel Spine

**The modular core for building business applications.**

Spine is an API-first, modular core for Laravel 12. It provides the cross-cutting infrastructure most business applications need — settings, activity logs, custom metadata, file uploads, relations, mail, PDF, SMS, QR codes, Excel import/export, tags, GDPR tooling, payment gateway abstraction, and a module manager — exposed as a versioned REST API (`/api/v1`).

## Quick Links

| Resource | Link |
|----------|------|
| 📖 Documentation | [docs/](./docs/README.md) |
| 🔌 Hooks & Events | [docs/hooks.md](./docs/hooks.md) |
| 🔐 RBAC | [docs/rbac.md](./docs/rbac.md) |
| 📦 Modules | [docs/modules.md](./docs/modules.md) |
| 🚀 Installation | [docs/installation.md](./docs/installation.md) |
| 🔑 Authentication | [docs/authentication.md](./docs/authentication.md) |
| 📡 API Reference | [docs/api.md](./docs/api.md) |
| 💻 GitHub | [github.com/laravelspine/laravelspine](https://github.com/laravelspine/laravelspine) |

## What You Get

| Area | Endpoints (all under `/api/v1`, `auth:sanctum`) |
|------|--------------------------------------------------|
| Settings | `GET/PUT/DELETE /settings/{key}`, `POST /settings/bulk` |
| Activity logs | `GET/POST /activity-logs`, `GET/DELETE /activity-logs/{id}` |
| Custom meta | `GET/POST /meta/{type}/{id}`, `GET/PUT/DELETE /meta/{type}/{id}/{key}` |
| Relations | `GET /relations/types`, `GET /relations/{type}/{id}` |
| Files | `POST /files`, `GET /files/{id}`, download, preview, limits |
| Mail | `POST /mail/send`, `/notify`, `/retry`, `/cleanup`, `GET /mail/queue` |
| PDF | `POST /pdf/generate`, `/from-html`, `/bulk-export` |
| SMS | `POST /sms/send`, `GET /sms/drivers` |
| QR code | `POST /qr-code/generate` |
| Excel | `POST /excel/export`, `/excel/import` |
| Tags | `GET/POST /tags`, `DELETE /tags/{id}` |
| Modules | `GET /modules`, install/enable/disable/uninstall |
| System | `GET /system/languages` |
| Payment | `GET /payment/gateways`, `POST /payment/intent` |
| GDPR | `GET /gdpr/export`, `POST /gdpr/anonymize`, `/gdpr/delete` |
| Broadcasting | `GET /broadcast/config`, `POST /broadcast/test` |

Plus helpers (`Str`, `Number`, `Time`), the `HasMetaData` trait, server-side list API (sort/filter/search/include via `spatie/laravel-query-builder`), and real-time support via Laravel Broadcasting/Reverb.

## Core Principles

1. **The core never contains module code.** Business modules (Sales, CRM, Projects, …) live outside the core and are mounted via [nwidart/laravel-modules](https://github.com/nwidart/laravel-modules).
2. **Modules communicate through events and interfaces**, never direct dependencies. Extension points are documented in [docs/hooks.md](./docs/hooks.md).
3. **API-first and versioned.** Every endpoint lives under `/api/v1`; breaking changes move to `v2` without breaking existing clients.
4. **RBAC by module.** Permissions are declared in each module's `manifest.php`, not hardcoded in the consumer. See [docs/rbac.md](./docs/rbac.md).

## Architecture

```
spine/laravel-spine (Package)
│
├── src/
│   ├── Http/Controllers/    # API controllers (Settings, Files, Mail, etc.)
│   ├── Services/            # Business logic services
│   ├── Events/              # 57+ event classes for hooks
│   ├── Models/              # ActivityLog, CustomMeta, Attachment, etc.
│   ├── Config/              # configs: spine.php, menus.php
│   └── Console/Commands/    # artisan commands (rbac:sync, etc.)
│
├── routes/api.php           # All API routes (106+ endpoints)
├── database/migrations/     # Schema migrations
├── docs/                    # Documentation
│   ├── README.md            # This file
│   ├── hooks.md             # Event registry
│   ├── rbac.md              # RBAC policy
│   ├── modules.md           # Module system
│   ├── installation.md      # Setup guide
│   ├── authentication.md    # Auth integration
│   └── api.md               # API reference
│
└── tests/                   # PHPUnit tests
```

## Requirements

- PHP 8.2+
- Laravel 12
- Composer
- MySQL / PostgreSQL

## Installation

```bash
composer require spine/laravel-spine
php artisan migrate
```

The service provider is auto-discovered. That's it — the `/api/v1/*` endpoints, migrations and routes are loaded automatically.

### Post-Installation

```bash
# Run package migrations
php artisan migrate

# Sync RBAC permissions from all active modules
php artisan spine:rbac:sync

# Verify API endpoints
curl http://your-app.test/api/v1/health
```

## Consumer Setup Notes

### Sanctum

Install automatically as a dependency. Add `Laravel\Sanctum\HasApiTokens` to your `User` model and run:

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --tag=sanctum-migrations
php artisan migrate
```

### 401 Responses

API-only apps should render unauthenticated requests as JSON. In `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->redirectGuestsTo('/api/v1/login');
})
->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->render(function (AuthenticationException $e, Request $request) {
        if ($request->is('api/*')) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
    });
})
```

### Auth

Define your own `login` / `user` endpoints. Spine deliberately does not ship auth endpoints — identity is application-specific.

### Meta Endpoints

`/meta/{type}/{id}`: the entity model must use the `Spine\Traits\HasMetaData` trait. The `user` type is resolved from `config('auth.providers.users.model')`; register additional types by extending the allowlist in `MetaController`.

### Modules

`nwidart/laravel-modules` is included. Put business modules in `modules/<Name>` or ship them as composer packages; mount via `modules_statuses.json`.

## Development

Clone the repo and develop against a consumer app. Fastest iteration loop:

```bash
composer config repositories.spine path /path/to/laravelspine
composer require spine/laravel-spine:@dev
```

## Directory Structure

| Path | Purpose |
|------|---------|
| `src/Http/Controllers/` | API controllers |
| `src/Services/` | Business logic services |
| `src/Events/` | Event classes (hooks) |
| `src/Models/` | Eloquent models |
| `src/Traits/` | Reusable traits |
| `src/Console/Commands/` | Artisan commands |
| `src/Config/` | Package configuration |
| `routes/api.php` | API route definitions |
| `database/migrations/` | Database migrations |
| `docs/` | Documentation |
| `tests/` | PHPUnit test suite |

## Documentation

Full documentation is in the `docs/` directory:

- **[README.md](./docs/README.md)** — This file
- **[hooks.md](./docs/hooks.md)** — Event registry (57+ events)
- **[rbac.md](./docs/rbac.md)** — Role & Permission policy
- **[modules.md](./docs/modules.md)** — Module system guide
- **[installation.md](./docs/installation.md)** — Step-by-step setup
- **[authentication.md](./docs/authentication.md)** — Auth integration
- **[api.md](./docs/api.md)** — API endpoint reference

## Contributing

Contributions are welcome. Before submitting:

1. Run `composer validate` and `php -l` on changed files.
2. Verify against a consumer app: endpoints must respond (`401` unauthenticated, `200` with token).
3. Keep the core generic: no business-domain code, no application-specific terms, no hard-coded hostnames.
4. Add or update Scribe annotations on any new/changed endpoint.

Small, focused PRs are appreciated over large rewrites. Open an issue first if you're unsure whether a change belongs in the core.

## License

MIT
