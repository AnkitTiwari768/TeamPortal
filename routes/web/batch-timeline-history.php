<?php

use App\Domain\BatchTimelineHistory\BatchTimelineHistoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Batch Timeline History
|--------------------------------------------------------------------------
| Standalone list of batch_timelines joined with dy_batches, users and
| team_snp_scheme - see BatchTimelineHistoryListQuery for the base query.
*/
Route::controller(BatchTimelineHistoryController::class)->group(function () {
    Route::get('/batch-timeline-history', 'index')->name('batch-timeline-history');
    Route::get('/batch-timeline-history/datalist', 'getList')->name('batch-timeline-history.datalist');
    Route::get('/batch-timeline-history/export-excel', 'downloadExcel')->name('batch-timeline-history.export-excel');
    Route::get('/batch-timeline-history/export-pdf', 'downloadPdf')->name('batch-timeline-history.export-pdf');
});
