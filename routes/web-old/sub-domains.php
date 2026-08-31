<?php

use Illuminate\Support\Facades\Route;

use App\Web\SubDomain\SubDomainController;

Route::group(['middleware' => 'auth.session'], function () {
    Route::controller(SubDomainController::class)->group(function () {
        Route::get('/sub-domains',  'index')->name('sub-domains.index');
        Route::get('/sub-domains/datalist', 'getSubDomain');
        Route::get('sub-domains/create', 'create');
        Route::get('sub-domains/{id}/edit', 'edit');
        Route::post('/sub-domains', 'createSubDomain');
        Route::post('/sub-domains/update/{id}', 'updateSubDomain');
    });
});
