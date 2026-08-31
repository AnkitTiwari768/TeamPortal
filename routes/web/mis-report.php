<?php

use Illuminate\Support\Facades\Route;

use App\Web\MisReport\MisReportController;
use App\Web\MisReport\MisDemandGenerationController;
use App\Web\MisReport\ProductCategoryMseReportController;

Route::group(['middleware' => 'auth'], function() {
    Route::controller(MisReportController::class)->group(function() {
        /* mis msme registration report */
        
        Route::get('/mis-reports-msme',  'index')->name('mis-reports-msme');
        Route::get('/mis-reports-msme/datalist', 'getMsmeList');
        Route::get('/mis-reports-msme-details/{id}', 'getMsmeDetails');

        /* mis snp registration report */
        Route::get('/mis-snp-registration-reports',  'registeredSnp')->name('mis-snp-registration-reports');
        Route::get('/mis-snp-registration-reports/datalist',  'getRegisteredList')->name('mis-snp-registration-reports');
         Route::get('/mis-snp-registration-reports/snp-view-detail/{id}', 'getSnpDetail');
         /* mis snp verification status */
         Route::get('/mis-snp-verification-status',  'verifiedSnp')->name('mis-snp-verification');
         /* mis bnp registration report */
         Route::get('/mis-bnp-registration-reports',  'registeredBnp')->name('mis-bnp-registration-reports');
         Route::get('/mis-bnp-registration-reports/datalist',  'getRegisteredBnpList')->name('mis-bnp-registration-reports');
         Route::get('/mis-bnp-registration-reports/bnp-view-detail/{id}', 'getBnpDetail');

        /* mis bnp verification status */
        Route::get('/mis-bnp-verification-status',  'verifiedBnp')->name('mis-bnp-verification');
        
        
        
        /* msme-snp-mapping-report */
        Route::get('/msme-snp-mapping-report',  'onboardedMSEs')->name('msme-snp-mapping-report');
        Route::get('/msme-snp-mapping-report/datalist',  'onboardedMSEsList')->name('msme-snp-mapping-report');
        Route::get('/msme-onboarding-ondc-report',  'transactedLiveMSEs')->name('msme-onboarding-ondc-report');
        Route::get('/msme-onboarding-ondc-report/datalist',  'transactedLiveMSEsList')->name('msme-onboarding-ondc-report');
        Route::get('/catalogue-creation-report',  'catalogueReadyMSEs')->name('catalogue-creation-report');
        Route::get('/catalogue-creation-report/datalist',  'catalogueReadyMSEsList')->name('catalogue-creation-report');
      
        /* Incentive Claim Report */
        Route::get('/catalogue-creation-claim-report',  'catlogueReport')->name('catalogue-creation-claim-report');
        Route::get('mis-claim-report/{claimtype}',  'claimList');
        Route::get('/account-management-support-claim-report',  'accountsClaim')->name('account-management-support-claim-report');
        Route::get('/transportation-logistics-claim-report',  'logisticClaim')->name('transportation-logistics-claim-report');
        Route::get('/packaging-claim-report',  'packagingClaim')->name('transportation-logistics-claim-report');

        /* Network Participants*/

        Route::get('/np-register-report',  'npRegisterReport')->name('np-register-report');
        Route::get('/np-register/datalist',  'getNpList')->name('np-register-report');
        Route::get('/np-view-details/{id}',  'npShow')->name('np-view-details');


        /* Account Mangement Report*/  
        Route::get('/account-management-report',  'npRegisterReport')->name('np-register-report');

        /* Workshop */
        Route::get('/workshop-creation-report',  'workshopReport')->name('workshop-report');
        Route::get('view-workshop/{id}','workshopView')->name('workshop-view');

        /* Fund Flow */
        Route::get('/fund-allocation-report',  'fundAllocationReport')->name('fund-allocation-report');
        Route::get('/fund-allocation-reoprt-view/{id}',  'getDetails')->name('fund-allocation-reoprt-view');

        Route::get('/fund-distribution-report',  'fundDistributionReport')->name('fund-distribution-report');
        Route::get('/fund-distribution-report-view/{id}',  'getDistributionDetails')->name('fund-distribution-report-view');

        Route::get('/mse-bulk-registration-mis-report',  'mseBulkRegistrationReport')->name('mse-bulk-registration-mis-report');
        Route::get('/get-mse-bulk-mis-report-list',  'getMseBulkData')->name('get-mse-bulk-mis-report-list');

        /* Association Registration */
        Route::get('/association-registration-report',  'associationRegistrationReport')->name('association-registration-report');
        Route::get('/association-registration-report-list',  'associationRegistrationList')->name('association-registration-report-list');
        Route::get('/association-registration-view/{id}',  'associationRegistrationView')->name('association-registration-view');


        /* SNP Wise MSE Registration Report */

        Route::get('/snp-wise-mse-registration-report',  'snpWiseMseRegistrationReport')->name('snp-wise-mse-registration-report');
        Route::get('/get-snp-wise-mse-list',  'getSnpWiseMsmeList')->name('get-snp-wise-mse-list');
        Route::get('/snp-wise-mse-detail/{id}',  'snpWiseMsmeDetail')->name('snp-wise-mse-detail');
      
    });


    Route::controller(MisDemandGenerationController::class)->group(function() {
        Route::get('/demand-generation-report',  'showDemandGenerationReport')->name('demand-generation-report');
        Route::get('/demand-generation-report/datalist',  'getDemandGenerationList')->name('demand-generation-report-datalist');
    });
      Route::controller(ProductCategoryMseReportController::class)->group(function() {
        Route::get('/mis-product-category-msme',  'productCategoryMsmeList')->name('mis-product-category-msme');
        Route::get('/category-wise-msme-data',  'getCategoryWiseMsmeData')->name('category.wise.msme.data');
    });
     
});