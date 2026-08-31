<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ForgotUdyamController;


Route::controller(ForgotUdyamController::class)->group(function() {
    Route::get('/forgot-udyam-number',  'index');
});