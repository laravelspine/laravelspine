<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\SampleTasks\Http\Controllers\SampleTaskController;

/*
|--------------------------------------------------------------------------
| CONTOH ROUTE MODUL CHILD
|--------------------------------------------------------------------------
| Konvensi sama dengan core: prefix 'api' + 'v1' + auth:sanctum.
|   GET    /api/v1/sample-tasks?sample_item_id={id}   (list child per parent)
|   POST   /api/v1/sample-tasks
|   GET    /api/v1/sample-tasks/{id}
|   PUT    /api/v1/sample-tasks/{id}
|   DELETE /api/v1/sample-tasks/{id}
*/

Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    Route::prefix('sample-tasks')->group(function () {
        Route::get('/', [SampleTaskController::class, 'index']);
        Route::post('/', [SampleTaskController::class, 'store']);
        Route::get('/{id}', [SampleTaskController::class, 'show'])->whereNumber('id');
        Route::put('/{id}', [SampleTaskController::class, 'update'])->whereNumber('id');
        Route::get('/{id}/activity-logs', [SampleTaskController::class, 'activityLogs'])->whereNumber('id');
        Route::delete('/{id}', [SampleTaskController::class, 'destroy'])->whereNumber('id');
    });
});
