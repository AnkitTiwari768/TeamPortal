<?php

use Illuminate\Support\Facades\Route;
use App\Web\PMRegistration\PMRegistrationController;

Route::controller(PMRegistrationController::class)->group(function () {
    Route::get('/pm-registration',  'index');
    Route::post('/create-pm-users',  'create');
});