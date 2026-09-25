# Graph Report - laravelspine  (2026-09-25)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 1232 nodes · 2449 edges · 125 communities (54 shown, 71 thin omitted)
- Extraction: 94% EXTRACTED · 6% INFERRED · 0% AMBIGUOUS · INFERRED: 139 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `7ee7fb0a`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Log
- PdfService
- ModuleService
- GenericExport
- Illuminate\Http\JsonResponse
- Illuminate\Support\ServiceProvider
- TwoFactorService
- .config
- Illuminate\Database\Eloquent\Model
- public_html/composer.json
- User
- Illuminate\Foundation\Events\Dispatchable
- RelationService
- Illuminate\Http\Request
- Controller
- MailService
- EntityCreated
- SampleTask
- ModuleController.php
- SettingService
- ActivityLog
- HasLifecycleHooks
- Str
- RbacService
- Illuminate\Database\Schema\Blueprint
- PublicController
- GdprService.php
- Illuminate\Support\Facades\Route
- SampleTasks/composer.json
- SmsDriver
- boilerplates/Sample/composer.json
- sampletasks/composer.json
- public_html/routes/api.php
- stubs/composer.json
- PermissionController
- Modules/Sample/composer.json
- MailService.php
- ActivityLogService
- .schema
- SampleItem
- SmsService
- AppCron
- MenuController
- PaymentGatewayInterface
- StripePaymentGateway
- Illuminate\Support\Facades\Schema
- SampleController
- DateFormatting
- NumberToWord
- Number
- Illuminate\Support\Facades\Cache
- SpineServiceProvider.php
- LogTaskActivity
- Log{{Entity}}Activity.php
- PaymentService
- LogEntityActivity
- Illuminate\Database\Seeder
- LogEntityActivity
- SpineScaffoldCommand
- MetaController
- LogTaskActivity
- MakeSpineEntity
- PaymentController
- PdfController
- IpGuardService
- Illuminate\Database\Migrations\Migration
- 2026_08_28_140909_create_tag_tables.php
- 2026_09_24_100000_add_language_to_users_table.php
- SmsSent
- GdprController
- Modules/Sample/database/migrations/2026_09_01_000001_add_fields_to_sample_items_table.php
- Modules/Sample/database/migrations/2026_09_01_000002_add_ulid_status_to_sample_items_table.php
- 2026_08_28_000003_create_custom_meta_table.php
- 2026_08_28_000004_create_attachments_table.php
- 2026_09_02_000001_create_user_dashboard_states_table.php
- 2026_09_09_000001_create_notifications_table.php
- create_{{table}}_table.php
- AdminInitializing
- BeforeEmailTemplateSend
- BeforeSendSimpleEmail
- CronBefore
- EntityCreating
- .send
- SendEmailTemplateTo
- SettingsUpdating
- UploadAllowedExtensions
- Modules/Sample/database/migrations/2026_09_01_000000_create_sample_items_table.php
- SampleTasks/database/migrations/2026_09_01_000000_create_sample_tasks_table.php
- AdminAuthInit
- AdminInitialized
- BeforeParseEmailTemplateMessage
- CronAfter
- CronTasks
- CurrentDateFormat
- DashboardWidgets
- EmailTemplateFromHeaders
- GetOption
- GetUploadPathByType
- MailFailed
- MailTested
- MoneyFormatting
- PdfCreated
- PermissionCatalog
- ProfileTabs
- QuickActions
- SetupMenu
- SidebarMenu
- StaffLogIn
- StaffLogOut
- EntityCode
- verify.sh

## God Nodes (most connected - your core abstractions)
1. `ActivityLogService` - 40 edges
2. `SampleItem` - 31 edges
3. `ModuleService` - 30 edges
4. `Controller` - 29 edges
5. `SampleTask` - 28 edges
6. `Log` - 24 edges
7. `ApiResponse` - 23 edges
8. `EntityCreated` - 20 edges
9. `EntityDeleted` - 20 edges
10. `EntityUpdated` - 20 edges

## Surprising Connections (you probably didn't know these)
- `SampleTaskController` --references--> `ActivityLogService`  [EXTRACTED]
  modules/sampletasks/Http/Controllers/SampleTaskController.php → public_html/src/Services/ActivityLogService.php
- `SampleTask` --mixes_in--> `HasLifecycleHooks`  [EXTRACTED]
  modules/sampletasks/Models/SampleTask.php → public_html/src/Traits/HasLifecycleHooks.php
- `SampleItem` --mixes_in--> `HasLifecycleHooks`  [EXTRACTED]
  modules/boilerplates/Sample/Models/SampleItem.php → public_html/src/Traits/HasLifecycleHooks.php
