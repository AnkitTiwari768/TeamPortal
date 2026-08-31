<?php

declare(strict_types=1);

namespace App\Web\NPRegistration;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Observers\AuditTrailLog;
use App\Http\Controllers\ClientController;
use App\Core\BaseRequest;
use App\Traits\HasFileUpload;
use App\Domain\DocumentCategory\DocumentCategoryService;
use App\Domain\NetworkProvider\NetworkProvider;
use App\Http\Api\V1\User\UserService as UserService;
use App\Domain\NetworkProvider\NetworkProviderService;
use App\Web\ProductDomainMapping\ProductDomainMappingService as DomainService;
//use App\Http\Api\V1\User\User;
//use App\Notifications\Email\Signup as SignupNotification;
//use Session;
//use Mail;

class NpRegistrationController extends ClientController
{
    use HasFileUpload;

    private static string $module = 'signup.index';

    public function __construct(
        private SnpService $service,
        private UserService $userservice,
        private DomainService $domaintype,
        private NetworkProviderService $networkProviderService,
        private DocumentCategoryService $documentCategoryService
    ) {}

    public function index(): View
    {

        $title = __('Network Participant Registration');
        $domain_type = $this->domaintype->getAllDomainCategory();
        $roleList = $this->service->getRoles();
        $lang = \DB::table('language')->select('*')->get();
        $document_catagories = $this->documentCategoryService->getDocumentCategoriesBySlugs(['flyer_presentation', 'authorized_person_certificate']);
        //dd($document_catagories);
        return view('np_registration.registration', compact('title', 'domain_type', 'roleList', 'lang', 'document_catagories'))
            ->with('lists', (object) $this->service->getDropdownList());
    }

    public function edit(string $id)
    {
        $row = $this->networkProviderService->getNetworkProviderById($id);

        // $row->contact_details = json_decode($row->contact_details, true);

        $title = __('NP Registration');
        $domain_type = $this->domaintype->getAllDomainCategory();
        $roleList = $this->service->getRoles();
        $lang = $this->networkProviderService->getLanguages();
        $document_catagories = $this->documentCategoryService->getDocumentCategoriesBySlugs(['flyer_presentation', 'authorized_person_certificate']);
        //dd($document_catagories);
        return view('np_registration.registration', compact('title', 'domain_type', 'roleList', 'lang', 'document_catagories', 'row'))
            ->with('lists', (object) $this->service->getDropdownList());
    }

    public function getsubdomains(Request $request)
    {
        return $this->success(
            $this->service->getsubdomains($request['domain_ids'])
        );
    }

    public function uploadFlyerPresentationCertificate(Request $request)
    {
        return $this->uploadFileWithValidation(
            $request,
            'file',
            config('upload.flyer_presentation_certificate_document_file_path'),
            BaseRequest::getPdfRules(1536),
            BaseRequest::getCommonFileRulesMessages(1536)
        );
    }
    public function uploadAuthorizedCertificate(Request $request)
    {
        return $this->uploadFileWithValidation(
            $request,
            'file',
            config('upload.authorized_certificate_document_file_path'),
            BaseRequest::getCommonFileRules(5120),
            BaseRequest::getCommonFileRulesMessages(5120)
        );
    }

    public function uploadCancelledCheque(Request $request)
    {
        return $this->uploadFileWithValidation(
            $request,
            'file',
            config('upload.cancelled_cheque_document_file_path'),
            BaseRequest::getCommonFileRules(200),
            BaseRequest::getCommonFileRulesMessages(200)
        );
    }

    public function uploadCommercialModel(Request $request)
    {
        return $this->uploadFileWithValidation(
            $request,
            'file',
            config('upload.commercial_model_document_file_path'),
            BaseRequest::getCommonFileRules(5120),
            BaseRequest::getCommonFileRulesMessages(5120)
        );
    }

    public function uploadDescription(Request $request)
    {
        return $this->uploadFileWithValidation(
            $request,
            'file',
            config('upload.description_document_file_path'),
            BaseRequest::getPdfRules(5120),
            BaseRequest::getPdfRuleMessages(5120)
        );
    }

    public function deleteSnpDocument(Request $request)
    {
        return $this->service->deleteDocuments($request->all());
    }
}
