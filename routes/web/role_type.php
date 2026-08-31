<?php

use Illuminate\Support\Facades\Route;

use App\Web\RoleType\RoleTypeController;

Route::group(['middleware' => 'auth'], function () {
    Route::controller(RoleTypeController::class)->group(function () {
        Route::get('/role-types', 'index')->name('role-types.index');
        Route::get('/role-types/datalist', 'getRoleTypes');
        Route::get('/role-types/create', 'create');
        Route::get('/role-types/permission-tree/{id?}', 'getPermissionTree');
        Route::get('/role-types/{id}/edit', 'edit');
        Route::post('/role-types', 'createRoleType');
        Route::post('/role-types/update/{id}', 'updateRoleType');
        Route::get('/role-types/{id}', 'view');
    });
});
