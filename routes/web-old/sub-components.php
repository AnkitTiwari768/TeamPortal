<?php

use Illuminate\Support\Facades\Route;

use App\Web\SubComponents\SubComponentsController;

Route::group(['middleware' => 'auth'], function() {
    Route::controller(SubComponentsController::class)->group(function() {
        Route::get('/sub-components',  'index')->name('sub-components.index');
        Route::get('/sub-components/datalist', 'getSubComponents');
        Route::get('sub-components/create', 'create');
        Route::get('sub-components/{id}/edit', 'edit');
        Route::post('/sub-components', 'createSubComponents');
        Route::post('/sub-components/update/{id}', 'updateSubComponents');
    });
    
    Route::get('getComponent/{id}', [SubComponentsController::class,'getComponent']);

    Route::get('getSubComponent/{id}', [SubComponentsController::class,'getSubComponentByComponent']);
    
});