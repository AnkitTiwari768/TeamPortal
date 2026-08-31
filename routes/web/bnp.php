<?php

use Illuminate\Support\Facades\Route;
use App\Web\BNP\BNPController;
use App\Web\ApplicationWorkflow\BnpWorkflowController;


Route::group(['middleware' => 'auth'], function () {
	
    Route::controller(BNPController::class)->group(function () {
        Route::get('/unverified-bnp',  'unverifiedBnp');
        Route::get('/unverified-bnp/datalist', 'getUnverifiedList');
        Route::get('/verified-bnp',  'verifiedBnp');
        Route::get('/verified-bnp/datalist', 'getVerifiedList');
        Route::get('/bnp-view-detail/{id}', 'getBnpDetail');
		Route::post('bnp-action', [BnpWorkflowController::class, 'store']);
    });
});
