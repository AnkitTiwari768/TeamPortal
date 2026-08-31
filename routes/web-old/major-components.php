<?php

use Illuminate\Support\Facades\Route;

use App\Web\MajorComponents\MajorComponentsController;

Route::group(['middleware' => 'auth'], function() {
    Route::controller(MajorComponentsController::class)->group(function() {
        Route::get('/major-components',  'index')->name('major-components.index');
        Route::get('/major-components/datalist', 'getMajorComponents');
        Route::get('major-components/create', 'create');
        Route::get('major-components/{id}/edit', 'edit');
        Route::post('/major-components', 'createMajorComponents');
        Route::post('/major-components/update/{id}', 'updateMajorComponents');
    });
});