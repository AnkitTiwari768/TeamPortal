<?php

use Illuminate\Support\Facades\Route;
use App\Http\Api\V1\UdyamBharatPortalIntergation\UBPController;
use App\Domain\UbpIntegration\UbpLandingController;


    Route::post('/get-token', [UBPController::class , 'getToken'])->name('ubp.get.token');

    Route::post('/send-request', [UBPController::class , 'registerMsme']);
