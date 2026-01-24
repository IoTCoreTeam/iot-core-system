<?php

use Illuminate\Support\Facades\Route;
use Modules\ControlModule\Http\Controllers\GatewayController;
use Modules\ControlModule\Http\Controllers\NodeManagementController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:api', 'admin'])->prefix('v1')->group(function () {

    Route::prefix('gateways')->group(function (): void {
        Route::get('/', [GatewayController::class, 'index'])->name('gateways.index');
        Route::post('/register', [GatewayController::class, 'registation'])->name('gateways.register');
        Route::post('{external_id}/deactivate', [GatewayController::class, 'deactivation'])->name('gateways.deactivate');
        Route::delete('{external_id}', [GatewayController::class, 'delete'])->name('gateways.delete');
    });
});

Route::get('available-nodes', [NodeManagementController::class, 'index'])->name('available-nodes.index');
