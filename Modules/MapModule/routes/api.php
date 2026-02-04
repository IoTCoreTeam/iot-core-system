<?php

use Illuminate\Support\Facades\Route;
use Modules\MapModule\Http\Controllers\AreaController;
use Modules\MapModule\Http\Controllers\MapController;
use Modules\MapModule\Http\Controllers\AccessPointController;
use Modules\MapModule\Http\Controllers\FingerprintController;
use Modules\MapModule\Http\Controllers\FingerprintRssiController;


Route::prefix('v1/map-module')->group(function () {
    Route::get('areas', [AreaController::class, 'index']);
    Route::post('areas', [AreaController::class, 'store']);
    Route::put('areas/{id}', [AreaController::class, 'update']);
    Route::delete('areas/{id}', [AreaController::class, 'destroy']);

    Route::get('maps', [MapController::class, 'index']);
    Route::post('maps', [MapController::class, 'store']);
    Route::put('maps/{id}', [MapController::class, 'update']);
    Route::delete('maps/{id}', [MapController::class, 'destroy']);
});
