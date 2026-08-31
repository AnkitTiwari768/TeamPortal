<?php

use App\Domain\ForgotUdyam\ForgotUdyamController;
use App\Domain\IARegistration\IARegistrationController;
use App\Domain\MsmeAuth\MsmeAuthController;
use App\Domain\SendOtpForgotUdyam\ForgotUdyamOtpController;
use App\Web\MsmeDashboard\MsmeDashboardController;
use Illuminate\Support\Facades\Route;
use App\Domain\Msme\MsmeDetailsAction;
use App\Domain\NetworkProvider\NetworkProviderController;
use App\Http\Controllers\Auth\{AuthController, OtpPageController};
use App\Web\Search\SearchController;
use Illuminate\Support\Facades\Redis;
use App\Domain\Upload\UploadController;
use App\Web\AovCategory\AovCategoryController;
use App\Http\Api\V1\PMRegistration\PMRegistrationController;
use App\Pmv\PmvController;
use App\Web\ApplicationStatus\ApplicationStatusController;
use App\Domain\Download\DownloadController;
use App\Web\SubDistrict\SubDistrictController;
use App\Domain\UbpIntegration\UbpLandingController;

use App\Web\Msme\MsmeSnpController;


Route::get('/download-certificate', [App\Web\Certificate\CertificateController::class, 'download'])
    ->name('certificate.download')
    ->middleware('auth');



Route::get('/deploy-changes', function () {
    $logFile = base_path('app/Web/DeployTracker/changes.log');
    if (!file_exists($logFile)) {
        abort(404);
    }
    return response()->file($logFile, [
        'Content-Type' => 'text/plain',
    ]);
});
Route::get('/send-msme-registration', function () {
    // app(\App\Domain\EmailTemplate\EmailTemplateService::class)->send(
    //     templateKey: 'msme-registration',
    //     toEmail: 'kb17@yopmail.com', //$user->email,
    //     data: [
    //         'msme_name' => 'Test', //$user->username,
    //         'helpdesk_number' => config('settings.helpdesk_number'),
    //         'email' => 'team@gov.in',
    //         'team_registration_id' => 'TEAM123',
    //         'year' => (string) date('Y')
    //     ]
    // );

    // app(\App\Domain\EmailTemplate\EmailTemplateService::class)->send(
    //     templateKey: 'snp-selection',
    //     toEmail: 'kb17@yopmail.com', //$user->email,
    //     data: [
    //         'snp_name' => 'Test', //$user->username,
    //         'helpdesk_number' => config('settings.helpdesk_number'),
    //         'email' => 'team@gov.in',
    //         'year' => (string) date('Y')
    //     ]
    // );

    // app(\App\Domain\EmailTemplate\EmailTemplateService::class)->send(
    //     templateKey: 'np-registration-approved',
    //     toEmail: 'kb17@yopmail.com', //$user->email,
    //     data: [
    //         'email' => 'Test', //$user->username,
    //         'username' => 'Test',
    //         'password' => 'Test@123',
    //         'year' => (string) date('Y')
    //     ]
    // );
});

