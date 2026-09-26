# Graph Report - laravelspine  (2026-09-25)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 2272 nodes · 4596 edges · 219 communities (68 shown, 151 thin omitted)
- Extraction: 95% EXTRACTED · 5% INFERRED · 0% AMBIGUOUS · INFERRED: 226 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `9e73a481`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Attachment
- ActivityLogService
- Illuminate\Http\JsonResponse
- TagService
- Illuminate\Database\Eloquent\Model
- .config
- PdfService
- Illuminate\Http\Request
- Controller
- CustomMeta
- RelationService
- Illuminate\Foundation\Events\Dispatchable
- .config
- SampleItem
- SettingService
- FileService
- ActivityLog
- HasLifecycleHooks
- Str
- composer.json
- SampleTask
- public_html/composer.json
- Illuminate\Database\Schema\Blueprint
- Illuminate\Support\Facades\Route
- .schema
- .schema
- Illuminate\Console\Command
- public_html/src/SpineServiceProvider.php
- public_html/src/Http/Controllers/ModuleController.php
- SmsDriver
- Log
- public_html/src/Services/MailService.php
- ModuleService
- Illuminate\Database\Migrations\Migration
- PaymentGatewayInterface
- UserDashboardState
- Str
- ModuleService
- PublicController
- public_html/src/Services/GdprService.php
- public_html/src/Console/stubs/Http/Controllers/{{Entity}}Controller.php
- GdprService
- Illuminate\Support\Facades\Schema
- public_html/src/Services/ModuleService.php
- SmsService
- LogFileActivity
- AuthController
- Spatie\Permission\Models\Role
- public_html/src/Modules/SampleTasks/composer.json
- src/Modules/SampleTasks/composer.json
- Illuminate\Support\ServiceProvider
- boilerplates/Sample/composer.json
- sampletasks/composer.json
- SpineScaffoldCommand
- public_html/src/Console/stubs/composer.json
- public_html/src/Modules/Sample/composer.json
- src/Console/stubs/composer.json
- src/Modules/Sample/composer.json
- ModuleController
- ModuleController
- AppCron
- MailService
- LogFileActivity
- TwoFactorService
- SpineServiceProvider
- TwoFactorService
- SpineServiceProvider
- public_html/src/Services/QrCodeService.php
- TwilioSmsDriver
- AppCron
- DateFormatting
- PaymentService
- QrCodeService
- LogEntityActivity
- Number
- src/Console/stubs/Listeners/Log{{Entity}}Activity.php
- DateFormatting
- Number
- GenericMailNotification
- Illuminate\Notifications\Notification
- Illuminate\Support\Facades\Event
- LogEntityActivity
- public_html/src/Modules/Sample/Providers/SampleServiceProvider.php
- NumberToWord
- SmsService
- Illuminate\Support\Facades\Storage
- Illuminate\Support\Facades\Cache
- LogTaskActivity
- MetaController
- MailService
- PaymentService
- .png
- PdfController
- GenericMailNotification
- IpGuardService
- BaseNotification
- IpGuardService
- NumberToWord
- PaymentGatewayInterface
- RbacService
- public_html/src/Http/Controllers/MenuController.php
- public_html/database/migrations/2026_08_28_140909_create_tag_tables.php
- public_html/database/migrations/2026_09_24_062556_add_ulid_to_users_table.php
- ModuleActivated
- public_html/src/Modules/Sample/database/migrations/2026_09_01_000001_add_fields_to_sample_items_table.php
- public_html/src/Modules/Sample/database/migrations/2026_09_01_000002_add_ulid_status_to_sample_items_table.php
- database/migrations/2026_09_02_000001_create_user_dashboard_states_table.php
- boilerplates/Sample/database/migrations/2026_09_01_000000_create_sample_items_table.php
- public_html/database/migrations/2026_08_28_000003_create_custom_meta_table.php
- public_html/database/migrations/2026_08_28_000004_create_attachments_table.php
- public_html/database/migrations/2026_09_09_000001_create_notifications_table.php
- AdminInitializing
- BeforeEmailTemplateSend
- BeforeSendSimpleEmail
- CronBefore
- .send
- MailTesting
- RelationResolving
- SendEmailTemplateTo
- SettingsUpdating
- UploadAllowedExtensions
- CronController
- .handle
- public_html/src/Modules/SampleTasks/database/migrations/2026_09_01_000000_create_sample_tasks_table.php
- public_html/src/Modules/SampleTasks/Providers/SampleTasksServiceProvider.php
- AdminInitializing
- BeforeEmailTemplateSend
- BeforeSendSimpleEmail
- CronBefore
- EntityCreating
- .send
- MailTesting
- SendEmailTemplateTo
- SettingsUpdating
- UploadAllowedExtensions
- .handle
- SmsDriver
- AdminAuthInit
- AdminInitialized
- AfterParseEmailTemplateMessage
- BeforeParseEmailTemplateMessage
- CronAfter
- CronTasks
- CurrentDateFormat
- EmailTemplateFromHeaders
- EmailTemplateParsed
- GetOption
- MailTested
- MergeFields
- ModulesLoaded
- NotificationCreating
- PdfCreated
- ProfileTabs
- QuickActions
- SalesNumberFormat
- SettingsTabs
- SetupMenu
- SidebarMenu
- StaffLogIn
- StaffLogOut
- EntityCode
- AdminAuthInit
- AdminInitialized
- AfterParseEmailTemplateMessage
- BeforeParseEmailTemplateMessage
- CronAfter
- CronTasks
- CurrentDateFormat
- DashboardWidgets
- EmailTemplateFromHeaders
- EmailTemplateParsed
- FileUploaded
- MailFailed
- MailTested
- MergeFields
- ModuleActivated
- ModuleDeactivated
- MoneyFormatting
- NotificationCreating
- PermissionCatalog
- QuickActions
- SalesNumberFormat
- SettingsTabs
- SetupMenu
- SidebarMenu
- SmsSent
- StaffLogIn
- StaffLogOut
- EntityCode
- public_html/tests/verify.sh
- tests/verify.sh
- BinaryFileResponse
- ErrorCorrectionLevel

