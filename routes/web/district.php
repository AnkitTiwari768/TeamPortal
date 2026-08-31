<?php

use Illuminate\Support\Facades\Route;

use App\Web\District\DistrictController;

Route::controller(DistrictController::class)->group(function() {
	Route::get('getDistrict/{id}', [DistrictController::class,'getByState']);
});

Route::group(['middleware' => 'auth'], function() {
    Route::controller(DistrictController::class)->group(function() {
        Route::get('/districts',  'index')->name('district.index');
        Route::get('/districts/datalist', 'getDistricts');
        Route::get('districts/create', 'create');
        Route::get('districts/{id}/edit', 'edit');
        Route::post('/districts', 'createDistrict');
        Route::post('/districts/update/{id}', 'updateDistrict');
    });
    
    
    
});