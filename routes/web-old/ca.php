<?php

use Illuminate\Support\Facades\Route;

use App\Web\CA\CAController;

Route::group(['middleware' => 'auth'], function() {
    Route::controller(CAController::class)->group(function() {
        Route::get('/ca-user', 'index')->name('ca-user.index');
        Route::get('/ca-user/create', 'create')->name('ca-user.create');
        Route::post('/ca-user', 'store')->name('ca-user.store'); 
        Route::get('/ca-user/edit/{id}', 'edit')->name('ca-user.edit');
        //Route::post('/ca-user/{id}', 'store')->name('ca-user.update'); 
        Route::post('/ca-user/update/{id}', 'update'); 
    });
});