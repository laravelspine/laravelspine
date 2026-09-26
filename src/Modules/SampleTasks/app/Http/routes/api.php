<?php

use Illuminate\Support\Facades\Route;
use Modules\SampleTasks\app\Http\Controllers\SampleTaskController;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::prefix('sample-tasks')->group(function () {
        Route::get('/', [SampleTaskController::class, 'index']);
        Route::post('/', [SampleTaskController::class, 'store']);
        Route::get('/{id}', [SampleTaskController::class, 'show'])->whereNumber('id');
        Route::put('/{id}', [SampleTaskController::class, 'update'])->whereNumber('id');
        Route::get('/{id}/activity-logs', [SampleTaskController::class, 'activityLogs'])->whereNumber('id');
        Route::delete('/{id}', [SampleTaskController::class, 'destroy'])->whereNumber('id');
    });
});
