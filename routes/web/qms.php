<?php

use App\Domain\QMS\QueryController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'auth'], function () {
    Route::get('qms/{claimSlug?}', [QueryController::class, 'index'])->name('qms.index');
    Route::get('qms/create', [QueryController::class, 'create'])->name('qms.create');
    Route::post('qms', [QueryController::class, 'store'])->name('qms.store');
    Route::get('qms-show/{id}', [QueryController::class, 'show'])->name('qms.show');
    Route::post('qms/{id}/reply', [QueryController::class, 'reply'])->name('qms.reply');
    Route::post('qms/{id}/close', [QueryController::class, 'close'])->name('qms.close');
});
