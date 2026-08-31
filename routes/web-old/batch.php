<?php

use App\Web\Batch\BatchController;
use App\Web\BatchWorkflow\BatchClaimWorkflowController;
use App\Web\BatchWorkflow\BatchWorkflowController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth'], function () {

    Route::post('create-batch-claim', [\App\Domain\Batch\BatchController::class, 'create']);
    Route::get('batch-list/{claimTypeSlug}', [\App\Domain\Batch\BatchController::class, 'index']);
    Route::get('batch-detail/{batchId}/{status}', [\App\Domain\Batch\BatchClaimController::class, 'index']);
    Route::post('batch-claim-approve', [\App\Domain\Claim\ClaimWorkflowController::class, 'approve']);
    Route::post('batch-claim-reject', [\App\Domain\Claim\ClaimWorkflowController::class, 'reject']);
    Route::post('batch-claim-query', [\App\Domain\Claim\ClaimWorkflowController::class, 'sendQuery']);
    Route::post('batch-invoice-reupload-request', [\App\Domain\Claim\ClaimWorkflowController::class, 'invoiceReuploadRequest']);
    Route::post('proceed-batch-workflow', [\App\Domain\Batch\BatchController::class, 'proceed']);
    Route::post('batch-payment-completed', [\App\Domain\Batch\BatchController::class, 'batchPaymentCompleted']);
    Route::get('export-batch-claims/{batchId}', [\App\Domain\Batch\BatchController::class, 'exportClaims']);

    Route::controller(BatchController::class)->group(function () {
        // Route::post('create-batch-claim', 'createBatch');
        // Route::get('/batch-list/{claimTypeSlug}', 'list');
        // Route::get('/batch-detail/{batchId}/{status?}', 'getBatchClaims');
        Route::post('/upload-ca-certificate', 'uploadCaCertificate');
        Route::post('resend-to-ca', 'updateBatch');
        Route::post('forward-to-ondc', 'forwardToONDC');
        //Route::post('move-to-drafts', 'moveToDrafts');
    });


    Route::controller(BatchClaimWorkflowController::class)->group(function () {
        // Route::post('batch-claim-approve', 'forward');
        Route::post('batch-claim-revert', 'revert');
        //Route::post('batch-claim-reject', 'reject');
        Route::post('batch-claim-revert-to-ondc', 'revertToOndc');
        Route::post('batch-claim-revert-to-snp', 'revertToSnp');
        Route::post('batch-claim-revert-to-nsic', 'revertToNsic');
        Route::post('resend-snp-to-nsic', 'resendSnpToNsic');
        Route::post('resend-nsic-to-finance', 'resendNsicToFinance');
    });

    Route::controller(BatchWorkflowController::class)->group(function () {
        Route::post('batch-approve', 'approve');
        Route::post('batch-revert', 'revert');
        Route::post('forward-to-nsic-by-ondc', 'processByOndc');
        Route::post('forward-to-nsic-finance', 'processByNsic');
        Route::post('batch-final-approval', 'processByNsicFinance');
    });

    Route::controller(\App\Domain\Batch\RollbackBatch::class)->group(function () {
        Route::get('rollback-batch', 'index');
        Route::get('get-rollback-batches', 'getBatches');
        Route::post('rollback-batch', 'rollback');
        Route::post('rollback-batch-with-claims', 'rollbackWithClaims');
        Route::post('remove-batch-and-claims-permanently', 'removeBatchAndClaimsPermanently');
    });
});
