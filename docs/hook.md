# Hooks & Events

Spine uses **Laravel Events** as its extension points. Every event class lives in
`Spine\Events\`. Consumers and modules react by registering **listeners** — no
core modification required.

This file is the living registry of Spine hooks: **update it every time a hook
is added** (see [Adding a new hook](#adding-a-new-hook)).

## ⚠️ Read this first: class ≠ wired hook

An event class existing in `src/Events/` does **not** mean the hook is live.
Perfex had a broad hook surface; when porting, every class was kept for
API compatibility, but the dispatch sites were only wired where the core
actually performs the action.

Current state — **57 event classes**:

| Status | Count |
|---|---|
| ✅ Wired — a real dispatch site exists in core | **26** |
| ⚠️ Not wired — class exists, **no dispatch site** | **31** |

A listener on a ⚠️ event will simply never fire. That is a known gap, not a
regression; the PRD tracks wiring the remaining ones (`docs/prd/03-requirements.md`,
hook section). The **Status** column below is authoritative — it was verified by
grepping `src/` and `routes/` for each class name.

## Event Registry

### Core services

| Event | Status | Fires when | Dispatched from | Payload |
|-------|--------|------------|-----------------|---------|
| `SettingUpdated` | ✅ | A setting is created or updated | `src/Services/SettingService.php:34,45` | `Setting $setting`, `bool $created` |
| `SmsSent` | ✅ | An SMS was sent | `src/Services/SmsService.php:113` | `string $to`, `string $body`, `?string $driver`, `array $result` |
| `MailSending` | ✅ | Before an email is sent; `$payload` **mutable** — throw to abort | `src/Services/MailService.php:32` | `array $payload` (mutable: to/subject/view/data/queue) |
| `MailTesting` | ✅ | Before an SMTP test email; `$payload` **mutable** — throw to abort | `src/Services/MailService.php:72` | `array $payload` (mutable: to/subject/body) |
| `MailTested` | ✅ | After an SMTP test attempt | `src/Services/MailService.php:81,91` | `bool $success`, `?string $error` |
| `MailFailed` | ⚠️ | Intended: after an email fails to send | — not wired | `array $mailData`, `string $error` |
| `DateFormatting` | ✅ | While a date/datetime is formatted; `$payload` **mutable** | `src/Services/DateService.php:35,62` | `array $payload` (mutable: format/formatted/value/sql) |
| `RelationResolving` | ✅ | While a relation is resolved; `$payload` **mutable** — throw to abort | `src/Services/RelationService.php:78` | `array $payload` (mutable: type/id/data) |
| `FileUploading` | ✅ | Before a file is stored — throw to reject | `src/Services/FileService.php:174` | `UploadedFile $file`, `string $relType`, `int $relId`, `?int $tenantId`, `string $disk` |
| `FileUploaded` | ✅ | After a file is stored | `src/Services/FileService.php:181` | `string $path`, `string $relType`, `int $relId`, `?int $tenantId`, `string $disk` |
| `FileDeleting` | ✅ | Before an attachment is deleted — throw to reject | `src/Services/FileService.php:195` | `Attachment $attachment` |
| `FileDeleted` | ✅ | After an attachment is deleted | `src/Services/FileService.php:200` | `Attachment $attachment` |
| `PdfCreating` | ✅ | Before a PDF is rendered; `$payload` **mutable** — throw to abort | `src/Services/PdfService.php:68,92` | `array $payload` (mutable: html/view/data/paper/orientation) |
| `PdfCreated` | ✅ | After a PDF is rendered | `src/Services/PdfService.php:80,106` | `string $binary`, `array $payload` |

### Entity lifecycle (via `HasLifecycleHooks` trait)

Fires for any model that `use`s the trait. Sample modules `Sample` and
`SampleTasks` already do.

| Event | Status | Fires when | Dispatched from | Payload |
|-------|--------|------------|-----------------|---------|
| `EntityCreating` | ✅ | **Before** create (Eloquent `creating`) — veto point | `src/Traits/HasLifecycleHooks.php:54` | `string $entityType`, `array $attributes` |
| `EntityCreated` | ✅ | **After** create (Eloquent `created`) | `src/Traits/HasLifecycleHooks.php:61` | `string $entityType`, `Model $entity` |
| `EntityUpdating` | ✅ | **Before** update (Eloquent `updating`) — veto point | `src/Traits/HasLifecycleHooks.php:65` | `string $entityType`, `Model $entity`, `array $changes` |
| `EntityUpdated` | ✅ | **After** update (Eloquent `updated`) | `src/Traits/HasLifecycleHooks.php:72` | `string $entityType`, `Model $entity`, `array $changes` |
| `EntityDeleting` | ✅ | **Before** delete (Eloquent `deleting`) — veto point | `src/Traits/HasLifecycleHooks.php:76` | `string $entityType`, `Model $entity` |
| `EntityDeleted` | ✅ | **After** delete (Eloquent `deleted`) | `src/Traits/HasLifecycleHooks.php:83` | `string $entityType`, `Model $entity` |

### Modules

| Event | Status | Fires when | Dispatched from | Payload |
|-------|--------|------------|-----------------|---------|
| `ModuleInstalled` | ✅ | A module was installed from a zip | `src/Http/Controllers/ModuleController.php:356` | `string $name`, `array $data` |
| `ModuleUninstalled` | ✅ | A module was uninstalled | `src/Http/Controllers/ModuleController.php:385` | `string $name`, `bool $purge` |
| `ModuleActivated` | ✅ | A module was enabled | `src/Http/Controllers/ModuleController.php:267` | `string $name` |
| `ModuleDeactivated` | ✅ | A module was disabled | `src/Http/Controllers/ModuleController.php:293` | `string $name` |
| `ModulesLoaded` | ⚠️ | Intended: after Nwidart modules load & boot | — not wired | `array $modules` |

### Notifications

| Event | Status | Fires when | Dispatched from | Payload |
|-------|--------|------------|-----------------|---------|
| `NotificationCreating` | ✅ | Before a realtime notification is broadcast; `$payload` **mutable** — throw to abort | `src/Http/Controllers/BroadcastController.php:54` | `array $payload` (mutable: userId/title/message/type/data) |
| `NotificationSent` | ✅ | A realtime notification was broadcast | `src/Http/Controllers/BroadcastController.php:58` | `int $userId`, `string $title`, `string $message`, `string $type`, `array $data` |

### Authentication

| Event | Status | Fires when | Dispatched from | Payload |
|-------|--------|------------|-----------------|---------|
| `StaffLogIn` | ✅ ⚠️ | **Only on self-registration.** Not yet wired to `AuthController::login()` | `src/Services/RegisterOtpService.php:119` | `mixed $staff`, `?string $token`, `array $meta` |
| `StaffLogOut` | ⚠️ | Intended: after a staff logout | — not wired | `mixed $staff`, `array $meta` |
| `AdminAuthInit` | ⚠️ | Intended: admin login page load | — not wired | `array $data` |
| `AdminInitializing` | ⚠️ | Intended: before admin request boots — veto point | — not wired | `mixed $user`, `array $context` |
| `AdminInitialized` | ⚠️ | Intended: after admin request boots | — not wired | `mixed $user`, `array $context` |

### Cron

`AppCron` (`src/Console/Commands/AppCron.php`) currently runs tasks and reports
results, but dispatches **no** cron events.

| Event | Status | Intended behaviour | Payload |
|-------|--------|--------------------|---------|
| `CronBefore` | ⚠️ | Before a cron run — veto point | `array $tasks` |
| `CronAfter` | ⚠️ | After a cron run completes | `array $results` |
| `CronTasks` | ⚠️ | Filter: collect tasks from core + modules | `array $tasks` (mutable via `addTask()`) |

### Email template system

| Event | Status | Intended behaviour | Payload |
|-------|--------|--------------------|---------|
| `BeforeParseEmailTemplateMessage` | ⚠️ | Before parsing a template | `string $template`, `array $data` (mutable) |
| `AfterParseEmailTemplateMessage` | ⚠️ | After parsing a template | `string $message`, `array $data` (mutable) |
| `EmailTemplateParsed` | ⚠️ | After parse, before send | `string $message`, `array $data` |
| `EmailTemplateFromHeaders` | ⚠️ | Resolve headers (from, reply-to) | `array $templateData` (mutable) |
| `BeforeEmailTemplateSend` | ⚠️ | Before sending — veto point | `array $data` |
| `BeforeSendSimpleEmail` | ⚠️ | Before sending template-less email — veto point | `array $data` |
| `SendEmailTemplateTo` | ⚠️ | Before dispatching to a recipient — veto point | `array $to`, `array $cc`, `array $bcc` (mutable) |
| `MergeFields` | ⚠️ | Collect merge fields for templates | `array $fields` (mutable via `addField()`) |

### UI extension points

These correspond to Perfex `application\views\*` hooks. The package exposes them
as events, but the **UI/frontend hook layer is not ported yet** (PRD phase: after
Core). No dispatch site exists.

| Event | Status | Intended behaviour | Payload |
|-------|--------|--------------------|---------|
| `SettingsTabs` | ⚠️ | Build settings tab navigation | `array $tabs` |
| `SettingsUpdating` | ⚠️ | Before settings update — veto point | `array $data` |
| `ProfileTabs` | ⚠️ | Build profile tab navigation | `array $tabs` |
| `SetupMenu` | ⚠️ | Build the setup menu | `array $menu` |
| `SidebarMenu` | ⚠️ | Build sidebar navigation | `array $menu` |
| `QuickActions` | ⚠️ | Build quick action buttons | `array $actions` |
| `DashboardWidgets` | ⚠️ | Build dashboard widgets | `array $widgets` |
| `PermissionCatalog` | ⚠️ | Build the permission catalog for RBAC | `array $permissions` |

### Formatting & upload helpers

| Event | Status | Intended behaviour | Payload |
|-------|--------|--------------------|---------|
| `CurrentDateFormat` | ⚠️ | Resolve the current date format | `array $payload` |
| `MoneyFormatting` | ⚠️ | Format currency amounts | `array $payload` |
| `SalesNumberFormat` | ⚠️ | Format sales order / invoice numbers | `array $payload` |
| `GetOption` | ⚠️ | Resolve a configuration option value | `string $key`, `mixed $default` |
| `GetUploadPathByType` | ⚠️ | Resolve the upload path for a type | `string $type`, `string $path` |
| `UploadAllowedExtensions` | ⚠️ | Filter allowed file extensions | `string $type`, `array $extensions` |

## Veto points

Throw inside a listener to abort the action. Only these are actually wired:

`FileUploading`, `FileDeleting`, `EntityCreating`, `EntityUpdating`,
`EntityDeleting`, `MailSending`, `MailTesting`, `NotificationCreating`,
`PdfCreating`, `RelationResolving`.

```php
use Spine\Events\FileUploading;

