<?php

use Illuminate\Support\Facades\Route;
use App\Web\LogisticImport\LogisticClaimImportController;

Route::group(['middleware' => 'auth'], function () {
    Route::post('logistic-claims/bulk-import', [LogisticClaimImportController::class, 'import']);
    Route::post('save-uploaded-logistic-claims', [LogisticClaimImportController::class, 'store']);
    Route::get(
        'logistic-claims/import-error-report/{token}',
        [LogisticClaimImportController::class, 'downloadErrorReport']
    )->name('logistic-claims.import.errors');
});
