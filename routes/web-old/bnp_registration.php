<?php

use Illuminate\Support\Facades\Route;

use App\Web\BNPRegistration\BnpRegistrationController;


    Route::controller(BnpRegistrationController::class)->group(function() {
        Route::get('/bnp-registration',  'index');
        Route::post('/create-bnp-registration',  'create');

        Route::get('/bnp-registration/{id}',  'updateBnp');
        Route::post('/update-bnp-registration/{id}',  'updateRegistration');
		
    });
