<?php

use Illuminate\Support\Facades\Route;

use App\Web\NPRegistration\NpRegistrationController;
use App\Web\ProductDomainMapping\ProductDomainMappingController;


Route::controller(NpRegistrationController::class)->group(function () {
    Route::get('/np-registration',  'index');
    Route::get('/np-registration/{id}',  'edit');
    Route::post('/getsubdomains',  'getsubdomains');
    Route::post('/upload-flyer-presentation-certificate', 'uploadFlyerPresentationCertificate');
    Route::post('/upload-authorized_certificate', 'uploadAuthorizedCertificate');
    Route::post('/upload-cancelled_cheque', 'uploadCancelledCheque');
    Route::post('/upload-commercial_model', 'uploadCommercialModel');
    Route::post('/upload-description', 'uploadDescription');
    Route::post('/snp-delete-documents', 'deleteSnpDocument');
    Route::post('/create-snp-registration',  'create');

    Route::get('/snp-registration/{id}',  'updateSnp');
    Route::post('/update-snp-registration/{id}',  'updateRegistration');
});
Route::post('/get-ondc-domain-id', [ProductDomainMappingController::class, 'getProductDomainOndcID']);
