<?php

use Illuminate\Support\Facades\Route;

use App\Web\Permission\PermissionController;

Route::group(['middleware' => 'auth'], function() {
    Route::controller(PermissionController::class)->group(function() {
        Route::get('/permissions',  'index')->name('permissions.index');
        Route::get('/permissions/datalist', 'getPermissions');
        Route::get('permissions/create', 'create');
        Route::get('permissions/{id}/edit', 'edit');
        Route::post('/permissions', 'createPermission');
        Route::post('/permissions/update/{id}', 'updatePermission');
    });
});