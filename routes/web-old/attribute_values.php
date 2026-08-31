<?php

use Illuminate\Support\Facades\Route;
use App\Web\Masters\AttributeValues\AttributeValuesController;

Route::middleware('auth')->group(function () {
    Route::controller(AttributeValuesController::class)->group(function () {
        Route::get('masters/{dynamicSlug}', 'index')->name('attribute_values.index');
        Route::get('masters/{dynamicSlug}/datalist', 'index')->name('attribute_values.datalist');
        Route::get('masters/{dynamicSlug}/create', 'create')->name('attribute_values.create');
        Route::post('masters/{dynamicSlug}', 'store')->name('attribute_values.store');
        Route::get('masters/{dynamicSlug}/{id}/edit', 'edit')->name('attribute_values.edit');
        Route::post('masters/{dynamicSlug}/update/{id}', 'update')->name('attribute_values.update');
        Route::get('attribute_values/details', 'getDetails')->name('attribute_values.details');
        Route::get('attribute_values/{id}', 'findById')->name('attribute_values.show');
        Route::get('/get-attribute-values/{attributeId}',  'getByAttributeId')->name('attribute.values.by.attribute');
    });

});