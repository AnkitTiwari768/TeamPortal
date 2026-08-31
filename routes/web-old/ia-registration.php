<?php

use Illuminate\Support\Facades\Route;

use App\Web\IARegistration\IARegistrationController;

	
    Route::controller(IARegistrationController::class)->group(function() {
        Route::get('/ia-registration',  'index');
        Route::get('/ia-pending-list',  'pendingIAList');
        Route::get('/ia-view-details/{id}',  'viewIAPage');
        Route::get('/ia-verified-list',  'verifiedIAList');

    });




