<?php

use Illuminate\Support\Facades\Route;
use Spine\Http\Controllers\ActivityLogController;
use Spine\Http\Controllers\AuthController;
use Spine\Http\Controllers\BroadcastController;
use Spine\Http\Controllers\ExcelController;
use Spine\Http\Controllers\FileController;
use Spine\Http\Controllers\GdprController;
use Spine\Http\Controllers\MailController;
use Spine\Http\Controllers\MetaController;
use Spine\Http\Controllers\ModuleController;
use Spine\Http\Controllers\NumberToWordController;
use Spine\Http\Controllers\PaymentController;
use Spine\Http\Controllers\PdfController;
use Spine\Http\Controllers\QrCodeController;
use Spine\Http\Controllers\RelationController;
use Spine\Http\Controllers\SettingController;
use Spine\Http\Controllers\SmsController;
use Spine\Http\Controllers\SystemController;
use Spine\Http\Controllers\TagController;

// Seluruh API infrastruktur di-versi-kan (tanpa kecuali).
// v1 = kontrak stabil pertama; breaking change berikutnya → v2, dst.
// Login/register publik; sisanya butuh Sanctum token.
Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Auth (terautentikasi)
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Settings (schema SEBELUM {key} supaya tidak tertangkap wildcard)
    Route::get('/settings/schema', [SettingController::class, 'schema']);
    Route::get('/settings/{key}', [SettingController::class, 'show']);
    Route::put('/settings/{key}', [SettingController::class, 'upsert']);
    Route::delete('/settings/{key}', [SettingController::class, 'destroy']);
    Route::post('/settings/bulk', [SettingController::class, 'bulk']);

    // Activity Logs (resource REST, multi-tenant)
    Route::apiResource('activity-logs', ActivityLogController::class)->only([
        'index', 'show', 'store', 'destroy',
    ]);

    // Custom Meta (polymorphic, key-value per entity)
    Route::get('/meta/{type}/{id}', [MetaController::class, 'index']);
    Route::post('/meta/{type}/{id}', [MetaController::class, 'store']);
    Route::get('/meta/{type}/{id}/{key}', [MetaController::class, 'show']);
    Route::put('/meta/{type}/{id}/{key}', [MetaController::class, 'update']);
    Route::delete('/meta/{type}/{id}/{key}', [MetaController::class, 'destroy']);

    // Relations (inti resolver; tipe di-register module via hook)
    Route::get('/relations/types', [RelationController::class, 'types']);
    Route::get('/relations/{type}/{id}', [RelationController::class, 'show']);

    // NumberToWord (angka → terbilang untuk invoice/PDF)
    Route::post('/number-to-word/convert', [NumberToWordController::class, 'convert']);
    Route::post('/number-to-word/convert-indian', [NumberToWordController::class, 'convertIndian']);

    // Modules (discovery & management)
    Route::get('/modules', [ModuleController::class, 'index']);
    Route::get('/modules/enabled', [ModuleController::class, 'enabled']);
    Route::post('/modules/install', [ModuleController::class, 'install']);
    Route::get('/modules/{name}', [ModuleController::class, 'show']);
    Route::get('/modules/{name}/manifest', [ModuleController::class, 'manifest']);
    Route::get('/modules/{name}/status', [ModuleController::class, 'status']);
    Route::post('/modules/{name}/enable', [ModuleController::class, 'enable']);
    Route::post('/modules/{name}/disable', [ModuleController::class, 'disable']);
    Route::post('/modules/{name}/uninstall', [ModuleController::class, 'uninstall']);

    // System (utilitas aplikasi)
    Route::get('/system/languages', [SystemController::class, 'languages']);

    // Files (upload Laravel Storage + metadata)
    Route::get('/files/limits', [FileController::class, 'limits']);
    Route::post('/files', [FileController::class, 'store']);
    Route::get('/files/{id}', [FileController::class, 'show']);
    Route::get('/files/{id}/download', [FileController::class, 'download']);
    Route::get('/files/{id}/preview', [FileController::class, 'preview']);
    Route::delete('/files/{id}', [FileController::class, 'destroy']);

    // Mail
    Route::post('/mail/send', [MailController::class, 'send']);
    Route::post('/mail/test', [MailController::class, 'test']);
    Route::post('/mail/notify', [MailController::class, 'notify']);
    Route::post('/mail/notify-many', [MailController::class, 'notifyMany']);
    Route::post('/mail/retry', [MailController::class, 'retryQueue']);
    Route::post('/mail/cleanup', [MailController::class, 'cleanUpQueue']);
    Route::get('/mail/queue', [MailController::class, 'queueStatus']);

    // Payment Gateway abstraction
    Route::get('/payment/gateways', [PaymentController::class, 'index']);
    Route::post('/payment/intent', [PaymentController::class, 'createIntent']);

    // GDPR / data privacy
    Route::get('/gdpr/export', [GdprController::class, 'export']);
    Route::post('/gdpr/anonymize', [GdprController::class, 'anonymize']);
    Route::post('/gdpr/delete', [GdprController::class, 'delete']);

    // PDF
    Route::post('/pdf/generate', [PdfController::class, 'generate']);
    Route::post('/pdf/from-html', [PdfController::class, 'fromHtml']);
    Route::post('/pdf/bulk-export', [PdfController::class, 'bulkExport']);

    // SMS (Twilio/Clickatell/Msg91 abstraction)
    Route::post('/sms/send', [SmsController::class, 'send']);
    Route::get('/sms/drivers', [SmsController::class, 'drivers']);

    // QR Code
    Route::post('/qr-code/generate', [QrCodeController::class, 'generate']);

    // Excel import/export
    Route::post('/excel/export', [ExcelController::class, 'export']);
    Route::post('/excel/import', [ExcelController::class, 'import']);

    // Tags
    Route::get('/tags', [TagController::class, 'index']);
    Route::post('/tags', [TagController::class, 'store']);
    Route::delete('/tags/{id}', [TagController::class, 'destroy']);

    // Broadcasting (Laravel Broadcasting/Reverb)
    Route::get('/broadcast/config', [BroadcastController::class, 'config']);
    Route::post('/broadcast/test', [BroadcastController::class, 'sendTest']);
});
