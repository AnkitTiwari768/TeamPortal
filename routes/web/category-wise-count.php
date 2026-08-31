<?php

use Illuminate\Support\Facades\Route;

use App\Web\MsmeAllList\CategoryWiseCountController;

/*
|--------------------------------------------------------------------------
| Category Wise MSME Count
|--------------------------------------------------------------------------
| Lists every active sub_domains category, mapped to a count of
| team_msme_schemes rows whose product_category_id JSON array contains
| that category's id (bpp_id IS NULL, select_snp = 0, major_activity
| present) - filterable by Category.
| Intentionally has no guard()/permission check - see MsmeAllListController.
*/
Route::controller(CategoryWiseCountController::class)->group(function () {
    Route::get('/category-wise-count', 'index')->name('category-wise-count');
    Route::get('/category-wise-count/datalist', 'getCategoryWiseCountList')->name('category-wise-count.datalist');
});
