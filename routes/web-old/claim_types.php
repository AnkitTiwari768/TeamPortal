<?php

use Illuminate\Support\Facades\Route;
use App\Web\Masters\ClaimTypes\ClaimTypesController;

Route::group(['middleware' => 'auth.session'], function () {
    Route::controller(ClaimTypesController::class)->group(function () {
            Route::get('/claimTypes',  'index')->name('claimTypes.index');
            Route::get('/claimTypes/datalist', 'getDataClaimTypes')->name('claimTypes.datalist');
            Route::get('claimTypes-create', 'create')->name('claimTypes-create');
            Route::get('claimTypes/{id}/edit', 'edit')->name('claimTypes.edit');
            Route::post('/claimTypes-create', 'claimTypesCreate')->name('claimTypes-create');
            Route::post('/claimTypes-update/{id}', 'claimTypesUpdate')->name('claimTypes-update');
    });
});

