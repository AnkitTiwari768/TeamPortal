<?php

use App\Domain\FundAllocation\FundAllocationController;
use Illuminate\Support\Facades\Route;

use App\Web\FundFlow\FundFlowController;
use App\Web\FundFlow\FundDistributionController;
use App\Domain\FundFlow\ComponentUtilzationMappingController;
use App\Domain\FundCarryForward\FundCarryForwardController;


Route::group(['middleware' => 'auth'], function () {

    Route::get('/configuration-home', function () {
        return view('fund_flow.configuration_home')->with('title', __('fund_flow.configuration_home'));
    });

    Route::controller(ComponentUtilzationMappingController::class)->prefix('component-utilization-mapping')->group(function () {
        Route::get('/', 'index')->name('component-utilization-mapping.index');
        Route::get('/create', 'create')->name('component-utilization-mapping.create');
        Route::get('/datalist', 'getDataTable');
        Route::get('/component-balances', 'getComponentBalances');
        Route::post('/store', 'store')->name('component-utilization-mapping.store');
        Route::post('/store/{id}', 'store');
        Route::get('/{id}/edit', 'edit')->name('component-utilization-mapping.edit');
        Route::get('/{id}', 'show')->name('component-utilization-mapping.show');
    });



    Route::controller(FundAllocationController::class)->group(function () {
        Route::get('/fund-allocations', 'index')->name('fund-allocations.index');
        Route::get('/fund-allocation/datalist', 'getDataTable');
        Route::get('/fund-allocation/attribute-values/{attributeId}', 'getAttributeValues');
    });

    Route::controller(FundAllocationController::class)->prefix('fund-allocation')->group(function () {
        Route::get('/create', 'create');
        Route::get('/period-summary', 'getPeriodSummary');
        Route::get('/component-balance', 'getComponentBalance');
        Route::get('/check-previous-balance', 'checkPreviousBalance');
        Route::get('/yearly-note-balance', 'getYearlyNoteBalance');
        Route::post('/upload-documents', 'uploadDocuments');
        Route::post('/store', 'store');
        Route::post('/store/{id}', 'store');
        Route::get('/{id}/edit', 'edit');
        Route::get('/{id}/view-document', 'viewDocument')->name('fund-allocations.view-document');
        Route::get('/{id}/download-document', 'downloadDocument')->name('fund-allocations.download-document');
        Route::get('/{id}', 'show')->name('fund-allocations.show');
    });

    // Distinct base path so it doesn't collide with the /fund-allocation/{id} show route above --
    // the shared buttonDelete() helper always builds "{endpoint}/{id}" and the shared delete
    // confirmation modal always issues a GET to it (see assets/js/script.js:679).
    Route::get('/fund-allocation-delete/{id}', [FundAllocationController::class, 'destroy'])->name('fund-allocations.destroy');

    Route::post('/store-fund-allocation/{id?}', [FundAllocationController::class, 'store']);

    // Read-only carry-forward history (list + view). Create/edit/store are deliberately
    // NOT routed -- the only carry-forward mechanism in the app is the automatic sweep
    // in StoreFundAllocationAction; this is purely an audit trail for it.
    Route::controller(FundCarryForwardController::class)->prefix('carry-forward-mapping')->group(function () {
        Route::get('/', 'index')->name('carry-forward-mapping.index');
        Route::get('/datalist', 'getDataTable');
        Route::get('/{id}', 'show')->name('carry-forward-mapping.show');
    });

    Route::controller(FundFlowController::class)->group(function () {
        // Route::get('/fund-allocation/{id}',  'getDetails');
        Route::post('/upload-allocation', 'uploadAllocationdocument');
        Route::post('/allocation-delete-documents', 'deleteAllocationDocument');
    });





    Route::prefix('fund-distributions')->controller(\App\Domain\FundDistribution\FundDistributionController::class)->group(function () {
        Route::get('/', 'index')->name('dist.index');
        Route::get('/create', 'create')->name('dist.create');
        Route::get('/drill/{fy}/{majorId}/{subId?}', 'showDetails')->name('dist.detail');
        Route::get('/{id}/edit', 'edit')->name('dist.edit');
        Route::get('/{id}/show', 'show')->name('dist.show');
        Route::get('/{id}/document', 'viewDocument')->name('dist.document');

        Route::get('/api/summary-list', 'getSummaryList');
        Route::get('/api/drill-list/{fy}/{majorId}/{subId?}', 'getGroupTransactions');

        Route::post('/store/{id?}', 'store')->name('dist.store');
        Route::delete('/delete/{id}', 'destroy')->name('dist.delete');

        Route::post('/api/fetch-pool', 'getRealTimePoolData');
        Route::post('/api/upload-asset', 'uploadAsset');
    });

    Route::post('/get-total-allocation', [FundFlowController::class, 'getTotalAllocation']);
});

Route::get('/debug-config', function () {
    $cols = \Illuminate\Support\Facades\Schema::getColumnListing('fund_allocations');
    \Illuminate\Support\Facades\Log::info('FUND_ALLOCATIONS_COLS: ' . json_encode($cols));
    return response()->json($cols);
});
