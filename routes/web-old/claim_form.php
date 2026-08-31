<?php

use Illuminate\Support\Facades\Route;

use App\Web\ClaimForm\ClaimFormController;
use App\Web\ClaimForm\CatalogueController;
use App\Web\Claim\ClaimController;

Route::group(['middleware' => 'auth'], function () {

    Route::controller(ClaimFormController::class)->group(function () {
        Route::get('/claims',  'index')->name('claims.index');
        Route::get('accounts-claims', 'accountsClaim');
        Route::get('/packaging-support-claim', 'packagingClaimList');
        Route::get('/logistics-transportation-claim',  'logisticClaim');
        Route::get('/catalogue-creation/{msmeId}', 'catalogueCreation');
        Route::get('/demand-generation-claim', 'demandGenerationClaim');
        Route::get('/transport-and-logistic-creation/{msmeId}', 'transportAndLogisticCreation');
        Route::get('/accounts-and-management/{msmeId}', 'accountsAndManagementCreation');
        Route::get('/claim-edit/{id}/edit', 'edit');
        Route::get('/claim-show/{id}', 'show')->name('claim.show');
        Route::get('/claim-orders-list/{claimId}', [ClaimController::class, 'claimOrdersList']);
        Route::get('/claim-orders-export/{claimId}', [ClaimController::class, 'exportClaimOrders']);
        // Route::get('/batch-claim-show/{id}', 'batchClaimsShowById')->name('batch.claim.show');	
        //Route::post('/claims/payment', 'PaymentUpdate');	

        Route::get('/demand-generation/{msmeId}', 'demandGeneration');
        Route::get('/packaging-claim/{msmeId}', 'packagingClaim');

        Route::get('/batch-claim-queries/{claimTypeSlug}',  'queries')->name('queries');

        Route::get('/packaging-support/{msmeId}', 'packagingSupportClaim');

        Route::get('/logistic-and-transport-creation/{msmeId}', 'transportAndLogisticCreation');
        //delete add more route
        // Route::post('/delete-claim-order', [ClaimController::class, 'deleteAddMoreOrder']);
    });

    Route::controller(CatalogueController::class)->group(function () {
        Route::get('/ready-for-catalogue-creation',  'catalogueReadyMSME');
        Route::get('/ready-for-catalogue-creation/datalist', 'catalogueReadyMSMEList');
        Route::get('/restricted-msme', 'restrictedMsme');
        Route::get('/restricted-msme-list', 'catalogueRestrictedMSMEList');
        Route::post('/upload-catalogue', 'uploadCatalogue');
        Route::post('/delete-catalogue', 'deleteCatalogue');
        Route::post('/save-catalogue', 'catalogueUpdate');
        Route::get('/catalogue-created',  'catalogueCreatedMSME')->name('catalogue-created');
        // Route::get('/catalogue-created/datalist', 'catalogueCreatedMSMEList');
        Route::get('/transacted-live',  'CatalogueClaimApprovedMSME')->name('transacted-live');
        Route::get('/transacted-live/datalist', 'CatalogueClaimApprovedMSMEList');
        Route::post('/catalogue-bulk-import', 'catalogueBulkUpload');

        Route::get('/claim-for-account-managment',  'accountManagmentClaimApprovedMSME')->name('claim-for-account-managment');
        Route::get('/claim-for-packaging',  'packagingClaimApprovedMSME')->name('claim-for-packaging');

        Route::get('/claim-for-transport-and-logistic',  'transportAndLogisticApprovedMSME')->name('claim-for-transport-and-logistic');

        Route::get('/get-msme-bonus-details/{msmeId}',  'msmeBonusDetails');



        Route::get('/accounts-created', 'accountsCreatedMSME');
        // Route::get('/accounts-created/datalist', 'accountsCreatedMSMEList');
        // Route::get('/packaging-created/datalist', 'packagingCreatedMSMEList');
    });


    Route::get('/catalogue-created/datalist', [\App\Domain\MSE\MseController::class, 'getMseForCatalogueClaim']);
    Route::get('/accounts-created/datalist', [\App\Domain\MSE\MseController::class, 'getMseForAccountClaim']);
    Route::get('/packaging-created/datalist', [\App\Domain\MSE\MseController::class, 'getMseForPackagingClaim']);

    Route::controller(ClaimFormController::class)->group(function () {
        Route::get('/catalogue-creation/{msmeId}', 'catalogueCreation');
    });
});
