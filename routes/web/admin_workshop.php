<?php

use Illuminate\Support\Facades\Route;
use App\Http\Api\V1\AdminWorkshop\AdminWorkshopController;
use App\Web\AdminWorkshopManagement\AdminWorkshopManagementController;

Route::get('admin-workshop',           [AdminWorkshopManagementController::class, 'index']);
Route::get('admin-workshop/datatable', [AdminWorkshopManagementController::class, 'getDataTable']);

Route::get('create-admin-workshop',    [AdminWorkshopManagementController::class, 'createPage']);
Route::post('store-admin-workshop',    [AdminWorkshopManagementController::class, 'store']);

Route::get('view-admin-workshop/{id}', [AdminWorkshopManagementController::class, 'show']);

Route::get('edit-admin-workshop/{id}/edit',    [AdminWorkshopManagementController::class, 'editPage']);
Route::post('update-admin-workshop/{id}', [AdminWorkshopManagementController::class, 'update']);

Route::delete('admin-workshop/{id}', [AdminWorkshopManagementController::class, 'destroy']);

Route::post('upload-supporting-document', [AdminWorkshopManagementController::class, 'uploadDocument']);

Route::post('admin-workshop/{adminWorkshopId}/expenses', [AdminWorkshopController::class, 'addExpense']);

Route::get('get-sub-duration/{id}',[AdminWorkshopManagementController::class, 'getSubDuration']);

Route::post('/exist-district-workshop-admin',[AdminWorkshopManagementController::class,'checkDistrictWorkshop'])->name('exist-district-workshop-admin');
