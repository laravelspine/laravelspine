# Laravel Spine

**The modular core for building business applications.**

Spine is an API-first, modular core for Laravel. It provides the cross-cutting
infrastructure most business applications need — settings, activity logs,
custom metadata, file uploads, relations, mail, PDF, SMS, QR codes, Excel
import/export, tags, GDPR tooling, payment gateway abstraction, and a module
manager — exposed as a versioned REST API (`/api/v1`), documented with Scribe,
authenticated with Sanctum, and ready to be extended with business modules.

Core principles:

- **The core never contains module code.** Business modules (Sales, CRM,
  Projects, …) live outside the core and are mounted via
  [nwidart/laravel-modules](https://github.com/nwidart/laravel-modules).
- **Modules communicate through events and interfaces**, never direct
  dependencies. The extension points are documented in
  [docs/hook.md](docs/hook.md).
- **API-first and versioned.** Every endpoint lives under `/api/v1`;
  breaking changes move to `v2` without breaking existing clients.

## What you get

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

Plus helpers (`Str`, `Number`, `Time`), the `HasMetaData` trait, server-side
list API (sort/filter/search/include via `spatie/laravel-query-builder`), and
real-time support via Laravel Broadcasting/Reverb.

## Requirements

- PHP 8.2+
- Laravel 12
- Composer

## Installation

```bash
composer require spine/laravel-spine
php artisan migrate
```

The service provider is auto-discovered. That's it — the `/api/v1/*`
endpoints, migrations and routes are loaded automatically.

Consumer setup notes (per application):

- **Sanctum**: installed automatically as a dependency. Add
  `Laravel\Sanctum\HasApiTokens` to your `User` model and run
  `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --tag=sanctum-migrations && php artisan migrate`.
- **401 responses**: API-only apps should render unauthenticated requests as
  JSON. In `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    // must point somewhere — the exception render below produces the response
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

- **Auth**: define your own `login` / `user` endpoints (e.g. Sanctum token
  issue). Spine deliberately does not ship auth endpoints — identity is
  application-specific.
- **Meta endpoints** (`/meta/{type}/{id}`): the entity model must use the
  `Spine\Traits\HasMetaData` trait. The `user` type is resolved from
  `config('auth.providers.users.model')`; register additional types by
  extending the allowlist in `MetaController`.
- **Modules**: `nwidart/laravel-modules` is included. Put business modules in
  `modules/<Name>` or ship them as composer packages; mount via
  `modules_statuses.json`.

## Development

Clone the repo and develop against the consumer app of your choice (a
`composer repositories.path` entry is the fastest loop):

```bash
composer config repositories.spine path /path/to/laravelspine
composer require spine/laravel-spine:@dev
```

## Contributing

Contributions are welcome — this project was built so that others can build
on it and, hopefully, send a patch someday. Before submitting:

1. Run `composer validate` on the package and `php -l` on changed files.
2. Verify against the consumer app: endpoints must respond
   (`401` unauthenticated, `200` with a Sanctum token).
3. Keep the core generic: no business-domain code, no application-specific
   terms, no hard-coded hostnames.
4. Add or update Scribe annotations on any new/changed endpoint.

Small, focused PRs are appreciated over large rewrites. Open an issue first
if you are unsure whether a change belongs in the core.

## License

MIT
