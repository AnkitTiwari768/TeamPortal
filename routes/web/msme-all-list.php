<?php

use Illuminate\Support\Facades\Route;

use App\Web\MsmeAllList\MsmeAllListController;

/*
|--------------------------------------------------------------------------
| MSME All List
|--------------------------------------------------------------------------
| Standalone clone of the mis-reports-msme list, scoped to six filters
| (From Date, To Date, Transaction Type, Product Category, State, Status).
| Intentionally has no guard()/permission check - see MsmeAllListController.
*/
Route::controller(MsmeAllListController::class)->group(function () {
    Route::get('/msme-all-list', 'index')->name('msme-all-list');
    Route::get('/msme-all-list/datalist', 'getMsmeList')->name('msme-all-list.datalist');
    Route::get('/msme-all-list/summary-cards', 'getSummaryCards')->name('msme-all-list.summary-cards');
    Route::get('/msme-all-list/export-excel', 'downloadExcel')->name('msme-all-list.export-excel');
    Route::get('/msme-all-list/export-pdf', 'downloadPdf')->name('msme-all-list.export-pdf');
});