- `{closure#3}()` --calls--> `Str`  [INFERRED]
  public_html/src/Services/GdprService.php → public_html/src/Support/Helpers/Str.php
- `SampleController` --references--> `ActivityLogService`  [EXTRACTED]
  modules/boilerplates/Sample/Http/Controllers/SampleController.php → public_html/src/Services/ActivityLogService.php

## Import Cycles
- None detected.

## Communities (125 total, 71 thin omitted)

### Community 0 - "Log"
Cohesion: 0.06
Nodes (14): BinaryFileResponse, Illuminate\Support\Facades\Log, LogFileActivity, LogSettingChange, Log, FileDeleted, FileDeleting, FileUploaded (+6 more)

### Community 1 - "PdfService"
Cohesion: 0.08
Nodes (15): Illuminate\Broadcasting\PrivateChannel, Illuminate\Bus\Queueable, Illuminate\Contracts\Broadcasting\ShouldBroadcastNow, Illuminate\Contracts\Queue\ShouldQueue, Illuminate\Foundation\Bus\Dispatchable, Illuminate\Mail\Mailable, Illuminate\Mail\Mailables\Content, Illuminate\Mail\Mailables\Envelope (+7 more)

### Community 2 - "ModuleService"
Cohesion: 0.11
Nodes (9): Nwidart\Modules\Contracts\ActivatorInterface, Nwidart\Modules\Contracts\RepositoryInterface, Nwidart\Modules\Module, DashboardController, RepositoryInterface, UserDashboardState, {closure#1}(), {closure#2}() (+1 more)

### Community 3 - "GenericExport"
Cohesion: 0.08
Nodes (18): Barryvdh\DomPDF\Facade\Pdf, Illuminate\Contracts\Filesystem\Filesystem, Illuminate\Http\UploadedFile, Illuminate\Support\Collection, Illuminate\Support\Facades\Storage, Maatwebsite\Excel\Concerns\FromArray, Maatwebsite\Excel\Concerns\ShouldAutoSize, Maatwebsite\Excel\Concerns\ToCollection (+10 more)

### Community 4 - "Illuminate\Http\JsonResponse"
Cohesion: 0.09
Nodes (13): Illuminate\Http\JsonResponse, Modules\{{Studly}}\Models\{{Entity, activityLogs(), __construct(), destroy(), index(), show(), store() (+5 more)

### Community 5 - "Illuminate\Support\ServiceProvider"
Cohesion: 0.09
Nodes (8): Illuminate\Support\Facades\Event, Illuminate\Support\ServiceProvider, SampleServiceProvider, SampleTasksServiceProvider, Modules\{{Studly}}\Listeners\Log, SampleServiceProvider, SampleTasksServiceProvider, SpineServiceProvider

### Community 6 - "TwoFactorService"
Cohesion: 0.08
Nodes (8): Endroid\QrCode\Encoding\Encoding, Endroid\QrCode\ErrorCorrectionLevel, Endroid\QrCode\Writer\PngWriter, ErrorCorrectionLevel, Illuminate\Support\Carbon, QrCodeService, TwoFactorService, Time

### Community 7 - ".config"
Cohesion: 0.12
Nodes (4): Illuminate\Validation\ValidationException, AuthController, RoleController, UserController

### Community 8 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.12
Nodes (11): Illuminate\Database\Eloquent\Model, EntityDeleting, EntityUpdating, TagService, {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}() (+3 more)

### Community 9 - "public_html/composer.json"
Cohesion: 0.07
Nodes (26): autoload, psr-4, config, sort-packages, description, extra, laravel, keywords (+18 more)

### Community 10 - "User"
Cohesion: 0.13
Nodes (12): Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Relations\MorphMany, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Laravel\Sanctum\HasApiTokens, CustomMeta, {closure#1}() (+4 more)

### Community 11 - "Illuminate\Foundation\Events\Dispatchable"
Cohesion: 0.10
Nodes (9): Illuminate\Foundation\Events\Dispatchable, AfterParseEmailTemplateMessage, EmailTemplateParsed, MailTesting, MergeFields, ModulesLoaded, NotificationCreating, SalesNumberFormat (+1 more)

### Community 12 - "RelationService"
Cohesion: 0.11
Nodes (9): Closure, Exception, Illuminate\Support\Facades\App, RelationResolving, RelationTypeNotRegisteredException, RelationController, SetLocale, RelationService (+1 more)

### Community 13 - "Illuminate\Http\Request"
Cohesion: 0.11
Nodes (3): Illuminate\Http\Request, MailController, NotificationController

### Community 14 - "Controller"
Cohesion: 0.11
Nodes (6): ApiResponse, Controller, ExcelController, QrCodeController, SmsController, TagController

### Community 15 - "MailService"
Cohesion: 0.12
Nodes (5): Illuminate\Notifications\Messages\MailMessage, Illuminate\Notifications\Notification, BaseNotification, GenericMailNotification, MailService

### Community 16 - "EntityCreated"
Cohesion: 0.15
Nodes (5): EntityCreated, EntityDeleted, EntityUpdated, Authenticatable, SyncSampleStatusFromTasks

### Community 17 - "SampleTask"
Cohesion: 0.17
Nodes (4): Illuminate\Routing\Controller, SampleTaskController, SampleTask, SampleTaskController

### Community 18 - "ModuleController.php"
Cohesion: 0.10
Nodes (5): ModuleActivated, ModuleDeactivated, ModuleInstalled, ModuleUninstalled, Spatie\Permission\PermissionRegistrar

### Community 19 - "SettingService"
Cohesion: 0.18
Nodes (7): Illuminate\Database\Eloquent\ModelNotFoundException, SettingUpdated, RepositoryInterface, Setting, Builder, Collection, SettingService

### Community 20 - "ActivityLog"
Cohesion: 0.13
Nodes (5): Illuminate\Database\Eloquent\Relations\MorphTo, ActivityLogController, ActivityLog, Spatie\QueryBuilder\AllowedFilter, Spatie\QueryBuilder\QueryBuilder

### Community 21 - "HasLifecycleHooks"
Cohesion: 0.18
Nodes (5): Illuminate\Database\Eloquent\Concerns\HasUlids, Illuminate\Database\Eloquent\Relations\BelongsTo, SampleItem, SampleTask, HasLifecycleHooks

### Community 23 - "RbacService"
Cohesion: 0.25
Nodes (4): Illuminate\Console\Command, SyncCoreRbacCommand, SyncRbacCommand, RbacService

### Community 24 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.21
Nodes (11): Illuminate\Database\Schema\Blueprint, {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), down(), up(), {closure#1}() (+3 more)

### Community 25 - "PublicController"
Cohesion: 0.20
Nodes (4): Illuminate\Support\Facades\File, Illuminate\Support\Facades\Lang, PublicController, TranslationController

### Community 26 - "GdprService.php"
Cohesion: 0.15
Nodes (4): Illuminate\Support\Facades\Hash, {closure#3}(), {closure#4}(), GdprService

### Community 28 - "SampleTasks/composer.json"
Cohesion: 0.14
Nodes (13): autoload, classmap, psr-4, description, extra, laravel, providers, license (+5 more)

### Community 29 - "SmsDriver"
Cohesion: 0.18
Nodes (4): LogSmsDriver, SmsDriver, TwilioSmsDriver, {closure#1}()

### Community 30 - "boilerplates/Sample/composer.json"
Cohesion: 0.15
Nodes (12): autoload, psr-4, description, extra, laravel, providers, license, name (+4 more)

### Community 31 - "sampletasks/composer.json"
Cohesion: 0.15
Nodes (12): autoload, psr-4, description, extra, laravel, providers, license, name (+4 more)

### Community 33 - "stubs/composer.json"
Cohesion: 0.15
Nodes (12): autoload, psr-4, description, extra, laravel, providers, license, name (+4 more)

### Community 34 - "PermissionController"
Cohesion: 0.15
Nodes (3): PermissionController, Spatie\Permission\Models\Permission, Spatie\Permission\Models\Role

### Community 35 - "Modules/Sample/composer.json"
Cohesion: 0.15
Nodes (12): autoload, psr-4, description, extra, laravel, providers, license, name (+4 more)

### Community 36 - "MailService.php"
Cohesion: 0.18
Nodes (5): Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Mail, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\Queue

### Community 37 - "ActivityLogService"
Cohesion: 0.20
Nodes (5): Authenticatable, SyncSampleStatusFromTasks, __construct(), ActivityLogService, Builder

### Community 38 - ".schema"
Cohesion: 0.22
Nodes (8): {closure#1}(), down(), up(), down(), up(), {closure#1}(), down(), up()

### Community 43 - "PaymentGatewayInterface"
Cohesion: 0.25
Nodes (3): Illuminate\Support\Facades\Config, PaymentGatewayInterface, {closure#1}()

### Community 45 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.22
Nodes (6): Illuminate\Support\Facades\Schema, {closure#1}(), down(), up(), Spatie\Permission\Contracts\Permission, Spatie\Permission\Exceptions\RoleDoesNotExist

### Community 50 - "Illuminate\Support\Facades\Cache"
Cohesion: 0.29
Nodes (4): Endroid\QrCode\QrCode, Endroid\QrCode\Writer\SvgWriter, Illuminate\Support\Facades\Cache, Illuminate\Support\Str

### Community 51 - "SpineServiceProvider.php"
Cohesion: 0.25
Nodes (4): Illuminate\Support\Facades\Broadcast, Spatie\Permission\Middleware\PermissionMiddleware, Spatie\Permission\Middleware\RoleMiddleware, Spatie\Permission\Middleware\RoleOrPermissionMiddleware

### Community 53 - "Log{{Entity}}Activity.php"
Cohesion: 0.50
Nodes (7): created(), deleted(), describe(), label(), Authenticatable, updated(), user()

### Community 56 - "Illuminate\Database\Seeder"
Cohesion: 0.38
Nodes (3): Illuminate\Database\Seeder, SampleItemsSeeder, SampleTasksSeeder

### Community 65 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.40
Nodes (4): Illuminate\Database\Migrations\Migration, {closure#1}(), down(), up()

### Community 66 - "2026_08_28_140909_create_tag_tables.php"
Cohesion: 0.40
Nodes (4): {closure#1}(), {closure#2}(), down(), up()

### Community 67 - "2026_09_24_100000_add_language_to_users_table.php"
Cohesion: 0.40
Nodes (4): {closure#1}(), {closure#2}(), down(), up()

### Community 70 - "Modules/Sample/database/migrations/2026_09_01_000001_add_fields_to_sample_items_table.php"
Cohesion: 0.40
Nodes (4): {closure#1}(), {closure#2}(), down(), up()

### Community 71 - "Modules/Sample/database/migrations/2026_09_01_000002_add_ulid_status_to_sample_items_table.php"
Cohesion: 0.40
Nodes (4): {closure#1}(), {closure#2}(), down(), up()

### Community 72 - "2026_08_28_000003_create_custom_meta_table.php"
Cohesion: 0.50
Nodes (3): {closure#1}(), down(), up()

### Community 73 - "2026_08_28_000004_create_attachments_table.php"
Cohesion: 0.50
Nodes (3): {closure#1}(), down(), up()

### Community 74 - "2026_09_02_000001_create_user_dashboard_states_table.php"
Cohesion: 0.50
Nodes (3): {closure#1}(), down(), up()

### Community 75 - "2026_09_09_000001_create_notifications_table.php"
Cohesion: 0.50
Nodes (3): {closure#1}(), down(), up()

### Community 76 - "create_{{table}}_table.php"
Cohesion: 0.50
Nodes (3): {closure#1}(), down(), up()

### Community 86 - "Modules/Sample/database/migrations/2026_09_01_000000_create_sample_items_table.php"
Cohesion: 0.50
Nodes (3): {closure#1}(), down(), up()

### Community 87 - "SampleTasks/database/migrations/2026_09_01_000000_create_sample_tasks_table.php"
Cohesion: 0.50
Nodes (3): {closure#1}(), down(), up()

## Knowledge Gaps
- **57 isolated node(s):** `verify.sh script`, `classmap`, `description`, `providers`, `license` (+52 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 314 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **71 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `ActivityLogService` connect `ActivityLogService` to `Illuminate\Http\JsonResponse`, `SampleItem`, `Illuminate\Http\Request`, `SampleController`, `EntityCreated`, `SampleTask`, `LogTaskActivity`, `Log{{Entity}}Activity.php`, `ActivityLog`, `LogEntityActivity`, `LogEntityActivity`, `LogTaskActivity`?**
  _High betweenness centrality (0.049) - this node is a cross-community bridge._
- **Why does `FileService` connect `Log` to `PdfService`, `ActivityLog`?**
  _High betweenness centrality (0.033) - this node is a cross-community bridge._
- **Why does `Log` connect `Log` to `Illuminate\Http\JsonResponse`, `SampleItem`, `StripePaymentGateway`, `Illuminate\Http\Request`, `SampleController`, `SampleTask`, `Log{{Entity}}Activity.php`, `SmsDriver`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **What connects `verify.sh script`, `classmap`, `description` to the rest of the system?**
  _57 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Log` be split into smaller, more focused modules?**
  _Cohesion score 0.059506531204644414 - nodes in this community are weakly interconnected._
- **Should `PdfService` be split into smaller, more focused modules?**
  _Cohesion score 0.08392603129445235 - nodes in this community are weakly interconnected._
- **Should `ModuleService` be split into smaller, more focused modules?**
  _Cohesion score 0.11379800853485064 - nodes in this community are weakly interconnected._