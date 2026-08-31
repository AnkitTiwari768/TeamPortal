<?php

use Illuminate\Support\Facades\Route;

use App\Web\Category\CategoryController;

Route::group(['middleware' => 'auth.session'], function () {
    Route::controller(CategoryController::class)->group(function () {
        Route::get('/categories',  'index')->name('categories.index');
        Route::get('/categories/datalist', 'getCategories');
        Route::get('categories/create', 'create');
        Route::get('categories/{id}/edit', 'edit');
        Route::post('/categories', 'createCategory');
        Route::post('/categories/update/{id}', 'updateCategory');
    });
});