## God Nodes (most connected - your core abstractions)
1. `ActivityLogService` - 62 edges
2. `Controller` - 56 edges
3. `SampleItem` - 46 edges
4. `ApiResponse` - 41 edges
5. `SampleTask` - 40 edges
6. `ModuleService` - 38 edges
7. `EntityCreated` - 30 edges
8. `EntityDeleted` - 30 edges
9. `EntityUpdated` - 30 edges
10. `Attachment` - 27 edges

## Surprising Connections (you probably didn't know these)
- `__construct()` --references--> `ActivityLogService`  [EXTRACTED]
  src/Console/stubs/Http/Controllers/{{Entity}}Controller.php → public_html/src/Services/ActivityLogService.php
- `__construct()` --references--> `ActivityLogService`  [EXTRACTED]
  src/Console/stubs/Listeners/Log{{Entity}}Activity.php → public_html/src/Services/ActivityLogService.php
- `FileController` --inherits--> `Controller`  [EXTRACTED]
  src/Http/Controllers/FileController.php → public_html/src/Http/Controllers/Controller.php
- `PdfService` --references--> `SettingService`  [EXTRACTED]
  src/Services/PdfService.php → public_html/src/Services/SettingService.php
- `created()` --references--> `EntityCreated`  [EXTRACTED]
  src/Console/stubs/Listeners/Log{{Entity}}Activity.php → public_html/src/Events/EntityCreated.php

## Import Cycles
- None detected.

## Communities (219 total, 151 thin omitted)

### Community 0 - "Attachment"
Cohesion: 0.05
Nodes (16): Illuminate\Http\UploadedFile, FileDeleted, FileDeleting, FileUploading, FileController, Attachment, FileService, Attachment (+8 more)

### Community 1 - "ActivityLogService"
Cohesion: 0.06
Nodes (18): Authenticatable, SyncSampleStatusFromTasks, EntityCreated, EntityDeleted, EntityUpdated, Authenticatable, SyncSampleStatusFromTasks, LogTaskActivity (+10 more)

