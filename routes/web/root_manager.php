<?php

use Illuminate\Support\Facades\Route;
use App\Web\RootManager\Secret\SecretPageController;
use App\Web\RootManager\SecretUserController;
use App\Web\RootManager\ByPassUser;



use App\Web\RootManager\Common\SnpMsmeCountController;
use App\Web\RootManager\Common\StateWiseCountController;
use App\Web\RootManager\Common\CityMsmeController;
use App\Web\RootManager\Common\CategoryMsmeController;
use App\Web\RootManager\Common\StateCountController;
use App\Web\RootManager\Common\UserByPassController;
use App\Web\RootManager\DataCleanup\DataCleanupController;

Route::get('/city-msme-list', [CityMsmeController::class, 'cityMmseFemaleList'])->name('city-msme-list');
Route::get('/city-msme-datalist',[CityMsmeController::class, 'getCityMsmeFemaleList'])->name('city-msme-datalist');
Route::get('/total-msme-datalist',[CityMsmeController::class, 'getTotalMsmeList'])->name('total-msme-datalist');
Route::get('msme-list', [SnpMsmeCountController::class, 'snpMsmeList'])->name('snp.msme.list');
Route::get('msme-data', [SnpMsmeCountController::class, 'getSnpMsmeData'])->name('snp.msme.data');
Route::get('state-wise-msme-list', [StateWiseCountController::class, 'stateMsmeList'])->name('state.wise.msme.list');
Route::get('state-wise-msme-data', [StateWiseCountController::class, 'getStateWiseMsmeData'])->name('state.wise.msme.data');
Route::get('category-wise-msme-list', [CategoryMsmeController::class, 'stateMsmeList'])->name('category.wise.msme.list');
Route::get('category-wise-msme-data', [CategoryMsmeController::class, 'getCategoryWiseMsmeData'])->name('category.wise.msme.data');
Route::get('state-count-list', [StateCountController::class, 'stateCountList'])->name('state.count.msme.list');
Route::get('state-count-msme-data', [StateCountController::class, 'getStateMsmeData'])->name('state.count.msme.data');

Route::get('/user-bypass', [UserByPassController::class, 'index']);
Route::post('/user-bypass/update', [UserByPassController::class, 'update'])->name('user-bypass.update');

Route::get('/data-cleanup', [DataCleanupController::class, 'index'])->name('data-cleanup.index');
Route::post('/data-cleanup/claims', [DataCleanupController::class, 'deleteClaimsData'])->name('data-cleanup.claims');
Route::post('/data-cleanup/component-utilization', [DataCleanupController::class, 'deleteComponentUtilizationData'])->name('data-cleanup.component-utilization');
Route::post('/data-cleanup/fund-allocation', [DataCleanupController::class, 'deleteFundAllocationData'])->name('data-cleanup.fund-allocation');
Route::post('/data-cleanup/fund-distribution', [DataCleanupController::class, 'deleteFundDistributionData'])->name('data-cleanup.fund-distribution');
Route::post('/data-cleanup/workshop', [DataCleanupController::class, 'deleteWorkshopData'])->name('data-cleanup.workshop');

// Your existing route with auth middleware
Route::group(['middleware' => 'auth'], function() 
{
    Route::controller(SecretPageController::class)->group(function() {
        // Login page
        Route::get('/secret-page', 'index')->name('secret.page');
        
        // Dashboard (after login)
        Route::get('/secret-dashboard', 'dashboard')->name('secret.dashboard');
        
        // API Routes for AJAX
        Route::post('/secret-verify', 'verify')->name('secret.verify');
        Route::post('/secret-logout', 'logout')->name('secret.logout');
        
    });
 
    
});

