<?php

use Illuminate\Support\Facades\Route;
use App\Domain\WorkflowType\WorkflowTypeController;

Route::group(['middleware' => 'auth'], function () {

    Route::controller(WorkflowTypeController::class)->group(function () {
        Route::get('workflow-types', 'index');
        Route::get('workflow-types/create', 'create');
        Route::get('workflow-types/datalist', 'getWorkflowTypes');
        Route::post('workflow-types', 'store');
        Route::get('workflow-types/{id}/edit', 'edit');
        Route::post('workflow-types-update/{id}', 'update');
        Route::delete('workflow-types-delete/{id}', 'destroy');
    });
});
