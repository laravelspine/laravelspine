# Laravel Spine Documentation

Welcome to the Laravel Spine documentation. This is the modular core for building business applications on Laravel 12.

## Getting Started

| Document | Description |
|----------|-------------|
| [Installation](./installation.md) | Step-by-step setup guide |
| [Authentication](./authentication.md) | Sanctum + custom auth endpoints |
| [API Reference](./api.md) | All `/api/v1` endpoints |

## Architecture

| Document | Description |
|----------|-------------|
| [Hooks & Events](./hooks.md) | 57+ extension points |
| [RBAC](./rbac.md) | Role & permission policy |
| [Modules](./modules.md) | Module system & manifest |

## Quick Reference

### Installation
```bash
composer require spine/laravel-spine
php artisan migrate
php artisan spine:rbac:sync
```

### Key Commands
```bash
php artisan spine:rbac:sync        # Sync permissions from modules
php artisan spine:rbac:sync --module=Customers  # Single module
```

### Event Hooks (Partial List)
```php
use Spine\Events\SettingUpdated;
use Spine\Events\FileUploaded;
use Spine\Events\MailSending;

Event::listen(SettingUpdated::class, function ($event) {
    Cache::forget("setting.{$event->setting->key}");
});
```

### RBAC in Module Manifest
```php
'rbac' => [
    'permissions' => ['customer:view', 'customer:create'],
    'roles' => [
        ['name' => 'customer-admin', 'permissions' => ['customer:*']],
    ],
    'grants' => ['staff' => ['customer:view']],
],
```

## Project Structure

```
spine/laravel-spine/
├── src/
│   ├── Http/Controllers/   # API controllers
│   ├── Services/           # Business logic
│   ├── Events/             # 57+ event classes
│   ├── Models/             # Eloquent models
│   ├── Traits/             # Reusable traits
│   └── Console/Commands/   # Artisan commands
├── routes/api.php          # API routes
├── database/migrations/    # Schema
├── src/Config/             # Configuration
└── docs/                   # This directory
```

## Related Projects

| Project | Description |
|---------|-------------|
| [wasnaker/crm-web](https://github.com/wasnaker/crm-web) | Frontend (Laravel + Inertia + React) |
| [wasnaker/crm-api](https://github.com/wasnaker/crm-api) | Backend API consumer |
| [laravelspine/spine](https://github.com/laravelspine/spine) | Minimal consumer app |

## License

MIT
