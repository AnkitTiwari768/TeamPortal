<?php

use App\Domain\AICataloguingClaim\AICataloguingClaimController;
use App\Web\ClaimForm\ClaimFormController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth'], function () {
    Route::get('/ai-cataloguing-claim', [ClaimFormController::class, 'aiCataloguingClaim'])->name('ai-cataloguing.index');

    Route::controller(AICataloguingClaimController::class)->group(function () {
        Route::get('/file-ai-cataloguing-claim', 'create')->name('ai-cataloguing.create');
        Route::post('/apply-ai-cataloguing-claim', 'import')->name('ai-cataloguing.import');
        Route::post('/submit-ai-cataloguing-claim', 'store')->name('ai-cataloguing.submit');
        Route::get('/ai-cataloguing-error-report/{token}', 'downloadErrorReport')->name('ai-cataloguing.error-report');
        Route::get('/download-ai-cataloguing-template', 'downloadTemplate')->name('ai-cataloguing.download-template');
    });
});
