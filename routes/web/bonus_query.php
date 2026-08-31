<?php

use App\Web\BonusQuery\ApplicationQueryController;
use App\Web\BonusQuery\ApplicationQueryLogController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth'], function () {

    Route::get('application-queries/{applicationId}', [ApplicationQueryController::class, 'index']);
    Route::get('application-query-details/{queryId}', [ApplicationQueryController::class, 'show']);

   
    Route::post('submit-bonus-claim', [ApplicationQueryController::class, 'store']);
    Route::post('query-reply', [ApplicationQueryLogController::class, 'store']);
    Route::post('update-query', [ApplicationQueryController::class, 'update']);


    Route::get('bonus-queries/{id}', [ApplicationQueryController::class, 'listApplicantQuery']);

    //Route::get('get-queries-data', [ApplicationQueryController::class, 'getQueryListData']);

    // Route::post('application-send-for-approval', [ApplicationWorkflowController::class, 'sendForApproval']);

    // Route::get('dashboard-np/{name}', function ($name) {
    //     //dd($name);
    //     $selectedYear =  date('Y') - 1;
    //     $title = __('message.dashboard_list');
    //     return view('dashboard.np', compact('title','selectedYear'));
    // });

});


