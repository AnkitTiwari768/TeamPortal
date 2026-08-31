<?php
use Illuminate\Support\Facades\Route;
use App\Web\FlowManagement\FlowManagementController; 

Route::group(['middleware' => 'auth'], function() {    
    Route::get('/flow-management/datalist', [FlowManagementController::class, 'datalist']);
	Route::get('/get-users', [FlowManagementController::class, 'getUsersByRoleId'])->name('get-users');
	Route::get('/get-permissions', [FlowManagementController::class, 'getPermissionsByRoleIdAndUserId'])->name('get-permissions');
    Route::resource('/flow-management', FlowManagementController::class)->except(['show']);
});