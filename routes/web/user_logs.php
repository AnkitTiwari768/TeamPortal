<?php

use Illuminate\Support\Facades\Route;

use App\Domain\UserLog\UserLogController;

Route::group(['middleware' => 'auth'], function () {
    Route::controller(UserLogController::class)->group(function () {
        Route::get('/user-logs', 'index')->name('user-logs.index');
        Route::get('/user-logs/datalist', 'datalist');
        Route::get('/user-logs/{id}', 'show')->whereNumber('id');
    });
});
