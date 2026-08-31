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
| `Spine\Events\NotificationCreating` | Before a notification is broadcast; `$payload` is **mutable** (notification_data filter) — throw to abort | `BroadcastController::sendTest()` | `array $payload` (mutable: userId/title/message/type/data) |
| `Spine\Events\MailSending` | Before an email is sent; `$payload` is **mutable** (email_template_parsed filter) — throw to abort | `MailService::send()` | `array $payload` (mutable: to/subject/view/data/queue) |
| `Spine\Events\DateFormatting` | While a date/datetime is formatted; `$payload` is **mutable** (date-format filters) | `DateService::format()` / `formatDateTime()` / `toSql()` | `array $payload` (mutable: format/formatted/value/sql) |
| `Spine\Events\RelationResolving` | While a relation is resolved; `$payload` is **mutable** (relation data filters) — throw to abort | `RelationService::resolve()` | `array $payload` (mutable: type/id/data) |
| `Spine\Events\FileUploading` | Before a file is stored; listeners may **reject** the upload by throwing | `FileService::storeUpload()` | `UploadedFile $file`, `string $relType`, `int $relId`, `?int $tenantId`, `string $disk` |
| `Spine\Events\FileUploaded` | After a file is stored | `FileService::storeUpload()` | `string $path`, `string $relType`, `int $relId`, `?int $tenantId`, `string $disk` |
| `Spine\Events\FileDeleting` | Before an attachment is deleted; listeners may **reject** the removal by throwing | `FileService::deleteUpload()` | `Attachment $attachment` |
| `Spine\Events\FileDeleted` | After an attachment is deleted (file off disk, row gone) | `FileService::deleteUpload()` | `Attachment $attachment` |
| `Spine\Events\PdfCreating` | Before a PDF is rendered; `$payload` is **mutable** (PDF data filters) — throw to abort | `PdfService::fromHtml()` / `fromView()` | `array $payload` (mutable: html/view/data/paper/orientation) |
| `Spine\Events\PdfCreated` | After a PDF is rendered | `PdfService::fromHtml()` / `fromView()` | `string $binary`, `array $payload` |

> Note: `FileUploading` and `FileDeleting` are veto points — throw
> `ValidationException` inside a listener to abort the operation before the
> file is touched.

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

## Related Laravel primitives

| Pattern | Laravel equivalent |
|---------|--------------------|
| Fire-and-forget action | Laravel event (this registry) |
| Value-mutating filter | `Illuminate\Pipeline` / Eloquent model lifecycle events |
| Bootstrap / startup hook | `ServiceProvider::boot()` + middleware |
| Auth lifecycle | `Illuminate\Auth\Events\Login` / `Logout` / `PasswordReset` |
| Mail lifecycle | `Illuminate\Mail\Events\MessageSent` / `MessageSending` |