Route::get('/migrate-legacy-claims', function () {
    try {
        (new \App\Domain\Claims\MigrateLegacyClaims())->run();
        return response()->json([
            'success' => true,
            'message' => 'Legacy claimsheet migration executed successfully.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
});
Route::get('pmv', [PmvController::class, 'index']);


Route::get('/teams-msme-registration', function () {
    return view('msme-registration', ['title' => 'MSME Registration']);
});

// Do not delete
Route::get('home', function () {
    return view('public.index');
});

// Do not delete
Route::get('home2', function () {
    return view('public.index2');
});

/*Route::get('dashboard-design', function () {
    return view('dashboard.index', ['title' => 'Dashboard']);
});*/

Route::get('/refresh-csrf', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->name('refresh-csrf');

Route::get('/logout', [AuthController::class, 'destroy'])->name('logout');

Route::get('/clear', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return 'Config, Cache, View, Route cleared!';
});

/*Route::group(['middleware' => 'auth'], function () {
    Route::get('dashboard-design', function () {
        return view('dashboard.index', ['title' => 'Dashboard']);
    });
});*/




Route::get('/redis-test', function () {
    Redis::setex('test', 60, 'hello');
    return Redis::get('test'); // should return "hello"
});

Route::post('forgot-udyam-send-otp', [ForgotUdyamOtpController::class, 'sendOtp']);
Route::post('forgot-udyam-verify-otp', [ForgotUdyamOtpController::class, 'verifyOtp']);

Route::post('/upload-document-public', UploadController::class);
Route::post('ia-registration', [IARegistrationController::class, 'register']);
Route::post('know-your-udyam', ForgotUdyamController::class);
Route::post('msme-send-otp', [MsmeAuthController::class, 'sendOtp']);
Route::post('msme-verify-otp', [MsmeAuthController::class, 'verifyOtp']);


Route::post('/register-network-provider', [NetworkProviderController::class, 'register']);
Route::post('/update-network-provider', [NetworkProviderController::class, 'update']);

Route::get('/files/{fileId}/download', [DownloadController::class, 'download']);

Route::get('/get-product-categories-public', [AovCategoryController::class, 'getPublicProductCategory']);
// Route::get('/role-switch-tab', function () {
//     return view('dashboard.Role-swtich-tab', ['title' => 'Role Switch Tab']);
// });


Route::get('/snp-certificate', function () {
    return view('pdf.snp_empanelment_letter', ['title' => 'SNP Certificate']);
});

Route::get('/get-dropdown-options', [\App\Web\Dropdown\DropdownController::class, 'getOptions']);

Route::get('msme-application-status', [ApplicationStatusController::class, 'msmePage']);
Route::post('msme-application-status-check', [ApplicationStatusController::class, 'msmeCheck']);
Route::get('np-application-status', [ApplicationStatusController::class, 'npPage']);
Route::post('np-application-status-check', [ApplicationStatusController::class, 'npCheck']);

require_once 'web/auth.php';
require_once 'web/forgot_password.php';
require_once 'web/forgot_password_otp.php';
require_once 'web/signup.php';
require_once 'web/registration.php';
require_once 'web/np_registration.php';
require_once 'web/bnp_registration.php';
require_once 'web/product_domain_mapping.php';
require_once 'web/language.php';
require_once 'web/ia-registration.php';
require_once 'web/forgot_udyam.php';
require_once 'web/district.php';
require_once 'web/demand-generation.php';
require_once 'web/ai-cataloguing-claim.php';
require_once 'web/pm_registration.php';
require_once 'web/udyam_bharat_portal.php';
require_once 'web/application_tracking_public.php';
require_once 'web/route-list.php';


Route::group(['middleware' => 'auth'], function () {

    Route::get('pending-ia', [IARegistrationController::class, 'pendingIA']);
    Route::get('verified-ia', [IARegistrationController::class, 'verifiedIA']);
    Route::get('view-ia/{id}', [IARegistrationController::class, 'show']);
    Route::post('approve-ia', [IARegistrationController::class, 'approveIA']);
    Route::post('reject-ia', [IARegistrationController::class, 'rejectIA']);

    //  MSME Dashboard Routes
    Route::get('msme-my-profile', [MsmeDashboardController::class, 'myprofile'])->name('msme-my-profile');
    Route::get('view-snp-detail', [MsmeDashboardController::class, 'viewSnpDetails'])->name('view-snp-detail');

    Route::post('/update-ia-profile/{id?}', [IARegistrationController::class, 'updateIAProfile']);


    Route::get('ia-dashboard/search', [IARegistrationController::class, 'getDashboardDetails']);
    Route::post('switch-role', [\App\Web\Dashboard\DashboardController::class, 'switchRole'])->name('switch-role');
    Route::post('reset-role',  [\App\Web\Dashboard\DashboardController::class, 'resetRole'])->name('reset-role');

    Route::controller(SearchController::class)->group(function () {
        Route::get('/search-roles', 'searchByRole');
    });

    Route::get('pmv-user', [PMRegistrationController::class,  'pmvUserList'])->name('pmv-user');
    Route::get('pmv-users/datalist', [PMRegistrationController::class,   'userDataTable']);
    Route::get('view-pm-user/{id}',  [PMRegistrationController::class,  'pmvUserDatail']);


    Route::get('ubp-user-list',  [UbpLandingController::class,  'ubpUserList']);
    Route::get('ubp-user-list/datalist',  [UbpLandingController::class,  'getUbpUserList']);

    require_once 'web/snp.php';
    require_once 'web/dashboard.php';
    require_once 'web/module.php';
    require_once 'web/audit_trail.php';
    require_once 'web/role.php';
    require_once 'web/role_type.php';
    require_once 'web/permission.php';
    require_once 'web/user.php';
    require_once 'web/country.php';
    require_once 'web/state.php';
    require_once 'web/event_logs.php';
    // require (not require_once): PHP tracks includes per-process, not per Laravel
    // Application instance, so require_once silently no-ops on the second+ app boot within
    // one process (e.g. a Feature test suite) and these routes would never register.
    // Harmless in production, where each request is its own process.
    require 'web/user_logs.php';

    require_once 'web/root_manager.php';

    require_once 'web/department.php';
    require_once 'web/designation.php';
    require_once 'web/profile.php';
    require_once 'web/categories.php';
    require_once 'web/sub-domains.php';
    require_once 'web/msme.php';
    require_once 'web/website_management.php';
    require_once 'web/claims.php';
    require_once 'web/logistic_claims.php';
    require_once 'web/change_password.php';
    require_once 'web/flow_management.php';
    require_once 'web/timeline.php';
    require_once 'web/major-components.php';
    require_once 'web/components.php';
    require_once 'web/sub-components.php';
    require_once 'web/claim_form.php';
    require_once 'web/fund-flow.php';
    require_once 'web/bnp.php';
    require_once 'web/mis-report.php';
    require_once 'web/ca.php';
    require_once 'web/custom-export.php';
    require_once 'web/batch.php';
    require_once 'web/workshop.php';
    require_once 'web/ia.php';
    require_once 'web/aov-categories.php';
    require_once 'web/bonus_query.php';
    require_once 'web/pmv_bulk_registration.php';
    require_once 'web/pmv_product_category.php';
    require_once 'web/notification.php';
    require_once 'web/notification_template.php';
    require_once 'web/email_template.php';
    require_once 'web/workflow-type.php';
    require_once 'web/workflow-state.php';
    require_once 'web/workflow-transition.php';
    require_once 'web/claim_types.php';
    require_once 'web/attribute_values.php';
    require_once 'web/attribute.php';
    require_once 'web/application_tracking_admin.php';
    require_once 'web/qms.php';
    require_once 'web/admin_workshop.php';
    require_once 'web/allocation.php';
    // require_once 'web/fund_distribution.php';
    require_once 'web/mis.php';
    require_once 'web/test_communication.php';
    //require_once 'web/sub_district.php';
    require_once 'web/component-utilization.php';
    require_once 'web/user_logs.php';
    require_once 'web/msme-all-list.php';
    require_once 'web/snp-category-list.php';
    require_once 'web/category-wise-count.php';
    require_once 'web/batch-timeline-history.php';
});

Route::get('/download-msme-snp', [MsmeSnpController::class, 'download']);
Route::get('/my-list', [MsmeSnpController::class, 'list']);
Route::get('snp-data', [MsmeSnpController::class, 'getData'])->name('msme.snp.data');

Route::get('get-sub-district/{id}', [SubDistrictController::class, 'getByDistrict']);

Route::get('/udyam/check', function () {
    return view('udyam.check');
});
