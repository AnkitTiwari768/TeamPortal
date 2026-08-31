<?php

use Illuminate\Support\Facades\Route;

use App\Http\Api\V1\PMRegistration\PMRegistrationController;

require_once 'api/udyam_bharat_portal.php';
require_once 'api/workshop_integeration.php';
require_once 'api/AiCatalog.php';


Route::get('/get-register-users-count', [PMRegistrationController::class, 'getRegistrationCount']);

Route::match(['get', 'post'], '/v1/udyam/check-combinations', \App\Domain\Udyam\UdyamCheckController::class);
Route::match(['get', 'post'], '/v1/udyam/check-combination', \App\Domain\Udyam\UdyamCheckController::class);
