<?php

use Illuminate\Support\Facades\Route;

use App\Web\PmvProductCategory\PmvProductCategoryController;

Route::group(['middleware' => 'auth.session'], function () {
    Route::controller(PmvProductCategoryController::class)->group(function () {
         Route::get('/pmv-category-list',  'index')->name('pmv-category-list');
         Route::get('/pmvCategory/datalist', 'getCategories');
         Route::get('pmv-category-create', 'create')->name('category-create');
         Route::get('pmv-categories/{id}/edit', 'edit');
        Route::post('/pmv-categories-create', 'pmvCreateCategory');
        Route::post('/pmv-categories-update/{id}', 'pmvUpdateCategory');
    });
});
