<?php

use Illuminate\Support\Facades\Route;

use App\Web\Signup\SignupController;
use App\Web\SendOtp\SendOtpMsmeRegistrationController;
//use App\Web\Signup\VerifyOtpController;

Route::post('send-otp-msme-registration', SendOtpMsmeRegistrationController::class);
//Route::post('verify-otp-msme-registrationotp', VerifyOtpController::class);

Route::controller(SignupController::class)->group(function () {
	Route::get('/signup',  'index');
	Route::post('/applicant',  'create');
	Route::get('/snp-details',  'snpDetails')->name('snp-details');
	Route::post('/udyam-details',  'udyamDetails')->name('udyam-details');
});

Route::get('/route-list', function () {
	return collect(Route::getRoutes())->map(function ($route) {
		return [
			'method' => implode('|', $route->methods()),
			'uri' => $route->uri(),
			'name' => $route->getName(),
			'action' => $route->getActionName(),
			'middleware' => $route->middleware(),
		];
	});
});

	
	//for new requirement
	/*Route::group(['middleware' => 'auth'], function() {
		Route::controller(SignupController::class)->group(function() {
			Route::post('/udyam-details',  'udyamDetails')->name('udyam-details');
			Route::post('/applicant-update/{id}',  'update');
		});
	});*/
