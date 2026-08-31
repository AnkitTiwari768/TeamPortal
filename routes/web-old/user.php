<?php

use Illuminate\Support\Facades\Route;

use App\Modules\User\UserController;
use App\Modules\User\CustomUserController;
use App\Modules\UserPermission\UserPermissionController;

Route::group(['middleware' => 'auth'], function () {
    Route::controller(UserController::class)->group(function () {
        Route::get('/users/datalist', 'datalist');
        Route::post('/users/update/{id}', 'update');
    });

    Route::post('/users/create', [\App\Domain\User\UserController::class, 'create']);
    Route::post('/users/update/{id}', [\App\Domain\User\UserController::class, 'update']);
    Route::post('/users/change-password', [\App\Domain\User\UserController::class, 'changePassword']);

    Route::resource('users', UserController::class);

    Route::controller(UserPermissionController::class)->group(function () {
        Route::get('/user-permissions/{id}', 'show');
        Route::get('/user-permissions/{id}/edit', 'edit');
        Route::post('user-permissions', 'store');
    });

    Route::controller(CustomUserController::class)->group(function () {
        Route::get('/user/create', 'create');
        Route::get('/user/datalist', 'datalist');
        Route::post('/user/update/{id}', 'update');
        Route::get('/get-custom-user-permissions/{userId}', 'getCustomUserPermisisons');
        Route::post('/save-custom-user-permissions', 'saveCustomUserPermisisons');
    });
    Route::resource('user', CustomUserController::class);
});
