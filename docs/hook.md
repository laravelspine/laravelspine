# Hooks & Events

Spine uses **Laravel Events** as its extension points. Every event lives in `Spine\Events\` and is dispatched from a
service or controller at the moment the action completes. Modules and consumers
react by registering **listeners** — no core modification required.

This file is the living registry of Spine hooks: **update it every time a hook
is added** (see [Adding a new hook](#adding-a-new-hook)).

## Event registry

| Event | Fires when | Dispatched from | Payload |
|-------|------------|-----------------|---------|
| `Spine\Events\SettingUpdated` | A setting is created or updated | `SettingService::set()` | `Setting $setting`, `bool $created` |
| `Spine\Events\SmsSent` | An SMS was sent | `SmsService::send()` | `string $to`, `string $body`, `?string $driver`, `array $result` |
| `Spine\Events\ModuleInstalled` | A module was installed from a zip | `ModuleController::install()` | `string $name`, `array $data` |
| `Spine\Events\ModuleUninstalled` | A module was uninstalled | `ModuleController::uninstall()` | `string $name`, `bool $purge` |
| `Spine\Events\ModuleActivated` | A module was enabled | `ModuleController::enable()` | `string $name` |
| `Spine\Events\ModuleDeactivated` | A module was disabled | `ModuleController::disable()` | `string $name` |
| `Spine\Events\NotificationSent` | A realtime notification is broadcast to a user (Reverb) | `BroadcastController` | `int $userId`, `string $title`, `string $message`, `string $type`, `array $data` |

## Listening to events

Register listeners in your app's `EventServiceProvider` or inside a module's
provider (nwidart modules: `modules/<Name>/Providers/EventServiceProvider.php`).

```php
// app/Providers/EventServiceProvider.php
use Spine\Events\SettingUpdated;

public function boot(): void
{
    Event::listen(SettingUpdated::class, function (SettingUpdated $event) {
        // invalidate cache, sync downstream, audit, ...
        Cache::forget("setting.{$event->setting->key}");
    });
}
```

Or use a dedicated listener class:

```php
use Spine\Events\SmsSent;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogSmsToAudit implements ShouldQueue
{
    public function handle(SmsSent $event): void
    {
        AuditLog::create(['to' => $event->to, 'driver' => $event->driver]);
    }
}
```

## Adding a new hook

Follow this checklist whenever Spine needs a new extension point:

1. **Create the event class** in `src/Events/` — `use Dispatchable`, public
   readonly constructor properties, no logic.
2. **Dispatch from the service** at the exact point the action completes
   (e.g. after the row is saved, after the driver returns).
3. **Add a row to the registry table** above (this file) — event, trigger
   point, dispatcher, payload.
4. Keep events **synchronous by default**; if listeners must not block the
   request, implement `ShouldQueue` on the listener (not the event).

## Migration reference

Hooks from legacy CRMs map to Laravel primitives as follows:

| Legacy | Spine / Laravel |
|--------|-----------------|
| `hooks()->do_action('name', $args)` | Laravel event (this registry) |
| `hooks()->apply_filters('name', $value)` | `Illuminate\Pipeline` / Eloquent model lifecycle events (per-module) |
| Bootstrap hooks (`application/hooks/`) | `ServiceProvider::boot()` + middleware |
| Auth hooks (`after_staff_login`, ...) | `Illuminate\Auth\Events\Login` / `Logout` / `PasswordReset` |
| Mail hooks (`email_template_sent`, ...) | `Illuminate\Mail\Events\MessageSent` / `MessageSending` |
