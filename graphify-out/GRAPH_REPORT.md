# Graph Report - laravelspine  (2026-09-26)

## Corpus Check
- 233 files · ~53,564 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 6 file(s) not represented in the graph (top: (none) 6)

## Summary
- 1411 nodes · 2774 edges · 104 communities (69 shown, 35 thin omitted)
- Extraction: 91% EXTRACTED · 9% INFERRED · 0% AMBIGUOUS · INFERRED: 242 edges (avg confidence: 0.89)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `f27a02fe`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Attachment
- ActivityLogService
- Illuminate\Http\JsonResponse
- GenericExport
- Illuminate\Database\Eloquent\Model
- routes/api.php
- PdfService
- Illuminate\Http\Request
- Controller
- User
- IpGuardService
- Illuminate\Foundation\Events\Dispatchable
- .config
- SampleItem
- SettingService
- Time
- ActivityLog
- HasLifecycleHooks
- Str
- composer.json
- SampleTask
- API Reference
- Illuminate\Database\Schema\Blueprint
- Illuminate\Support\Facades\Route
- Installation Guide
- Modules/Sample/database/migrations/2026_09_01_000001_add_fields_to_sample_items_table.php
- RbacService
- SpineServiceProvider.php
- Modules
- Laravel Spine
- Log
- MailService.php
- Authentication
- Illuminate\Database\Migrations\Migration
- RBAC — Role & Permission
- DashboardController
- Laravel Spine Documentation
- ModuleService
- PublicController
- SpineScaffoldCommand
- Illuminate\Database\Seeder
- GdprService.php
- Illuminate\Support\Facades\Schema
- AGENTS.md — laravelspine (package `spine/laravel-spine`)
- UserFactory.php
- MetaController
- TagService
- LogEntityActivity
- src/Modules/SampleTasks/composer.json
- Illuminate\Support\ServiceProvider
- boilerplates/Sample/composer.json
- sampletasks/composer.json
- MakeSpineEntity
- UserFactory
- Consumer Setup Notes
- src/Console/stubs/composer.json
- src/Modules/Sample/composer.json
- 2026_09_24_062556_add_ulid_to_users_table.php
- ModuleController
- AppCron
- 2026_09_24_100000_add_language_to_users_table.php
- 2026_08_28_000004_create_attachments_table.php
- 2026_09_09_000001_create_notifications_table.php
- sampletasks/database/migrations/2026_09_01_000000_create_sample_tasks_table.php
- TwoFactorService
- SpineServiceProvider
- QrCodeService.php
- create_{{table}}_table.php
- Modules/Sample/database/migrations/2026_09_01_000000_create_sample_items_table.php
- SampleTasks/database/migrations/2026_09_01_000000_create_sample_tasks_table.php
- sampletasks/README.md
- SampleTasks/README.md
- Log{{Entity}}Activity.php
- DateFormatting
- Number
- AuthController
- MailService
- PaymentService
- QrCodeService
- NumberToWord
- MenuController
- .schema
- boilerplates/Sample/database/migrations/2026_09_01_000000_create_sample_items_table.php
- Authentication
- Cron
- Core services
- UI extension points
- EntityCode
- tests/verify.sh

## God Nodes (most connected - your core abstractions)
1. `ActivityLogService` - 40 edges
2. `SampleItem` - 31 edges
3. `ModuleService` - 30 edges
4. `Controller` - 29 edges
5. `SampleTask` - 28 edges
6. `API Reference` - 26 edges
7. `Log` - 24 edges
8. `ApiResponse` - 23 edges
9. `EntityCreated` - 22 edges
10. `EntityDeleted` - 22 edges

## Surprising Connections (you probably didn't know these)
- `Meta Endpoints` --references--> `MetaController`  [INFERRED]
  README.md → src/Http/Controllers/MetaController.php
- `Sanctum` --references--> `User`  [INFERRED]
  README.md → src/Models/User.php
- `Struktur` --references--> `HasLifecycleHooks`  [INFERRED]
  AGENTS.md → src/Traits/HasLifecycleHooks.php
- `Cron` --references--> `AppCron`  [INFERRED]
  docs/hook.md → src/Console/Commands/AppCron.php
- `Core services` --references--> `DateFormatting`  [INFERRED]
  docs/hook.md → src/Events/DateFormatting.php

## Import Cycles
- None detected.

## Communities (104 total, 35 thin omitted)

