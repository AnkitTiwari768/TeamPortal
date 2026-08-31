<?php

use Illuminate\Support\Facades\Route;
use App\Domain\UbpIntegration\UbpLandingController;
use App\Domain\UbpIntegration\UbpRegistrationController;
use App\Domain\UbpIntegration\UbpTokenService;

Route::middleware(['web'])->prefix('ubp')->group(function () {

    Route::get('/login', [UbpLandingController::class , 'login'])->name('ubp.login');

    Route::get('/register', [UbpRegistrationController::class , 'show'])->name('ubp.register');

    Route::post('/register', [UbpRegistrationController::class , 'store'])->name('ubp.register.save');

});