### Community 2 - "Illuminate\Http\JsonResponse"
Cohesion: 0.05
Nodes (16): Illuminate\Http\JsonResponse, ActivityLogController, NumberToWordController, PaymentController, SettingController, activityLogs(), __construct(), destroy() (+8 more)

### Community 3 - "TagService"
Cohesion: 0.06
Nodes (17): Illuminate\Contracts\Filesystem\Filesystem, Illuminate\Support\Collection, Maatwebsite\Excel\Concerns\FromArray, Maatwebsite\Excel\Concerns\ShouldAutoSize, Maatwebsite\Excel\Concerns\ToCollection, Maatwebsite\Excel\Concerns\WithCustomCsvSettings, Maatwebsite\Excel\Concerns\WithHeadingRow, Maatwebsite\Excel\Concerns\WithHeadings (+9 more)

### Community 4 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.07
Nodes (23): Illuminate\Database\Eloquent\Model, EntityCreating, EntityDeleting, EntityUpdating, {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}() (+15 more)

### Community 5 - ".config"
Cohesion: 0.06
Nodes (8): BroadcastController, MenuController, PermissionController, PublicController, RoleController, SystemController, TranslationController, UserController

### Community 6 - "PdfService"
Cohesion: 0.07
Nodes (18): Illuminate\Broadcasting\PrivateChannel, Illuminate\Bus\Queueable, Illuminate\Contracts\Broadcasting\ShouldBroadcastNow, Illuminate\Contracts\Queue\ShouldQueue, Illuminate\Foundation\Bus\Dispatchable, Illuminate\Mail\Mailable, Illuminate\Mail\Mailables\Content, Illuminate\Mail\Mailables\Envelope (+10 more)

### Community 7 - "Illuminate\Http\Request"
Cohesion: 0.06
Nodes (10): Illuminate\Http\Request, index(), store(), update(), Log, MailController, NotificationController, MailController (+2 more)

### Community 8 - "Controller"
Cohesion: 0.05
Nodes (13): ApiResponse, Controller, ExcelController, QrCodeController, SmsController, TagController, Controller, ExcelController (+5 more)

