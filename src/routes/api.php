<?php

use Illuminate\Support\Facades\Route;
use kha333n\crudmodule\Http\Controllers\CrudController;

Route::group([
    'prefix' => config('crudmodule.route_prefix'),
    'middleware' => config('crudmodule.middleware'),
], function () {
    Route::prefix('/{model}')->group(function () {
        Route::get('/', [CrudController::class, 'index']);
        Route::post('/', [CrudController::class, 'store']);
        Route::get('/{id}', [CrudController::class, 'show']);
        Route::put('/{id}', [CrudController::class, 'update']);
        Route::delete('/{id}', [CrudController::class, 'destroy']);
        Route::delete('/{id}/force', [CrudController::class, 'forceDestroy']);
        Route::patch('/{id}', [CrudController::class, 'restore']);
    });
});

