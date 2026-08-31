<?php

use Illuminate\Support\Facades\Route;

use App\Web\Registration\RegistrationController;
	
    Route::controller(RegistrationController::class)->group(function() {
		Route::post('/applicant-registration',  'create');
	    Route::get('/registration',  'registration');
		Route::get('/snp-select-details',  'snpSelectDetails')->name('snp-select-details');//for new requirement
		
    });
