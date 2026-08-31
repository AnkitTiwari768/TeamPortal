<?php 

use Illuminate\Support\Facades\Route;
use App\Web\Dashboard\DashboardController;
use App\Web\Dashboard\AdminDashboardController;
use App\Web\Dashboard\SnpDashboardController;

Route::group(['middleware' => ['auth']], function() {
    Route::get('/dashboard/{year?}', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard-new/{year?}', [DashboardController::class, 'indexNew'])->name('dashboard');
    Route::get('/dashboard-mail', [DashboardController::class, 'mailsend']);
    Route::get('/get-fund-management-metrics', [DashboardController::class, 'getFundManagementMetrics']);

    Route::get('/get-mapping-msme-count', [DashboardController::class, 'getMappingMsmeCount']);
    Route::get('/get-static-bar-chart', [DashboardController::class, 'getStaticBarChart']);
	
	Route::get('/get-msme-category-count-onboarded', [DashboardController::class, 'getMsmeCategoryCountOnboarded']);
	
	Route::get('/get-msme-state-wise-count', [DashboardController::class, 'getMsmeStateWiseCount']);

	Route::get('/get-msme-percantege-seller-and-catalogues', [DashboardController::class, 'getMsmePercentageSellerAndCatlogues']);
    Route::get('/get-claim-counts-by-status', [DashboardController::class, 'getClaimsSummary'])->name('claims.counts');
    Route::get('/get-top-performer-snps', [DashboardController::class, 'getTopPerformerSnps']);
    Route::get('/get-msme-gender-percentege', [DashboardController::class, 'getMsmeGenderPercantege']);
    Route::get('/snp-dashboard-msme-category-count-onboarded', [SnpDashboardController::class, 'getMsmeCategoryCountOnboarded']);


     // ── Administrator Dashboard ──────────────────────────────────────────
    Route::get('/admin-dashboard-mse-summary', [AdminDashboardController::class, 'getMseSummary']);
    Route::get('/admin-dashboard-claim-summary', [AdminDashboardController::class, 'getClaimSummary']);
    Route::get('/admin-dashboard-msme-category-count-onboarded', [AdminDashboardController::class, 'getMsmeCategoryCountOnboarded']);
    Route::get('/admin-dashboard-top-performer-nps', [AdminDashboardController::class, 'getTopPerformerNps']);
    Route::get('/admin-dashboard-msme-gender-percentage', [AdminDashboardController::class, 'getMsmeGenderPercantege']);
    Route::get('/admin-dashboard-state-wise-msme-count', [AdminDashboardController::class, 'getMsmeStateWiseCount']);
    Route::get('/admin-dashboard-registered-vs-onboarded-monthly', [AdminDashboardController::class, 'getRegisteredVsOnboardedMonthly']);

   

   
});