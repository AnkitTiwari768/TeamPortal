<?php

use Illuminate\Support\Facades\Route;

use App/Web/SubDistrict/SubDistrictController;

Route::controller(SubDistrictController::class)->group(function() {
	Route::get('get-sub-district/{id}','getByDistrict');
});

Route::group(['middleware' => 'auth'], function() {
    Route::controller(SubDistrictController::class)->group(function() {
        Route::get('/districts',  'index')->name('district.index');
        Route::get('/districts/datalist', 'getDistricts');
        Route::get('districts/create', 'create');
        Route::get('districts/{id}/edit', 'edit');
        Route::post('/districts', 'createDistrict');
        Route::post('/districts/update/{id}', 'updateDistrict');
    });   
});