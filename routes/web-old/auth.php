<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

Route::group(['middleware' => 'guest'], function() {
    Route::controller(AuthController::class)->group(function() {
        Route::get('/', 'index');
        Route::get('/login', 'index')->name('login');        
        Route::post('/login-attempt', 'store')->name('login-attempt');
        Route::get('refresh_captcha', 'refreshCaptcha');     
        //Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    });
});