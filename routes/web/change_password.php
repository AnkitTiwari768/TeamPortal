<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ChangePasswordController;

Route::group(['middleware' => 'auth'], function() { 
    Route::get('/change-password', [ChangePasswordController::class, 'index']);
    Route::post('/update-password', [ChangePasswordController::class, 'changePassword']);
});