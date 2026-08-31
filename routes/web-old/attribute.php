<?php

use Illuminate\Support\Facades\Route;
use App\Web\Masters\Attributes\AttributesController;

Route::group(['middleware' => 'auth.session'], function () {
    Route::controller(AttributesController::class)->group(function () {
            Route::get('/attributes',  'index')->name('attributes.index');
            Route::get('/attributes/datalist', 'getDataattributes')->name('attributes.datalist');
            Route::get('attributes-create', 'create')->name('attributes-create');
            Route::get('attributes/{id}/edit', 'edit')->name('attributes.edit');
            Route::post('/attributes-create', 'attributesCreate')->name('attributes-create');
            Route::post('/attributes-update/{id}', 'attributesUpdate')->name('attributes-update');
    });
});

