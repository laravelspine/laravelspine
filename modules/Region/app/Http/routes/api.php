<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Region\app\Http\Controllers\RegionController;

/*
 |--------------------------------------------------------------------------
 | ROUTE MODUL Region — konvensi core: api/v1 + auth:sanctum
 |--------------------------------------------------------------------------
 |   GET    /api/v1/regions/provinces                    (list)
 |   POST   /api/v1/regions/provinces                    (create)
 |   GET    /api/v1/regions/provinces/{id}               (detail)
 |   PUT    /api/v1/regions/provinces/{id}               (update)
 |   DELETE /api/v1/regions/provinces/{id}               (delete)
 |   GET    /api/v1/regions/provinces/{id}/regencies     (list by province)
 |   GET    /api/v1/regions/regencies                     (list, ?province_id=X)
 |   POST   /api/v1/regions/regencies                     (create)
 |   GET    /api/v1/regions/regencies/{id}                (detail)
 |   PUT    /api/v1/regions/regencies/{id}                (update)
 |   DELETE /api/v1/regions/regencies/{id}                (delete)
 */

Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    Route::prefix('regions')->group(function () {
        // Provinces
        Route::get('/provinces', [RegionController::class, 'provinces']);
        Route::post('/provinces', [RegionController::class, 'storeProvince']);
        Route::get('/provinces/{id}', [RegionController::class, 'showProvince'])->whereNumber('id');
        Route::put('/provinces/{id}', [RegionController::class, 'updateProvince'])->whereNumber('id');
        Route::delete('/provinces/{id}', [RegionController::class, 'destroyProvince'])->whereNumber('id');
        Route::get('/provinces/{id}/regencies', [RegionController::class, 'provinceRegencies'])->whereNumber('id');
        Route::get('/provinces/{id}/activity-logs', [RegionController::class, 'provinceActivityLogs'])->whereNumber('id');

        // Regencies
        Route::get('/regencies', [RegionController::class, 'regencies']);
        Route::post('/regencies', [RegionController::class, 'storeRegency']);
        Route::get('/regencies/{id}', [RegionController::class, 'showRegency'])->whereNumber('id');
        Route::put('/regencies/{id}', [RegionController::class, 'updateRegency'])->whereNumber('id');
        Route::delete('/regencies/{id}', [RegionController::class, 'destroyRegency'])->whereNumber('id');
    });
});