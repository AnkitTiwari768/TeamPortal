<?php

use Illuminate\Support\Facades\Route;

use App\Web\Components\ComponentsController;

Route::group(['middleware' => 'auth'], function() {
    Route::controller(ComponentsController::class)->group(function() {
        Route::get('/components',  'index')->name('components.index');
        Route::get('/components/datalist', 'getComponents');
        Route::get('components/create', 'create');
        Route::get('components/{id}/edit', 'edit');
        Route::post('/components', 'createComponents');
        Route::post('/components/update/{id}', 'updateComponents');
    });
});