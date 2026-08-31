<?php

use Illuminate\Support\Facades\Route;

use App\Web\AovCategory\AovCategoryController;

Route::group(['middleware' => 'auth.session'], function () {
    Route::controller(AovCategoryController::class)->group(function () {
        Route::get('/aov-categories',  'index')->name('aov-categories-index');
        Route::get('/categories/datalist', 'getCategories');
        Route::get('aov-categories-create', 'create')->name('categories-create');
        Route::get('aov-categories/{id}/edit', 'edit');
        Route::post('/aov-categories-create', 'createCategory');
        Route::post('/aov-categories-update/{id}', 'updateCategory');
        Route::get('/view-aov-category/{id}', 'view');
        Route::delete('/delete-aov-category/{id}', 'deleteCategory');
    });
});
