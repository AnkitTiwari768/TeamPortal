<?php

use Illuminate\Support\Facades\Route;

use App\Web\MsmeAllList\SnpCategoryListController;

/*
|--------------------------------------------------------------------------
| SNP Category List
|--------------------------------------------------------------------------
| Role-wise listing of network_providers.role_selection_details - each
| JSON entry inside that column becomes one row - filterable by Role
| (BNP/SNP/LSP), Category and Transaction Type (B2C/B2B/Both).
| Intentionally has no guard()/permission check - see MsmeAllListController.
*/
Route::controller(SnpCategoryListController::class)->group(function () {
    Route::get('/snp-category-list', 'index')->name('snp-category-list');
    Route::get('/snp-category-list/datalist', 'getSnpCategoryList')->name('snp-category-list.datalist');
    Route::get('/snp-category-list/export-excel', 'downloadExcel')->name('snp-category-list.export-excel');
    Route::get('/snp-category-list/export-pdf', 'downloadPdf')->name('snp-category-list.export-pdf');
});
