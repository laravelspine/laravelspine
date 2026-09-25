# Modules

Spine uses [nwidart/laravel-modules](https://github.com/nwidart/laravel-modules) for modular architecture. Business modules live outside the core and are mounted dynamically.

## Module Structure

```
modules/
└── Customers/
    ├── composer.json
    ├── module.json
    ├── manifest.php
    ├── src/
    │   ├── Http/Controllers/
    │   ├── Models/
    │   ├── Services/
    │   └── Providers/
    ├── database/migrations/
    └── routes/
```

## Manifest File

Every module must have a `manifest.php` at the root:

```php
<?php

return [
    'name' => 'Customers',
    'version' => '1.0.0',
    'description' => 'Customer management module',
    
    // RBAC declarations
    'rbac' => [
        'permissions' => [
            'customer:view',
            'customer:create',
            'customer:edit',
            'customer:delete',
        ],
        'roles' => [
            ['name' => 'customer-admin', 'permissions' => ['customer:*']],
        ],
        'grants' => ['staff' => ['customer:view']],
    ],
    
    // Menu entries
    'menu' => [
        ['slug' => 'customers', 'label' => 'Customers', 'icon' => '👥', 'path' => '/customers', 'permission' => 'customer:view'],
    ],
    
    // Settings tabs
    'settings' => [
        [
            'slug' => 'customers',
            'label' => 'Customer Settings',
            'icon' => '🏢',
            'position' => 20,
            'fields' => [
                ['key' => 'customer_prefix', 'label' => 'Customer Prefix', 'type' => 'text'],
            ],
        ],
    ],
    
    // Profile tabs
    'profile_tabs' => [],
    
    // Routes
    'routes' => [
        'web' => 'routes/web.php',
        'api' => 'routes/api.php',
    ],
];
```

## Enabling a Module

Add to `modules_statuses.json`:

```json
{
    "Customers": true,
    "Invoices": true
}
```

Or via command:

```bash
php artisan module:enable Customers
```

## Module Commands

```bash
# List modules
php artisan module:list

# Enable module
php artisan module:enable Customers

# Disable module
php artisan module:disable Customers

# Install from ZIP
php artisan module:install /path/to/module.zip

# Uninstall
php artisan module:uninstall Customers
```

## Module Events

Modules can listen to Spine events:

```php
// src/Providers/EventServiceProvider.php
protected $listen = [
    Spine\Events\ModuleInstalled::class => [
        Listeners\OnModuleInstalled::class,
    ],
    Spine\Events\SettingUpdated::class => [
        Listeners\SyncCustomerSettings::class,
    ],
];
```

## Creating a Module

```bash
php artisan module:make Customers
```

This creates:
```
modules/Customers/
├── composer.json
├── module.json
├── manifest.php
├── src/
│   ├── Http/
│   ├── Models/
│   ├── Services/
│   └── Providers/
├── database/
│   └── migrations/
└── routes/
    ├── api.php
    └── web.php
```

## Module Dependencies

Declare in `composer.json`:

```json
{
    "name": "wasnaker/customers",
    "require": {
        "spine/laravel-spine": "@dev"
    }
}
```

## Module API Routes

```php
// routes/api.php
use App\Modules\Customers\Http\Controllers\CustomerController;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('customers', CustomerController::class);
});
```

## Module Web Routes

```php
// routes/web.php
use App\Modules\Customers\Http\Controllers\CustomerController;

Route::get('/customers', [CustomerController::class, 'index']);
```

## Testing Modules

```bash
# Run module tests
php artisan test --filter=CustomerTest

# Check module status
php artisan module:list
```

## Related

- [RBAC](./rbac.md)
- [Hooks & Events](./hooks.md)
- [Installation](./installation.md)
