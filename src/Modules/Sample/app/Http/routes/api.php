<?php

use Illuminate\Support\Facades\Route;
use Modules\Sample\app\Http\Controllers\SampleController;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::prefix('sample')->group(function () {
        Route::get('/', [SampleController::class, 'index']);
        Route::post('/', [SampleController::class, 'store']);
        Route::get('/{id}', [SampleController::class, 'show'])->whereNumber('id');
        Route::put('/{id}', [SampleController::class, 'update'])->whereNumber('id');
        Route::get('/{id}/activity-logs', [SampleController::class, 'activityLogs'])->whereNumber('id');
        Route::delete('/{id}', [SampleController::class, 'destroy'])->whereNumber('id');
    });
});
