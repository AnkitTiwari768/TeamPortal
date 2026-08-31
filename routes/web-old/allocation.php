<?php

use Illuminate\Support\Facades\Route;
use App\Web\Allocation\AllocationController;

Route::group(['middleware' => ['auth']], function () {

    Route::controller(AllocationController::class)->prefix('web/allocation')->group(function () {

        /** Listing page */
        Route::get('/', 'index')->name('allocation.index');

        /** DataTable AJAX list — must be before /{id} */
        Route::get('/datalist', 'datalist')->name('allocation.datalist');

        /** File upload */
        Route::post('/upload-document', 'uploadDocument')->name('allocation.upload');

        /** Create form */
        // Route::get('/create', 'create')->name('allocation.create');

        /** Store new allocation */
        Route::post('/', 'store')->name('allocation.store');

        /** Edit form */
        Route::get('/{id}/edit', 'edit')->name('allocation.edit');

        /** Update existing allocation — accept both POST and PUT */
        Route::post('/{id}', 'store')->name('allocation.update');
        Route::put('/{id}', 'store')->name('allocation.update.put');

        /** View allocation detail */
        // Route::get('/{id}', 'show')->name('allocation.show');

        /** View allocation document */
        Route::get('/{id}/document', 'viewDocument')->name('allocation.document');
    });

    // ── Internal API Routes ──────────────────────────────────────────────────
    Route::controller(AllocationController::class)->prefix('web/api/allocation')->group(function () {

        Route::get('/components', 'getComponents');
        Route::get('/sub-components', 'getSubComponents');
        Route::get('/attribute-values/{attributeId}', 'getAttributeValues');
    });
});