### Community 0 - "Attachment"
Cohesion: 0.08
Nodes (12): BinaryFileResponse, boilerplates, Cara pakai, Isi, Kontrak manifest, Prasyarat konsumen, FileDeleted, FileDeleting (+4 more)

### Community 1 - "ActivityLogService"
Cohesion: 0.11
Nodes (9): Authenticatable, SyncSampleStatusFromTasks, __construct(), Authenticatable, SyncSampleStatusFromTasks, LogTaskActivity, Authenticatable, ActivityLogService (+1 more)

### Community 2 - "Illuminate\Http\JsonResponse"
Cohesion: 0.13
Nodes (11): Illuminate\Http\JsonResponse, Modules\{{Studly}}\Models\{{Entity, activityLogs(), __construct(), destroy(), index(), show(), store() (+3 more)

### Community 3 - "GenericExport"
Cohesion: 0.08
Nodes (18): Barryvdh\DomPDF\Facade\Pdf, Illuminate\Contracts\Filesystem\Filesystem, Illuminate\Http\UploadedFile, Illuminate\Support\Collection, Illuminate\Support\Facades\Storage, Maatwebsite\Excel\Concerns\FromArray, Maatwebsite\Excel\Concerns\ShouldAutoSize, Maatwebsite\Excel\Concerns\ToCollection (+10 more)

### Community 4 - "Illuminate\Database\Eloquent\Model"
Cohesion: 0.10
Nodes (19): Adding a New Hook, Entity lifecycle (via `HasLifecycleHooks` trait), Event Registry, Hooks & Events, Listening to Events, ⚠️ Read this first: class ≠ wired hook, Related Laravel Primitives, Illuminate\Database\Eloquent\Model (+11 more)

### Community 6 - "PdfService"
Cohesion: 0.09
Nodes (14): Illuminate\Broadcasting\PrivateChannel, Illuminate\Bus\Queueable, Illuminate\Contracts\Broadcasting\ShouldBroadcastNow, Illuminate\Contracts\Queue\ShouldQueue, Illuminate\Foundation\Bus\Dispatchable, Illuminate\Mail\Mailable, Illuminate\Mail\Mailables\Content, Illuminate\Mail\Mailables\Envelope (+6 more)

### Community 7 - "Illuminate\Http\Request"
Cohesion: 0.09
Nodes (4): Illuminate\Http\Request, GdprController, MailController, NotificationController

### Community 8 - "Controller"
Cohesion: 0.09
Nodes (7): ApiResponse, Controller, ExcelController, PdfController, QrCodeController, SmsController, TagController

### Community 9 - "User"
Cohesion: 0.26
Nodes (8): Illuminate\Contracts\Auth\MustVerifyEmail, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, Laravel\Sanctum\HasApiTokens, Spatie\Permission\Traits\HasRoles, {closure#1}(), User

### Community 10 - "IpGuardService"
Cohesion: 0.09
Nodes (12): Closure, Exception, Illuminate\Database\Eloquent\Builder, Illuminate\Support\Facades\App, Illuminate\Support\Facades\Cache, RelationTypeNotRegisteredException, SetLocale, {closure#1}() (+4 more)

### Community 11 - "Illuminate\Foundation\Events\Dispatchable"
Cohesion: 0.05
Nodes (20): Email template system, Formatting & upload helpers, Illuminate\Foundation\Events\Dispatchable, AfterParseEmailTemplateMessage, BeforeEmailTemplateSend, BeforeParseEmailTemplateMessage, BeforeSendSimpleEmail, CurrentDateFormat (+12 more)

### Community 12 - ".config"
Cohesion: 0.12
Nodes (5): Spatie\Permission\Models\Permission, Spatie\Permission\Models\Role, PermissionController, RoleController, UserController

### Community 13 - "SampleItem"
Cohesion: 0.17
Nodes (3): SampleController, SampleItem, SampleController

### Community 14 - "SettingService"
Cohesion: 0.07
Nodes (13): Illuminate\Database\Eloquent\ModelNotFoundException, SettingUpdated, RepositoryInterface, Setting, SmsChannel, Builder, Collection, SettingService (+5 more)

### Community 15 - "Time"
Cohesion: 0.20
Nodes (3): Illuminate\Support\Carbon, What You Get, Time

### Community 16 - "ActivityLog"
Cohesion: 0.10
Nodes (9): Illuminate\Database\Eloquent\Relations\MorphMany, Illuminate\Database\Eloquent\Relations\MorphTo, Spatie\QueryBuilder\AllowedFilter, Spatie\QueryBuilder\QueryBuilder, ActivityLogController, ActivityLog, CustomMeta, {closure#1}() (+1 more)

### Community 17 - "HasLifecycleHooks"
Cohesion: 0.16
Nodes (5): Illuminate\Database\Eloquent\Concerns\HasUlids, Illuminate\Database\Eloquent\Relations\BelongsTo, SampleItem, SampleTask, HasLifecycleHooks

### Community 18 - "Str"
Cohesion: 0.13
Nodes (3): Konvensi penulisan, RegisterOtpService, Str

### Community 19 - "composer.json"
Cohesion: 0.07
Nodes (26): autoload, psr-4, config, sort-packages, description, extra, laravel, keywords (+18 more)

### Community 20 - "SampleTask"
Cohesion: 0.16
Nodes (4): Illuminate\Routing\Controller, SampleTaskController, SampleTask, SampleTaskController

### Community 21 - "API Reference"
Cohesion: 0.08
Nodes (26): Activity Log Endpoints, API Reference, Auth Endpoints, Authentication, Base URL, Broadcast Endpoints, Error Responses, Excel Endpoints (+18 more)

### Community 22 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.21
Nodes (11): {closure#1}(), {closure#2}(), down(), up(), Illuminate\Database\Schema\Blueprint, {closure#1}(), {closure#2}(), {closure#1}() (+3 more)

### Community 24 - "Installation Guide"
Cohesion: 0.12
Nodes (16): Configuration, Development Mode, Installation Guide, Issue: 401 on all API endpoints, Issue: Missing migration tables, Issue: RBAC sync fails, Next Steps, Requirements (+8 more)

### Community 25 - "Modules/Sample/database/migrations/2026_09_01_000001_add_fields_to_sample_items_table.php"
Cohesion: 0.40
Nodes (4): {closure#1}(), {closure#2}(), down(), up()

### Community 26 - "RbacService"
Cohesion: 0.24
Nodes (4): Illuminate\Console\Command, SyncCoreRbacCommand, SyncRbacCommand, RbacService

### Community 27 - "SpineServiceProvider.php"
Cohesion: 0.25
Nodes (4): Illuminate\Support\Facades\Broadcast, Spatie\Permission\Middleware\PermissionMiddleware, Spatie\Permission\Middleware\RoleMiddleware, Spatie\Permission\Middleware\RoleOrPermissionMiddleware

### Community 28 - "Modules"
Cohesion: 0.17
Nodes (12): Creating a Module, Enabling a Module, Manifest File, Module API Routes, Module Commands, Module Dependencies, Module Events, Module Structure (+4 more)

### Community 29 - "Laravel Spine"
Cohesion: 0.17
Nodes (12): Architecture, Contributing, Core Principles, Development, Directory Structure, Documentation, Installation, Laravel Spine (+4 more)

### Community 30 - "Log"
Cohesion: 0.13
Nodes (6): Illuminate\Support\Facades\Log, LogFileActivity, Log, FileUploaded, LogFileActivity, LogSettingChange

### Community 31 - "MailService.php"
Cohesion: 0.18
Nodes (5): Illuminate\Support\Facades\Artisan, Illuminate\Support\Facades\DB, Illuminate\Support\Facades\Mail, Illuminate\Support\Facades\Notification, Illuminate\Support\Facades\Queue

### Community 32 - "Authentication"
Cohesion: 0.18
Nodes (11): 1. User Model, 2. Auth Controller (Consumer), 2FA Support (Optional), 3. Register Routes, Authentication, Multi-Language Support, Overview, Related (+3 more)

### Community 33 - "Illuminate\Database\Migrations\Migration"
Cohesion: 0.22
Nodes (7): {closure#1}(), down(), up(), {closure#1}(), down(), up(), Illuminate\Database\Migrations\Migration

### Community 34 - "RBAC — Role & Permission"
Cohesion: 0.24
Nodes (9): Catatan operasional, Kebijakan, Kontrak manifest — key `rbac`, RBAC — Role & Permission, Scope & environment (data), Setup konsumen, Sinkronisasi, Wildcard resolution (+1 more)

### Community 36 - "Laravel Spine Documentation"
Cohesion: 0.18
Nodes (11): Architecture, Event Hooks (Partial List), Getting Started, Installation, Key Commands, Laravel Spine Documentation, License, Project Structure (+3 more)

### Community 37 - "ModuleService"
Cohesion: 0.18
Nodes (6): Nwidart\Modules\Contracts\ActivatorInterface, Nwidart\Modules\Contracts\RepositoryInterface, Nwidart\Modules\Module, {closure#1}(), {closure#2}(), ModuleService

### Community 38 - "PublicController"
Cohesion: 0.17
Nodes (5): Illuminate\Support\Facades\File, Illuminate\Support\Facades\Lang, PublicController, SystemController, TranslationController

### Community 40 - "Illuminate\Database\Seeder"
Cohesion: 0.25
Nodes (4): Illuminate\Database\Seeder, AdminUserSeeder, SampleItemsSeeder, SampleTasksSeeder

### Community 41 - "GdprService.php"
Cohesion: 0.17
Nodes (3): {closure#3}(), {closure#4}(), GdprService

### Community 42 - "Illuminate\Support\Facades\Schema"
Cohesion: 0.22
Nodes (6): {closure#1}(), down(), up(), Illuminate\Support\Facades\Schema, Spatie\Permission\Contracts\Permission, Spatie\Permission\Exceptions\RoleDoesNotExist

### Community 43 - "AGENTS.md — laravelspine (package `spine/laravel-spine`)"
Cohesion: 0.25
Nodes (7): AGENTS.md — laravelspine (package `spine/laravel-spine`), 🚨 ATURAN PALING PENTING, Git, Project, Struktur, Testing & lint, Tidak ada `artisan` di sini

### Community 44 - "UserFactory.php"
Cohesion: 0.32
Nodes (3): Illuminate\Support\Facades\Hash, Illuminate\Support\Str, {closure#2}()

### Community 49 - "src/Modules/SampleTasks/composer.json"
Cohesion: 0.14
Nodes (13): autoload, classmap, psr-4, description, extra, laravel, providers, license (+5 more)

### Community 50 - "Illuminate\Support\ServiceProvider"
Cohesion: 0.08
Nodes (12): Illuminate\Support\Facades\Event, Illuminate\Support\ServiceProvider, LogEntityActivity, Authenticatable, LogSettingChange, SampleServiceProvider, LogTaskActivity, Authenticatable (+4 more)

### Community 51 - "boilerplates/Sample/composer.json"
Cohesion: 0.15
Nodes (12): autoload, psr-4, description, extra, laravel, providers, license, name (+4 more)

### Community 52 - "sampletasks/composer.json"
Cohesion: 0.15
Nodes (12): autoload, psr-4, description, extra, laravel, providers, license, name (+4 more)

### Community 54 - "UserFactory"
Cohesion: 0.40
Nodes (3): Illuminate\Database\Eloquent\Factories\Factory, UserFactory, static

### Community 55 - "Consumer Setup Notes"
Cohesion: 0.33
Nodes (6): 401 Responses, Auth, Consumer Setup Notes, Meta Endpoints, Modules, Sanctum

### Community 56 - "src/Console/stubs/composer.json"
Cohesion: 0.15
Nodes (12): autoload, psr-4, description, extra, laravel, providers, license, name (+4 more)

### Community 57 - "src/Modules/Sample/composer.json"
Cohesion: 0.15
Nodes (12): autoload, psr-4, description, extra, laravel, providers, license, name (+4 more)

### Community 58 - "2026_09_24_062556_add_ulid_to_users_table.php"
Cohesion: 0.40
Nodes (4): {closure#1}(), {closure#2}(), down(), up()

### Community 59 - "ModuleController"
Cohesion: 0.07
Nodes (10): Modules, Spatie\Permission\PermissionRegistrar, ModuleActivated, ModuleDeactivated, ModuleInstalled, ModulesLoaded, ModuleUninstalled, ModuleController (+2 more)

### Community 61 - "2026_09_24_100000_add_language_to_users_table.php"
Cohesion: 0.40
Nodes (4): {closure#1}(), {closure#2}(), down(), up()

### Community 62 - "2026_08_28_000004_create_attachments_table.php"
Cohesion: 0.50
Nodes (3): {closure#1}(), down(), up()

### Community 63 - "2026_09_09_000001_create_notifications_table.php"
Cohesion: 0.50
Nodes (3): {closure#1}(), down(), up()

### Community 64 - "sampletasks/database/migrations/2026_09_01_000000_create_sample_tasks_table.php"
Cohesion: 0.50
Nodes (3): {closure#1}(), down(), up()

### Community 67 - "QrCodeService.php"
Cohesion: 0.33
Nodes (4): Endroid\QrCode\Encoding\Encoding, Endroid\QrCode\QrCode, Endroid\QrCode\Writer\PngWriter, Endroid\QrCode\Writer\SvgWriter

### Community 68 - "create_{{table}}_table.php"
Cohesion: 0.50
Nodes (3): {closure#1}(), down(), up()

### Community 69 - "Modules/Sample/database/migrations/2026_09_01_000000_create_sample_items_table.php"
Cohesion: 0.50
Nodes (3): {closure#1}(), down(), up()

### Community 70 - "SampleTasks/database/migrations/2026_09_01_000000_create_sample_tasks_table.php"
Cohesion: 0.50
Nodes (3): {closure#1}(), down(), up()

### Community 75 - "Log{{Entity}}Activity.php"
Cohesion: 0.50
Nodes (7): created(), deleted(), describe(), label(), Authenticatable, updated(), user()

### Community 89 - "MailService"
Cohesion: 0.12
Nodes (5): Illuminate\Notifications\Messages\MailMessage, Illuminate\Notifications\Notification, BaseNotification, GenericMailNotification, MailService

### Community 90 - "PaymentService"
Cohesion: 0.09
Nodes (7): Illuminate\Support\Facades\Config, Illuminate\Support\Facades\Http, PaymentController, PaymentGatewayInterface, StripePaymentGateway, {closure#1}(), PaymentService

### Community 91 - "QrCodeService"
Cohesion: 0.33
Nodes (3): Endroid\QrCode\ErrorCorrectionLevel, ErrorCorrectionLevel, QrCodeService

### Community 106 - ".schema"
Cohesion: 0.15
Nodes (12): {closure#1}(), down(), up(), {closure#1}(), down(), up(), down(), up() (+4 more)

### Community 107 - "boilerplates/Sample/database/migrations/2026_09_01_000000_create_sample_items_table.php"
Cohesion: 0.50
Nodes (3): {closure#1}(), down(), up()

### Community 125 - "Authentication"
Cohesion: 0.12
Nodes (6): Authentication, AdminAuthInit, AdminInitialized, AdminInitializing, StaffLogIn, StaffLogOut

### Community 128 - "Cron"
Cohesion: 0.20
Nodes (4): Cron, CronAfter, CronBefore, CronTasks

### Community 129 - "Core services"
Cohesion: 0.08
Nodes (10): Core services, Notifications, Veto points, EntityCreating, MailSending, MailTested, MailTesting, NotificationCreating (+2 more)

### Community 133 - "UI extension points"
Cohesion: 0.08
Nodes (9): UI extension points, DashboardWidgets, PermissionCatalog, ProfileTabs, QuickActions, SettingsTabs, SettingsUpdating, SetupMenu (+1 more)

## Knowledge Gaps
- **152 isolated node(s):** `name`, `description`, `keywords`, `license`, `type` (+147 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 412 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **35 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Laravel Spine` connect `Laravel Spine` to `Time`, `Consumer Setup Notes`, `installation.md`?**
  _High betweenness centrality (0.098) - this node is a cross-community bridge._
- **Why does `What You Get` connect `Time` to `ActivityLog`, `Str`, `Laravel Spine`?**
  _High betweenness centrality (0.076) - this node is a cross-community bridge._
- **Why does `ActivityLogService` connect `ActivityLogService` to `Illuminate\Http\JsonResponse`, `Illuminate\Database\Eloquent\Model`, `Log{{Entity}}Activity.php`, `SampleItem`, `ActivityLog`, `LogEntityActivity`, `Illuminate\Support\ServiceProvider`, `SampleTask`, `Log`?**
  _High betweenness centrality (0.073) - this node is a cross-community bridge._
- **What connects `name`, `description`, `keywords` to the rest of the system?**
  _152 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Attachment` be split into smaller, more focused modules?**
  _Cohesion score 0.08108108108108109 - nodes in this community are weakly interconnected._
- **Should `ActivityLogService` be split into smaller, more focused modules?**
  _Cohesion score 0.11231884057971014 - nodes in this community are weakly interconnected._
- **Should `Illuminate\Http\JsonResponse` be split into smaller, more focused modules?**
  _Cohesion score 0.13405797101449277 - nodes in this community are weakly interconnected._