<?php

use App\Domain\NetworkProvider\NetworkProviderController;
use App\Web\ApplicationWorkflow\ApplicationWorkflowController;
use App\Web\ApplicationWorkflow\SnpWorkflowController;
use App\Web\NetworkProvider\UnverifiedNetworkProviderController;
use App\Web\NetworkProvider\VerifiedNetworkProviderController;
use Illuminate\Support\Facades\Route;

use App\Web\SNP\SNPController;
use App\Web\SNP\SNPMSMEController;
use App\Web\SendOtp\SendOtpController;
use App\Web\VerifyOtp\VerifyOtpMsmeMappingController;
//use App\Web\Signup\VerifyOtpController;
use App\Web\SNP\MsmeSnpMappingController;

use App\Web\Signup\SignupController;


//Route::post('verify-otp', VerifyOtpController::class);

Route::group(['middleware' => 'auth'], function () {

    Route::post('bulk-update-bpp-id', [\App\Domain\BPPIDUpdate\BPPIDUpdateController::class, 'updateBulkBppId']);
    Route::get('bppid-update', [\App\Domain\BPPIDUpdate\BPPIDUpdateController::class, 'index'])->name('bppid-update');
    Route::get('bppid-update/download/{reportKey}/{type}/{format}', [\App\Domain\BPPIDUpdate\BPPIDUpdateController::class, 'downloadReport'])->name('bppid-update.download');
    Route::get('bppid-update/datalist', [\App\Domain\BPPIDUpdate\BPPIDUpdateController::class, 'dataList'])->name('bppid-update.datalist');
    Route::get('bppid-update/summary-cards', [\App\Domain\BPPIDUpdate\BPPIDUpdateController::class, 'summaryCards'])->name('bppid-update.summary-cards');
    Route::get('bppid-update/export-excel', [\App\Domain\BPPIDUpdate\BPPIDUpdateController::class, 'exportExcel'])->name('bppid-update.export-excel');
    Route::get('bppid-update/export-pdf', [\App\Domain\BPPIDUpdate\BPPIDUpdateController::class, 'exportPdf'])->name('bppid-update.export-pdf');

    Route::post('send-otp', SendOtpController::class);
    Route::post('verify-otp', VerifyOtpMsmeMappingController::class);


    Route::controller(UnverifiedNetworkProviderController::class)->group(function () {
        Route::get('/unverified-np',  'index');
        Route::get('/unverified-np/datalist', 'getList');
        Route::get('/np-view-detail/{id}', 'show');
    });

    Route::controller(VerifiedNetworkProviderController::class)->group(function () {
        Route::get('/verified-np',  'index');
        Route::get('/verified-np/datalist', 'getList');
    });

    Route::controller(NetworkProviderController::class)->group(function () {
        Route::post('/approve-network-provider', 'approveNetworkProvider');
        Route::post('/reject-network-provider', 'rejectNetworkProvider');
        Route::post('/revert-network-provider', 'revertNetworkProvider');
    });


    Route::controller(SNPController::class)->group(function () {

        Route::get('migrated-snp-list', 'migratedSnp');
        Route::get('get-migrated-snp', 'getMigratedSnpList');
        Route::get('migrated-snp-view/{id}', 'migratedSnpView');
    });

    Route::controller(SNPMSMEController::class)->group(function () {
        Route::get('/open-msme',  'msmeForMe');
        Route::get('/snp-msme/datalist', 'getmyMSMEList');

        Route::get('/msme-chossen-me',  'msmeChoosenMe');
        Route::get('/msme-chossen-me/datalist', 'getmsmeChoosenMeList');


        Route::get('/onboarded-msme',  'onboardedMSME');

        Route::get('/mse-to-be-validated',  'mseToBeValidated');
        Route::get('/mse-to-be-validated/datalist', 'getMseToBeValidatedList');


        Route::get('/validated-mse',  'validatedMse');
        Route::get('/validated-mse/datalist', 'getValidatedMseList');


        Route::get('/mse-secondstep-registration/{msmeId}/edit',  'secondStep');
        Route::post('/snp-udyam-details',  'udyamSnpDetails')->name('snp-udyam-details');


        Route::get('/mse-self-registration',  'mseSelfRegistration');
        Route::get('/mse-self-registration/datalist', 'getMseSelfRegistrationList');

        Route::get('/mse-seeks-helpdesk-support',  'mseSeeksHelpDeskSupport');
        Route::get('/mse-seeks-helpdesk-support/datalist', 'getmseSeeksHelpDeskSupportList');





        Route::post('/applicant-update/{id}',  'update');


        Route::get('/onboard-mse',  'onboardMSME');
        Route::get('/onboard-mse/datalist', 'getmyOnboardMSMEList');


        Route::get('/msme-inprogress/{msmeId}',  'msmeInprogress');
        Route::get('/otp-send',  'otpSend');
    });


    Route::get('/onboarded-msme/datalist', [\App\Domain\MSE\MseController::class, 'getOnboardedMSE']);

    Route::post('msme-snp-mapping', MsmeSnpMappingController::class);

    Route::post('snp-action', [SnpWorkflowController::class, 'store']);
});
