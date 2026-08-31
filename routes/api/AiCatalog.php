<?php

use App\Http\Api\V1\AiCatalog\AiCatalogConstants;
use App\Http\Api\V1\AiCatalog\AiCatalogController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/AiCatalog')->group(function () {
    Route::post('registration', [AiCatalogController::class, 'registration'])
        ->middleware('throttle:' . AiCatalogConstants::RATE_LIMIT_AUTH)
        ->name('ai-catalog.registration');

    Route::post('login', [AiCatalogController::class, 'login'])
        ->middleware('throttle:' . AiCatalogConstants::RATE_LIMIT_AUTH)
        ->name('ai-catalog.login');

    Route::get('validate-token', [AiCatalogController::class, 'validateToken'])
        ->middleware('throttle:' . AiCatalogConstants::RATE_LIMIT_VALIDATE)
        ->name('ai-catalog.validate-token');
});
