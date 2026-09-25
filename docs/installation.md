# Installation Guide

## Requirements

- PHP 8.2+
- Laravel 12
- Composer
- MySQL 5.7+ / PostgreSQL 12+
- Node.js 18+ (optional, for assets)

## Step 1: Install Package

```bash
cd /path/to/your/laravel/app
composer require spine/laravel-spine
```

## Step 2: Run Migrations

```bash
php artisan migrate
```

This creates tables for:
- `activity_logs`
- `attachments`
- `custom_meta`
- `mail_queue`
- `modules`
- `notifications`
- `settings`
- `tags`
- `taggables`

## Step 3: Configure Sanctum

Add `HasApiTokens` to your User model:

```php
// app/Models/User.php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    
    protected $guard_name = 'sanctum';
}
```

Publish and run Sanctum migrations:

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --tag=sanctum-migrations
php artisan migrate
```

## Step 4: Configure API Exception Handling

In `bootstrap/app.php`:

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

## Step 5: Sync RBAC

```bash
php artisan spine:rbac:sync
```

This reads `rbac` declarations from all active module manifests and syncs permissions/roles.

## Step 6: Verify Installation

```bash
# Health check
curl http://your-app.test/api/v1/health

# Should return:
# {"status":"healthy","api":{"status":"healthy"},"db":{"status":"healthy"}}
```

## Step 7: Create Admin User

```bash
php artisan tinker
```

```php
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@example.com';
$user->password = bcrypt('password');
$user->save();
$user->assignRole('admin');
```

## Configuration

Publish config if needed:

```bash
php artisan vendor:publish --provider="Spine\SpineServiceProvider" --tag=config
```

Key config options in `config/spine.php`:

```php
return [
    'rbac' => [
        'guard' => 'sanctum',
        'super_admin_role' => 'admin',
    ],
    'modules' => [
        'directory' => app_path('Modules'),
        'namespace' => 'App\\Modules',
    ],
];
```

## Development Mode

For local development with hot reload:

```bash
# Install dev dependencies
composer install --dev

# Run migrations with seed
php artisan migrate:fresh --seed

# Start development server
php artisan serve
```

## Troubleshooting

### Issue: 401 on all API endpoints
- Ensure Sanctum is installed and configured
- Check that `HasApiTokens` is on the User model
- Verify `SANCTUM_STATEFUL_DOMAINS` in `.env`

### Issue: Missing migration tables
- Run `php artisan migrate --force`
- Clear config cache: `php artisan config:clear`

### Issue: RBAC sync fails
- Ensure spatie/laravel-permission is installed
- Run `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
- Run migrations again

## Next Steps

- Read [Authentication](./authentication.md) for auth setup
- Read [RBAC](./rbac.md) for permission policy
- Read [Hooks & Events](./hooks.md) for extension points
- Read [Modules](./modules.md) for module development
