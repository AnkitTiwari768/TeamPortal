<?php

use Illuminate\Support\Facades\Route;
use App\Web\Ia\IaMseController;

Route::group(['middleware' => 'auth'], function () {
    Route::controller(IaMseController::class)->group(function () {
        Route::get('/registered-msme',  'registeredMsme');
		Route::get('/registered-msme/datalist', 'getRegisteredMsmeList');
    });
});
