<?php

use App\Domain\DemandGeneration\DemandGenerationClaimImportController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth'], function () {
    Route::controller(DemandGenerationClaimImportController::class)->group(function () {
        Route::get('/file-demand-generation-incentive-claim', 'create');
        Route::post('/apply-demand-generation-claim', 'import')->name('demand-generation.import');
        Route::post('/submit-demand-generation-claim', 'store')->name('demand-generation.submit');
        Route::post('/demand-generation/reset', 'reset')->name('demand-generation.reset');
    });

    Route::get('/download-template', [\App\Domain\DemandGeneration\DemandGenerationTemplateController::class, 'downloadCsvTemplate'])->name('download.csv.template');
    Route::get('/demand-generation-error-report/{token}', [DemandGenerationClaimImportController::class, 'downloadErrorReport']);


    Route::controller(\App\Domain\ClaimType\ClaimTypeController::class)->group(function () {
        Route::get('claim-types', 'index');
        Route::get('claim-types/create', 'create');
        Route::get('claim-types/datalist', 'getClaimTypes');
        Route::post('claim-types', 'store');
        Route::get('claim-types/{id}/edit', 'edit');
        Route::post('claim-types-update/{id}', 'update');
        Route::delete('claim-types-delete/{id}', 'destroy');
    });
});


Route::get('/download-demand-generation-csv-template/{numTeams?}/{numTransactions?}', [\App\Domain\DemandGeneration\DummyTransactionExportController::class, 'exportDummyCsv'])
    ->name('demand-generation.download-template');
