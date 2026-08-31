<?php

use Illuminate\Support\Facades\Route;
use App\Domain\ComponentUtilizationMapping\ComponentUtilizationMappingController;

Route::prefix('component-utilization-mapping')->group(function () {
    Route::get('/', [ComponentUtilizationMappingController::class, 'index'])->name('component-utilization.index');
    Route::post('/get-components', [ComponentUtilizationMappingController::class, 'getUtilizedComponents'])->name('component-utilization.get-components');
    Route::post('/store', [ComponentUtilizationMappingController::class, 'store'])->name('component-utilization.store');
});