### Community 9 - "CustomMeta"
Cohesion: 0.08
Nodes (19): Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Database\Eloquent\Relations\MorphMany, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Laravel\Sanctum\HasApiTokens, CustomMeta, {closure#1}() (+11 more)

### Community 10 - "RelationService"
Cohesion: 0.07
Nodes (13): Closure, Exception, Illuminate\Support\Facades\App, RelationTypeNotRegisteredException, RelationController, SetLocale, RelationService, RelationResolving (+5 more)

### Community 11 - "Illuminate\Foundation\Events\Dispatchable"
Cohesion: 0.06
Nodes (14): Illuminate\Foundation\Events\Dispatchable, DashboardWidgets, FileUploaded, GetUploadPathByType, MailFailed, MoneyFormatting, PermissionCatalog, GetOption (+6 more)

### Community 12 - ".config"
Cohesion: 0.08
Nodes (6): AuthController, BroadcastController, MenuController, PermissionController, RoleController, UserController

### Community 13 - "SampleItem"
Cohesion: 0.09
Nodes (10): Illuminate\Database\Seeder, Illuminate\Routing\Controller, SampleController, SampleItem, SampleItemsSeeder, SampleController, SampleTasksSeeder, SampleItemsSeeder (+2 more)

### Community 14 - "SettingService"
Cohesion: 0.10
Nodes (12): Illuminate\Database\Eloquent\ModelNotFoundException, SettingUpdated, RepositoryInterface, Setting, Builder, Collection, SettingService, SettingUpdated (+4 more)

### Community 15 - "FileService"
Cohesion: 0.06
Nodes (9): Illuminate\Support\Carbon, Time, ActivityLogService, Builder, FileService, Attachment, BinaryFileResponse, UploadedFile (+1 more)

### Community 16 - "ActivityLog"
Cohesion: 0.08
Nodes (8): Illuminate\Database\Eloquent\Relations\MorphTo, ActivityLog, Spatie\QueryBuilder\AllowedFilter, Spatie\QueryBuilder\QueryBuilder, ActivityLogController, ActivityLog, Attachment, CustomMeta

### Community 17 - "HasLifecycleHooks"
Cohesion: 0.11
Nodes (8): Illuminate\Database\Eloquent\Concerns\HasUlids, Illuminate\Database\Eloquent\Relations\BelongsTo, SampleItem, SampleTask, HasLifecycleHooks, Setting, SampleItem, SampleTask

### Community 18 - "Str"
Cohesion: 0.08
Nodes (5): {closure#3}(), {closure#4}(), GdprService, RegisterOtpService, Str

### Community 19 - "composer.json"
Cohesion: 0.07
Nodes (26): autoload, psr-4, config, sort-packages, description, extra, laravel, keywords (+18 more)

### Community 20 - "SampleTask"
Cohesion: 0.12
Nodes (5): Illuminate\Support\Facades\Log, SampleTaskController, SampleTask, SampleTaskController, SampleTaskController

### Community 21 - "public_html/composer.json"
Cohesion: 0.07
Nodes (26): autoload, psr-4, config, sort-packages, description, extra, laravel, keywords (+18 more)

### Community 22 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.11
Nodes (11): {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), Illuminate\Database\Schema\Blueprint, {closure#1}() (+3 more)

### Community 24 - ".schema"
Cohesion: 0.10
Nodes (19): {closure#1}(), down(), up(), {closure#1}(), down(), up(), {closure#1}(), down() (+11 more)

### Community 25 - ".schema"
Cohesion: 0.11
Nodes (17): {closure#1}(), down(), up(), {closure#1}(), down(), up(), {closure#1}(), {closure#2}() (+9 more)

### Community 26 - "Illuminate\Console\Command"
Cohesion: 0.17
Nodes (7): Illuminate\Console\Command, SyncCoreRbacCommand, SyncRbacCommand, RbacService, Spatie\Permission\PermissionRegistrar, SyncCoreRbacCommand, SyncRbacCommand

### Community 27 - "public_html/src/SpineServiceProvider.php"
Cohesion: 0.13
Nodes (6): Illuminate\Support\Facades\Broadcast, MakeSpineEntity, MakeSpineModule, Spatie\Permission\Middleware\PermissionMiddleware, Spatie\Permission\Middleware\RoleMiddleware, Spatie\Permission\Middleware\RoleOrPermissionMiddleware

### Community 28 - "public_html/src/Http/Controllers/ModuleController.php"
Cohesion: 0.10
Nodes (3): ModuleDeactivated, ModuleInstalled, ModuleUninstalled

### Community 29 - "SmsDriver"
Cohesion: 0.14
Nodes (6): SmsSent, LogSmsDriver, SmsDriver, TwilioSmsDriver, {closure#1}(), {closure#1}()

### Community 30 - "Log"
Cohesion: 0.11
Nodes (8): Log, LogFileActivity, FileDeleted, FileDeleting, FileUploaded, FileUploading, StripePaymentGateway, LogSmsDriver

### Community 31 - "public_html/src/Services/MailService.php"
Cohesion: 0.14
Nodes (5): Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Mail, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\Queue

### Community 33 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.12
Nodes (5): {closure#1}(), {closure#1}(), {closure#1}(), Illuminate\Database\Migrations\Migration, {closure#1}()

### Community 34 - "PaymentGatewayInterface"
Cohesion: 0.15
Nodes (5): Illuminate\Support\Facades\Config, PaymentGatewayInterface, StripePaymentGateway, {closure#1}(), {closure#1}()

### Community 35 - "UserDashboardState"
Cohesion: 0.21
Nodes (3): DashboardController, UserDashboardState, DashboardController

### Community 38 - "PublicController"
Cohesion: 0.17
Nodes (4): Illuminate\Support\Facades\File, Illuminate\Support\Facades\Lang, PublicController, TranslationController

### Community 39 - "public_html/src/Services/GdprService.php"
Cohesion: 0.12
Nodes (4): Illuminate\Support\Str, {closure#3}(), {closure#4}(), SpineScaffoldCommand

### Community 40 - "public_html/src/Console/stubs/Http/Controllers/{{Entity}}Controller.php"
Cohesion: 0.18
Nodes (13): Modules\{{Studly}}\Models\{{Entity, activityLogs(), __construct(), destroy(), show(), __construct(), created(), deleted() (+5 more)

### Community 41 - "GdprService"
Cohesion: 0.16
Nodes (3): GdprController, GdprService, GdprController

### Community 42 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.15
Nodes (5): {closure#1}(), {closure#1}(), Illuminate\Support\Facades\Schema, Spatie\Permission\Contracts\Permission, Spatie\Permission\Exceptions\RoleDoesNotExist

### Community 43 - "public_html/src/Services/ModuleService.php"
Cohesion: 0.25
Nodes (7): Nwidart\Modules\Contracts\ActivatorInterface, Nwidart\Modules\Contracts\RepositoryInterface, Nwidart\Modules\Module, {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}()

### Community 44 - "SmsService"
Cohesion: 0.20
Nodes (3): SmsChannel, SmsService, SmsChannel

### Community 45 - "LogFileActivity"
Cohesion: 0.14
Nodes (6): LogFileActivity, FileDeleted, FileDeleting, FileUploaded, FileUploading, SampleServiceProvider

### Community 48 - "public_html/src/Modules/SampleTasks/composer.json"
Cohesion: 0.14
Nodes (13): autoload, classmap, psr-4, description, extra, laravel, providers, license (+5 more)

### Community 49 - "src/Modules/SampleTasks/composer.json"
Cohesion: 0.14
Nodes (13): autoload, classmap, psr-4, description, extra, laravel, providers, license (+5 more)

### Community 50 - "Illuminate\Support\ServiceProvider"
Cohesion: 0.19
Nodes (4): Illuminate\Support\ServiceProvider, SampleServiceProvider, SampleTasksServiceProvider, SampleTasksServiceProvider

### Community 51 - "boilerplates/Sample/composer.json"
Cohesion: 0.15
Nodes (12): autoload, psr-4, description, extra, laravel, providers, license, name (+4 more)

### Community 52 - "sampletasks/composer.json"
Cohesion: 0.15
Nodes (12): autoload, psr-4, description, extra, laravel, providers, license, name (+4 more)

### Community 53 - "SpineScaffoldCommand"
Cohesion: 0.19
Nodes (3): SpineScaffoldCommand, MakeSpineEntity, MakeSpineModule

### Community 54 - "public_html/src/Console/stubs/composer.json"
Cohesion: 0.15
Nodes (12): autoload, psr-4, description, extra, laravel, providers, license, name (+4 more)

### Community 55 - "public_html/src/Modules/Sample/composer.json"
Cohesion: 0.15
Nodes (12): autoload, psr-4, description, extra, laravel, providers, license, name (+4 more)

### Community 56 - "src/Console/stubs/composer.json"
Cohesion: 0.15
Nodes (12): autoload, psr-4, description, extra, laravel, providers, license, name (+4 more)

### Community 57 - "src/Modules/Sample/composer.json"
Cohesion: 0.15
Nodes (12): autoload, psr-4, description, extra, laravel, providers, license, name (+4 more)

### Community 58 - "ModuleController"
Cohesion: 0.20
Nodes (3): ModuleController, RepositoryInterface, Response

### Community 59 - "ModuleController"
Cohesion: 0.20
Nodes (3): ModuleController, RepositoryInterface, Response

### Community 62 - "LogFileActivity"
Cohesion: 0.20
Nodes (5): LogFileActivity, FileDeleted, FileDeleting, FileUploaded, FileUploading

### Community 67 - "public_html/src/Services/QrCodeService.php"
Cohesion: 0.33
Nodes (5): Endroid\QrCode\Encoding\Encoding, Endroid\QrCode\ErrorCorrectionLevel, Endroid\QrCode\QrCode, Endroid\QrCode\Writer\PngWriter, Endroid\QrCode\Writer\SvgWriter

### Community 75 - "src/Console/stubs/Listeners/Log{{Entity}}Activity.php"
Cohesion: 0.42
Nodes (8): __construct(), created(), deleted(), describe(), label(), Authenticatable, updated(), user()

### Community 82 - "public_html/src/Modules/Sample/Providers/SampleServiceProvider.php"
Cohesion: 0.25
Nodes (3): LogSettingChange, SettingUpdated, SampleServiceProvider

### Community 85 - "Illuminate\Support\Facades\Storage"
Cohesion: 0.38
Nodes (5): Barryvdh\DomPDF\Facade\Pdf, Illuminate\Support\Facades\Storage, {closure#1}(), {closure#1}(), ZipArchive

### Community 86 - "Illuminate\Support\Facades\Cache"
Cohesion: 0.38
Nodes (3): Illuminate\Support\Facades\Cache, Illuminate\Support\Facades\Hash, Illuminate\Validation\ValidationException

### Community 101 - "public_html/database/migrations/2026_08_28_140909_create_tag_tables.php"
Cohesion: 0.40
Nodes (4): {closure#1}(), {closure#2}(), down(), up()

### Community 102 - "public_html/database/migrations/2026_09_24_062556_add_ulid_to_users_table.php"
Cohesion: 0.40
Nodes (4): {closure#1}(), {closure#2}(), down(), up()

### Community 104 - "public_html/src/Modules/Sample/database/migrations/2026_09_01_000001_add_fields_to_sample_items_table.php"
Cohesion: 0.40
Nodes (4): {closure#1}(), {closure#2}(), down(), up()

### Community 105 - "public_html/src/Modules/Sample/database/migrations/2026_09_01_000002_add_ulid_status_to_sample_items_table.php"
Cohesion: 0.40
Nodes (4): {closure#1}(), {closure#2}(), down(), up()

### Community 108 - "public_html/database/migrations/2026_08_28_000003_create_custom_meta_table.php"
Cohesion: 0.50
Nodes (3): {closure#1}(), down(), up()

### Community 109 - "public_html/database/migrations/2026_08_28_000004_create_attachments_table.php"
Cohesion: 0.50
Nodes (3): {closure#1}(), down(), up()

### Community 110 - "public_html/database/migrations/2026_09_09_000001_create_notifications_table.php"
Cohesion: 0.50
Nodes (3): {closure#1}(), down(), up()

### Community 123 - "public_html/src/Modules/SampleTasks/database/migrations/2026_09_01_000000_create_sample_tasks_table.php"
Cohesion: 0.50
Nodes (3): {closure#1}(), down(), up()

## Knowledge Gaps
- **100 isolated node(s):** `verify.sh script`, `sort-packages`, `description`, `keywords`, `providers` (+95 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 593 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **151 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `ActivityLogService` connect `ActivityLogService` to `Illuminate\Http\JsonResponse`, `public_html/src/Console/stubs/Http/Controllers/{{Entity}}Controller.php`, `LogEntityActivity`, `src/Console/stubs/Listeners/Log{{Entity}}Activity.php`, `SampleItem`, `ActivityLog`, `LogEntityActivity`, `SampleTask`, `LogTaskActivity`?**
  _High betweenness centrality (0.080) - this node is a cross-community bridge._
- **Why does `ModuleService` connect `ModuleService` to `UserDashboardState`, `ModuleController`, `public_html/src/Services/ModuleService.php`, `Illuminate\Console\Command`, `ModuleController`, `public_html/src/Http/Controllers/ModuleController.php`?**
  _High betweenness centrality (0.036) - this node is a cross-community bridge._
- **Why does `Str` connect `Str` to `SpineScaffoldCommand`, `public_html/src/Services/GdprService.php`?**
  _High betweenness centrality (0.036) - this node is a cross-community bridge._
- **What connects `verify.sh script`, `sort-packages`, `description` to the rest of the system?**
  _100 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Attachment` be split into smaller, more focused modules?**
  _Cohesion score 0.05048076923076923 - nodes in this community are weakly interconnected._
- **Should `ActivityLogService` be split into smaller, more focused modules?**
  _Cohesion score 0.06398809523809523 - nodes in this community are weakly interconnected._
- **Should `Illuminate\Http\JsonResponse` be split into smaller, more focused modules?**
  _Cohesion score 0.05376972530683811 - nodes in this community are weakly interconnected._