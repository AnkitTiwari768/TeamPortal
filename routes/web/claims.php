<?php

use App\Web\ApplicationWorkflow\ApplicationWorkflowController;
use Illuminate\Support\Facades\Route;
use App\Web\ClaimForm\ExportController;

use App\Web\Import\CatalogueClaimImportController;
use App\Web\Import\AccountClaimImportController;
use App\Web\Claim\ClaimController;

Route::group(['middleware' => 'auth'], function () {

    Route::controller(ClaimController::class)->group(function () {
        Route::post('/upload-claim-documents',  'upload');
        Route::post('claims', 'store');
        Route::post('/claim-update/{id}', 'store');
        Route::get('/claims-list/{claimTypeSlug}', 'list');
        Route::get('/reject-claim-list/{claimTypeSlug}', 'reject_list');
        Route::get('/batch-query-list/{claimTypeSlug}', 'batchQueryList');
        Route::get('batch-query-detail/{batchId}', 'batchClaimQueryList');
        Route::post('batch-query-proceed', 'proceedBatchQuery');

        Route::get('/batch-claim-query-detail/{claimTypeSlug}', 'batchClaimQueryDetail');
        Route::get('/claim-msme-details/{id}', 'getMsmeDetails');
        Route::post('claim-move-to-drafts', 'claimMoveToDrafts');
    });

    // Bulk import route for claims
    // Route::post('claims/bulk-import', [\App\Web\Claim\ClaimBulkImportController::class, 'import']);
    Route::post('claims/bulk-import', [CatalogueClaimImportController::class, 'import']);
    Route::post('save-uploaded-claims', [CatalogueClaimImportController::class, 'store']);

    Route::post('claims/account-bulk-import', [AccountClaimImportController::class, 'import']);
    Route::post('save-account-uploaded-claims', [AccountClaimImportController::class, 'store']);
    Route::get('/claim-error-report/{token}', [AccountClaimImportController::class, 'downloadPdfErrorReport']);

    // Packaging claims
    Route::post('packaging-claims/bulk-import', [\App\Web\PackagingImport\PackingClaimImportController::class, 'import']);
    Route::post('save-uploaded-packaging-claims', [\App\Web\PackagingImport\PackingClaimImportController::class, 'store']);
    Route::get('packaging-claims/import-error-report/{token}', [\App\Web\PackagingImport\PackingClaimImportController::class, 'downloadErrorReport'])->name('packaging.claims.import.errors');

    Route::post('application-action', [ApplicationWorkflowController::class, 'store']);

    Route::post('/export-selected-excel', [ExportController::class, 'exportSelected']);
    Route::post('/export-selected-excel-accounts', [ExportController::class, 'exportSelectedForAccounts']);
    Route::get('/download-account-dummy-template', [ExportController::class, 'downloadAccountDummyTemplate'])->name('accounts.download-template');
    Route::get('/download-packaging-dummy-template', [ExportController::class, 'downloadPackagingDummyTemplate'])->name('packaging.download-template');

    Route::get(
        'claims/import-error-report/{token}',
        [CatalogueClaimImportController::class, 'downloadErrorReport']
    )->name('claims.import.errors');

    Route::get(
        'accounts/import-error-report/{token}',
        [AccountClaimImportController::class, 'downloadErrorReport']
    );

    Route::controller(ClaimController::class)->group(function () {
        Route::post('/claims/{id}', 'destroy');
    });
});
