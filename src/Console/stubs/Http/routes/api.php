<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\{{Studly}}\Http\Controllers\{{Entity}}Controller;

/*
|--------------------------------------------------------------------------
| ROUTE MODUL (konvensi core: api/v1 + auth:sanctum)
|--------------------------------------------------------------------------
|   GET    /api/v1/{{route}}              (list)
|   POST   /api/v1/{{route}}
|   GET    /api/v1/{{route}}/{id}
|   PUT    /api/v1/{{route}}/{id}
|   GET    /api/v1/{{route}}/{id}/activity-logs
|   DELETE /api/v1/{{route}}/{id}
*/

Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    Route::prefix('{{route}}')->group(function () {
        Route::get('/', [{{Entity}}Controller::class, 'index']);
        Route::post('/', [{{Entity}}Controller::class, 'store']);
        Route::get('/{id}', [{{Entity}}Controller::class, 'show'])->whereNumber('id');
        Route::put('/{id}', [{{Entity}}Controller::class, 'update'])->whereNumber('id');
        Route::get('/{id}/activity-logs', [{{Entity}}Controller::class, 'activityLogs'])->whereNumber('id');
        Route::delete('/{id}', [{{Entity}}Controller::class, 'destroy'])->whereNumber('id');
    });
});
