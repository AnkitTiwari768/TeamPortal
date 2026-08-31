<?php
use Illuminate\Support\Facades\Route;

use App\Web\CustomExport\CustomExportController;

Route::group(['middleware' => 'auth'], function() {
    Route::controller(CustomExportController::class)->group(function() {
        // Route::post('/custom-export-by-ids', function(){
        //     return 'ok';
        // });
        Route::post('/custom-export-by-ids', 'exportByIds');
        Route::post('/custom-export-by-ids-catalogue', 'exportByIdsCatalogue');
    });
});