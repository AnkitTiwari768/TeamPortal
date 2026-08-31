<?php

use Illuminate\Support\Facades\Route;
use App\Web\FundDistribution\DistributionController;

/*
|--------------------------------------------------------------------------
| Isolated Enterprise Fund Distribution Routes
|--------------------------------------------------------------------------
*/

Route::prefix('web/fund-distributions')->controller(DistributionController::class)->group(function () {

    // Page Renders
    Route::get('/', 'index')->name('dist.index');
    Route::get('/create', 'create')->name('dist.create');
    Route::get('/drill/{fy}/{majorId}/{subId?}', 'showDetails')->name('dist.detail');
    Route::get('/{id}/edit', 'edit')->name('dist.edit');
    Route::get('/{id}/show', 'show')->name('dist.show');
    Route::get('/{id}/document', 'viewDocument')->name('dist.document');

    // Dynamic Core APIs
    Route::get('/api/summary-list', 'getSummaryList');
    Route::get('/api/drill-list/{fy}/{majorId}/{subId?}', 'getGroupTransactions');

    // Mutation Engines
    Route::post('/store/{id?}', 'store')->name('dist.store');
    Route::delete('/delete/{id}', 'destroy')->name('dist.delete');

    // Helper Hooks
    Route::post('/api/fetch-pool', 'getRealTimePoolData');
    Route::post('/api/upload-asset', 'uploadAsset');
});
