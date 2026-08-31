<?php

use Illuminate\Support\Facades\Route;

use App\Web\{
    Role\RoleController,
    RolePermission\RolePermissionController
};

Route::group(['middleware' => 'auth'], function() {
    Route::controller(RoleController::class)->group(function() {
        Route::get('/roles',  'index')->name('roles.index');
        Route::get('/roles/datalist', 'getRoles');
        Route::get('roles/create', 'create');
        Route::get('roles/{id}/edit', 'edit');
        Route::post('/roles', 'createRole');
        Route::post('/roles/update/{id}', 'updateRole');
        Route::get('/roles/{id}', 'view');
    });
    
    Route::controller(RolePermissionController::class)->group(function() {
        Route::get('/role-permission/{id}/edit', 'edit');
        Route::post('/role-permission', 'storeRolePermission'); 
    });
});