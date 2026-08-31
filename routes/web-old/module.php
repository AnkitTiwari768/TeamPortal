<?php
use Illuminate\Support\Facades\Route;
use App\Modules\Module\ModuleController;

Route::group(['middleware' => 'auth'], function() {
    Route::controller(StateController::class)->group(function() {
    Route::get('/modules', [ModuleController::class, 'index']);
    Route::get('/modules/datalist', [ModuleController::class, 'datalist']);
    Route::post('/modules/update/{id}', [ModuleController::class, 'update']);
    Route::resource('/modules', ModuleController::class);
    });
});