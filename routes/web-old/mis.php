<?php

use Illuminate\Support\Facades\Route;

use App\Domain\MIS\TierWiseMISReportController;
use App\Domain\MIS\SnpWiseMISReportController;
use App\Domain\MIS\ProductCategoryMISReportController;
use App\Domain\MIS\SNPWiseMSMEMISReportController;
use App\Domain\MIS\StateWiseMISReportController;

Route::group(['middleware' => 'auth'], function() 
{
    Route::controller(TierWiseMISReportController::class)->group(function() {
        Route::get('/tier-wise-mis-report',  'index')->name('tier-wise-mis-report');
        Route::get('/tier-wise-mis-report/datalist', 'getTierWiseMISReport')->name('tier-wise-mis-report-datalist');
    });

    Route::controller(SnpWiseMISReportController::class)->group(function() {
            Route::get('/snp-wise-mis-report',  'index')->name('snp-wise-mis-report');
            Route::get('/snp-wise-mis-report/datalist', 'getSnpWiseMISReport')->name('snp-wise-mis-report-datalist');
        });

    Route::controller(ProductCategoryMISReportController::class)->group(function() {
            Route::get('/product-category-mis-report',  'index')->name('product-category-mis-report');
            Route::get('/product-category-mis-report/datalist', 'getProductCategoryMISReport')->name('product-category-mis-report-datalist');
        });

    Route::controller(SNPWiseMSMEMISReportController::class)->group(function() {
            Route::get('/snp-wise-msme-mis-report',  'index')->name('snp-wise-msme-mis-report');
            Route::get('/snp-wise-msme-mis-report/datalist', 'getSNPWiseMSMEMISReport')->name('snp-wise-msme-mis-report-datalist');
        });
    Route::controller(StateWiseMISReportController::class)->group(function() {
            Route::get('/state-wise-mis-report',  'index')->name('state-wise-mis-report');
            Route::get('/state-wise-mis-report/datalist', 'getStateWiseMISReport')->name('state-wise-mis-report-datalist');
    });

});