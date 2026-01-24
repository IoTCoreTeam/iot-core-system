<?php

use Illuminate\Support\Facades\Route;
use Modules\ControlModule\Http\Controllers\ControlModuleController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('controlmodules', ControlModuleController::class)->names('controlmodule');
});