Event::listen(FileUploading::class, function (FileUploading $event) {
    if ($event->file->getSize() > 10_000_000) {
        throw ValidationException::withMessages(['file' => 'Max 10 MB.']);
    }
});
```

## Listening to Events

Register listeners in the consumer's `EventServiceProvider` or inside a module's
provider (nwidart: `modules/<Name>/Providers/<Name>ServiceProvider.php`).

```php
use Spine\Events\SettingUpdated;

public function boot(): void
{
    Event::listen(SettingUpdated::class, function (SettingUpdated $event) {
        Cache::forget("setting.{$event->setting->key}");
    });
}
```

Or a dedicated listener class:

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

A working end-to-end example is in the sample module:
`modules/SampleTasks/Providers/SampleTasksServiceProvider.php` listens to
`EntityCreated`, `EntityUpdated`, and `EntityDeleted`.

## Adding a New Hook

1. **Create the event class** in `src/Events/` — `use Dispatchable`, public
   readonly constructor properties, no logic.
2. **Dispatch from the service** at the point the action completes. Without
   step 2 the class stays ⚠️ and listeners never fire.
3. **Add a row to the registry above** with the real `file:line` of the dispatch
   site, and flip **Status** to ✅.
4. Keep events **synchronous by default**; if listeners must not block the
   request, implement `ShouldQueue` on the *listener*, not the event.

## Related Laravel Primitives

| Pattern | Laravel equivalent |
|---------|--------------------|
| Fire-and-forget action | Laravel event (this registry) |
| Value-mutating filter | `Illuminate\Pipeline` / Eloquent model lifecycle events |
| Bootstrap / startup hook | `ServiceProvider::boot()` + middleware |
| Auth lifecycle | `Illuminate\Auth\Events\Login` / `Logout` / `PasswordReset` |
| Mail lifecycle | `Illuminate\Mail\Events\MessageSent` / `MessageSending` |
