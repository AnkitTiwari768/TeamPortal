<?php

use Illuminate\Support\Facades\Route;

use App\Web\Department\DepartmentController;

Route::group(['middleware' => 'auth'], function() {
    Route::controller(DepartmentController::class)->group(function() { 
        Route::get('/departments',  'index')->name('departments.index');
        Route::get('/departments/datalist', 'getDepartments');
        Route::get('departments/create', 'create');
        Route::get('departments/{id}/edit', 'edit');
        Route::post('/departments', 'createDepartment');
        Route::post('/departments/update/{id}', 'updateDepartment');
    }); 
});