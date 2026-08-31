<?php

use Illuminate\Support\Facades\Route;

use App\Web\Designation\DesignationController;

Route::group(['middleware' => 'auth'], function() {
    Route::controller(DesignationController::class)->group(function() {
        Route::get('/services',  'index')->name('services.index');
        Route::get('/services/datalist', 'getDesignations');
        Route::get('services/create', 'create');
        Route::get('services/{id}/edit', 'edit');
        Route::post('/services', 'createDesignation');
        Route::post('/services/update/{id}', 'updateDesignation');
    });
 
});