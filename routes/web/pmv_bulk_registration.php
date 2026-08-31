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

Route::get('/download-pmv-format', function () {
    $file = storage_path('app/download_format/PMV_Bulk_Upload.xlsm');
    abort_unless(file_exists($file), 404);
    return response()->download(
        $file,
        'PMV_Bulk_Upload.xlsm',
        [
            'Content-Type' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
        ]
    );
})->name('download.pmv.format');