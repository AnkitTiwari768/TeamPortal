<?php 

use Illuminate\Support\Facades\Route;
use App\Web\Dashboard\DashboardController;
use App\Web\Dashboard\AdminDashboardController;

Route::group(['middleware' => ['auth']], function() {
    Route::get('/dashboard/{year?}', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard-new/{year?}', [DashboardController::class, 'indexNew'])->name('dashboard');
    Route::get('/dashboard-mail', [DashboardController::class, 'mailsend']);
    Route::get('/get-mapping-msme-count', [DashboardController::class, 'getMappingMsmeCount']);
    Route::get('/get-static-bar-chart', [DashboardController::class, 'getStaticBarChart']);
	
	Route::get('/get-msme-category-count-onboarded', [DashboardController::class, 'getMsmeCategoryCountOnboarded']);
	
	Route::get('/get-msme-state-wise-count', [DashboardController::class, 'getMsmeStateWiseCount']);

	Route::get('/get-msme-percantege-seller-and-catalogues', [DashboardController::class, 'getMsmePercentageSellerAndCatlogues']);
    Route::get('/get-claim-counts-by-status', [DashboardController::class, 'getClaimsSummary'])->name('claims.counts');
    Route::get('/get-top-performer-snps', [DashboardController::class, 'getTopPerformerSnps']);
    Route::get('/get-msme-gender-percentege', [DashboardController::class, 'getMsmeGenderPercantege']);

    // ── Administrator Dashboard ──────────────────────────────────────────
    Route::get('/admin-dashboard-mse-summary',   [AdminDashboardController::class, 'getMseSummary']);
    Route::get('/admin-dashboard-claim-summary', [AdminDashboardController::class, 'getClaimSummary']);
   
});