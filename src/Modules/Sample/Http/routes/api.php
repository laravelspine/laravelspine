<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Sample\Http\Controllers\SampleController;

/*
|--------------------------------------------------------------------------
| CONTOH ROUTE MODUL
|--------------------------------------------------------------------------
| Modul mengikuti konvensi core: prefix 'api' + 'v1' + auth:sanctum.
| Endpoint modul jadi:
|   GET    /api/v1/sample
|   POST   /api/v1/sample
|   DELETE /api/v1/sample/{id}
|
| (Tanpa middleware ini route modul jatuh ke /sample — di luar kontrak API.)
*/

Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    Route::prefix('sample')->group(function () {
        Route::get('/', [SampleController::class, 'index']);
        Route::post('/', [SampleController::class, 'store']);
        Route::get('/{id}', [SampleController::class, 'show'])->whereNumber('id');
        Route::put('/{id}', [SampleController::class, 'update'])->whereNumber('id');
        Route::get('/{id}/activity-logs', [SampleController::class, 'activityLogs'])->whereNumber('id');
        Route::delete('/{id}', [SampleController::class, 'destroy'])->whereNumber('id');
    });
});
