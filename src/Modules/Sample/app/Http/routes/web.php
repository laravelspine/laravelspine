<?php

use Illuminate\Support\Facades\Route;
use Modules\Sample\app\Http\Controllers\SampleController;

/*
|--------------------------------------------------------------------------
| WEB ROUTES MODUL
|--------------------------------------------------------------------------
*/

Route::middleware(['web'])->group(function () {
    Route::prefix('sample')->name('sample.')->group(function () {
        Route::get('/', [SampleController::class, 'indexPage'])->name('list');
        Route::get('/create', [SampleController::class, 'createPage'])->name('create');
        Route::get('/{id}/edit', [SampleController::class, 'editPage'])->name('edit')->whereNumber('id');
        Route::get('/{id}', [SampleController::class, 'detailPage'])->name('detail')->whereNumber('id');
    });
});
