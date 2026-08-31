<?php

use Illuminate\Support\Facades\Route;
use App\Web\PmvBulkRegistration\PMVBulkUploadController;

Route::group(['middleware' => 'auth'], function() {  
    
    Route::controller(PMVBulkUploadController::class)->group(function() {
        Route::get('/pmv-bulk-upload', 'index')->name('pmv-bulk-upload');
        Route::post('/pmv-bulk-registation-import', 'import')->name('pmv-bulk-registation-import');
        Route::get('/download-failed-records', 'downloadFailed')->name('pmv.download.failed.all');
        Route::get('/pmvList', 'list')->name('pmvList');
        Route::get('/pmvDataList', 'pmvDataList')->name('pmvDataList');
        
        // Error download routes
        Route::get('/download-errors', 'downloadErrorsExcel')->name('pmv.download.errors.excel');
        Route::get('/error-count', 'getErrorCount')->name('pmv.error.count');
    });
});