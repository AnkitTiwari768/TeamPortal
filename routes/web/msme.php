<?php

use Illuminate\Support\Facades\Route;

use App\Web\Msme\MsmeController;
use App\Web\MseBulkRegistration\MseBulkRegistrationController;
use App\Web\MseBulkRegistration\MseBulkRegistrationUdyamController;
use App\Web\MseBulkRegistration\MseDraftBulkUploadController;
use App\Web\MseBulkRegistration\MseDownloadController;
use App\Web\MseBulkRegistration\FailedMsmeList\FailedMsmeListController;





Route::group(['middleware' => 'auth'], function() {


    Route::controller(MsmeController::class)->group(function() {
        Route::get('/msme-registration',  'index')->name('msme-registration');
        Route::get('/msme/datalist', 'getMsmeList');
        Route::get('/msme-details/{id}', 'getMsmeDetails');
		Route::get('/msme-details/{id}', 'getMsmeDetails');
		Route::get('/msme-bulk-registration',  'msme_index')->name('msme-bulk-registration');
		Route::post('/bulk-import',  'bulk_import')->name('bulk-import');
		Route::get('relevant-snp', 'relevantSnp')->name('relevant-snp');
		Route::get('get-relevant-snp', 'getRelevantSnpList')->name('get-relevant-snp');
		Route::get('relevant-snp-view/{id}', 'relevantSnpView')->name('relevant-snp-view');
    });
	
	Route::controller(MseBulkRegistrationController::class)->group(function() {
		Route::get('/msme-bulk-registration',  'index')->name('msme-bulk-registration');
		Route::post('/msme-bulk-import',  'msme_bulk_import')->name('msme-bulk-import');
    });
	
	Route::controller(MseBulkRegistrationUdyamController::class)->group(function() {
		Route::get('/msme-bulk-registration-udyam',  'importBulkUdyam')->name('msme-bulk-registration-udyam');
		Route::post('/msme-bulk-import-udyam',  'msme_bulk_import_udyam')->name('msme-bulk-import-udyam');
		Route::get('/msme-bulk-export',  'msme_bulk_export_udyam')->name('msme-bulk-export');
    });
	
	Route::controller(MseDownloadController::class)->group(function() {
		Route::get('/msme-bulk-export',  'mseBulkExportUdyam')->name('msme-bulk-export');
    });
	
	Route::controller(MseDraftBulkUploadController::class)->group(function() {
		Route::get('/msme-bulk-draft-registration-udyam',  'draftBulkUpload')->name('msme-bulk-draft-registration-udyam');
		Route::post('/msme-bulk-draft-import',  'msme_bulk_draft_import')->name('msme-bulk-draft-import');
		Route::get('/mse/download-failed-all','downloadFailedAll')->name('mse.download.failed.all');
		Route::get('/import-progress/{key}','getProgress')->name('import.progress');

		Route::get('/msme-draft-list',  'msmeDraftList')->name('msme-draft-list');
		Route::get('/msme/draftDataList', 'getMsmeDraftList');
		Route::get('msme/download-error', 'downloadErrorExcel')->name('msme.download.error');

    });

	// Failed MSME List
	Route::controller(FailedMsmeListController::class)->group(function() {
		Route::get('/failed-msme-list', 'index')->name('failed-msme-list');
		Route::get('/msme/failed-msme/datalist', 'datalist')->name('failed-msme-list.datalist');
	});

});
Route::get('/msme/process-drafts',[MseDraftBulkUploadController::class, 'msmeProcessDrafts'])->withoutMiddleware(['auth'])->name('msme.process.drafts');
Route::get('/msme/process-drafts-ia',[MseDraftBulkUploadController::class, 'msmeProcessDraftsIa'])->withoutMiddleware(['auth'])->name('msme.process.drafts-ia');


