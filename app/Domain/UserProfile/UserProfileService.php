<?php

declare(strict_types=1);

namespace App\Domain\UserProfile;

use App\Http\Services\CommonService;
use App\Http\Api\V1\User\UserService as UserService;
use App\Traits\HasAttribute;
use App\Domain\Audit\Audit;
use App\Domain\NetworkProvider\NetworkProvider;
use App\Domain\NetworkProvider\NetworkProviderService;
use App\Web\ProductDomainMapping\ProductDomainMappingService;
use App\Domain\DocumentCategory\DocumentCategoryService;
use App\Web\NPRegistration\SnpService;
use App\Domain\Language\Language;
use App\Domain\NetworkProvider\FeeType;
use Illuminate\Support\Facades\DB;
use App\Web\IARegistration\IAService;
use App\Domain\IARegistration\IndustrialAssociation;
use App\Web\IARegistration;
use App\Http\Api\V1\Signup\{SignupService};

class UserProfileService
{
    use HasAttribute;

    public function __construct(
        private SnpService $snpService,
        private NetworkProviderService $networkProviderService,
        private ProductDomainMappingService $mappingService,
        private DocumentCategoryService $documentCategoryService,
        private SignupService $signupService,
    ) {
        $this->signupService = $signupService;
    }

    public function getProfileViewDetails(string $userId): array
    {
        crypto_secrets();

        // $snpData = $this->networkProviderService->getSnpDetailsByUserId($userId);
        // $npData = $this->networkProviderService->getNetworkProviderByUserId($userId);
        // dd($snpData, $npData);

        $userRoles = $this->getUserRoles($userId);
        $parentUserId = DB::table('users')->where('id', $userId)->value('parent_user_id');
        $CommonService = app(CommonService::class);

        if (
            empty($parentUserId) && (in_array('snp', $userRoles) ||
                in_array('bnp', $userRoles) ||
                in_array('lsp', $userRoles))
        ) {
            return [
                'view' => 'np_registration.profile',
                'data' => [
                    'title' => __('profile.my_profile'),
                    'module_url' => 'users.index',
                    'id' => $userId,
                    'userRoles' => $userRoles,
                    'row' => $this->networkProviderService->getNetworkProviderByUserId($userId),
                    'snpData' => hasRole('snp') ? (array) $this->networkProviderService->getSnpDetailsByUserId($userId) : null,
                    'domain_type' => $this->mappingService->getAllDomainCategory(),
                    'roleList' => $this->snpService->getRoles(),
                    'lang' => $this->networkProviderService->getLanguages(),
                    'lists' => (object) $this->snpService->getDropdownList(),
                    'document_catagories' => $this->documentCategoryService->getDocumentCategoriesBySlugs([
                        'flyer_presentation',
                        'authorized_person_certificate'
                    ]),
                ]
            ];
        } elseif (in_array('ia-registration', $userRoles)) {

            $iaData = DB::table('industrial_associations as ia')
                ->join('users as u', 'u.id', '=', 'ia.user_id')
                ->leftJoin('file_uploads as fu', 'fu.id', '=', 'ia.authorization_document_id')
                ->where('ia.user_id', $userId)
                ->select(
                    'ia.*',
                    'u.email as user_email',
                    'u.mobile as user_mobile',
                    'fu.file_name as authorization_file_name',
                    'fu.file_system_name as authorization_file_system_name',
                    'fu.file_path as authorization_file_path'
                )->first();

            return [
                'view' => 'ia-registration.profile',
                'data' => [
                    'title' => __('Associate Registrations Profile'),
                    'row' => (array) $iaData,
                    'id' => $iaData?->id,
                    'documents' => app(IAService::class)->getClaimDocuments('upload-authorization-certificate-document'),
                    'lists' => (object) app(IAService::class)->getDropdownList(),
                    'editableFields' => config('ia_profile.editable_fields'),
                ]
            ];
        } elseif (in_array('msme', $userRoles)) {

            $msmeData = DB::table('team_msme_schemes as m')
                ->leftJoin('states as s', 's.id', '=', 'm.state_id')
                ->leftJoin('locations as d', 'd.id', '=', 'm.district_id')
                ->where('m.user_id', $userId)
                ->select(
                    'm.*',
                    's.name as state_name',
                    'd.name as district_name',
                )
                ->first();
            $userProfile = app(GetUserProfileDetailsAction::class)->execute($userId);
            $basicDetail = [];
            if ($msmeData) {
                $basicDetail = [
                    'EnterpriseName'       => $msmeData->enterprise_name ?? '',
                    'EntrepreneurName'     => $msmeData->entrepreneur_name ?? '',
                    'OrganisationType'     => $msmeData->organisation_type ?? '',
                    'EmailId'              => $msmeData->email ?? '',
                    'CommunicationAddress' => $msmeData->address ?? '',
                    'State'                => $msmeData->state_name ?? '',
                    'District'             => $msmeData->district_name ?? '',
                    'EnterpriseType'       => $msmeData->msme_classification ?? '',
                    'MajorActivity'        => $msmeData->major_activity ?? '',
                    'turnover'             => $msmeData->turnover ?? '',
                    'pan_no'             => $msmeData->pan_no ?? '',
                    'gstin_no'             => $msmeData->gstin_no ?? '',
                ];
            }

            return [
                'view' => 'applicant_signup.profile',
                'data' => [
                    'title' => __('profile.my_profile'),
                    'module_url' => 'users.index',
                    'id' => $userId,
                    'isProfile' => true,
                    'crypto_salt' => session('crypto_salt'),
                    'crypto_iv' => session('crypto_iv'),
                    'crypto_key' => session('crypto_key'),
                    'crypto_key_size' => session('crypto_key_size'),
                    'crypto_iterations' => session('crypto_iterations'),
                    'row' => (array) $userProfile,
                    'lists' => (object) $this->signupService->getDropdownList(),
                    'udetails' => [
                        'BasicDetail' => $basicDetail,
                        'roles' => $userId ? $this->getUserRoleOptions($userId) : [],
                        'role_list' => $CommonService->getRoles(),
                        'departments' => $CommonService->getDepartments(),
                        'designations' => $CommonService->getDesignations(),
                        'countries' => $CommonService->getCountries(),
                        'states' => $CommonService->getStates(),
                        'districts' => $CommonService->getDistricts(),
                        'status' => $CommonService->getStatus(),
                    ],
                    'selected' => [
                        'current_state_business_id' => $msmeData->current_state_business_id ?? '',
                        'attending_ondc_awareness_workshop' => $msmeData->attending_ondc_awareness_workshop ?? '',
                        'ondc_transaction_type_id' => $msmeData->ondc_transaction_type_id ?? '',
                        'select_snp' => $msmeData->select_snp ?? '',
                    ],
                ]
            ];
        } else {

        if (hasRole('nsic') || hasRole('nsic-finance') || hasRole('ondc-admin') ) 
            $middleNameDisabled = $emailDisabled = $hiddenStateId = true;    
        else 
            $middleNameDisabled = $emailDisabled = $hiddenStateId = false;   

            return [
                'view' => 'users.form',
                'data' => [
                    'title' => __('profile.my_profile'),
                    'module_url' => 'users.index',
                    'id' => $userId,
                    'isProfile' => true,
                    'crypto_salt' => session('crypto_salt'),
                    'crypto_iv' => session('crypto_iv'),
                    'crypto_key' => session('crypto_key'),
                    'crypto_key_size' => session('crypto_key_size'),
                    'crypto_iterations' => session('crypto_iterations'),
                    'row' => (array) app(GetUserProfileDetailsAction::class)->execute($userId),
                    'middleNameDisabled' => $middleNameDisabled,
                    'emailDisabled' => $emailDisabled,
                    'hiddenStateId' => $hiddenStateId,  
                    'details' => (object) [
                        'roles' => $userId ? $this->getUserRoleOptions($userId) : [],
                        'role_list' => $CommonService->getRoles(),
                        'departments' => $CommonService->getDepartments(),
                        'designations' => $CommonService->getDesignations(),
                        'countries' => $CommonService->getCountries(),
                        'states' => $CommonService->getStates(),
                        'districts' => $CommonService->getDistricts(),
                        'status' => $CommonService->getStatus(),
                    ]

                ]
            ];
        }
    }

    public function getUserRoles(string $userId): array
    {
        return DB::table('user_roles as ur')
            ->join('roles as r', 'ur.role_id', '=', 'r.id')
            ->select('r.slug')
            ->where('user_id', $userId)
            ->pluck('r.slug')
            ->toArray();
    }

    public function getUserRoleOptions(string $userId): array
    {
        return DB::table('user_roles as ur')
            ->join('roles as r', 'ur.role_id', '=', 'r.id')
            ->where('ur.user_id', $userId)
            ->select('r.id as id', 'r.name as text')
            ->get()
            ->toArray();
    }

    public function getProfileRevisions(string $id)
    {
        if (hasRole('snp') || hasRole('bnp') || hasRole('lsp')) {
            $auditableClass = NetworkProvider::class;
            $auditableId = DB::table('network_providers')->where('user_id',getSubUserAndParentIds($id))->value('id');
        } else if (hasRole('ia-registration')) {
            $auditableClass = IndustrialAssociation::class;
            $auditableId = DB::table('industrial_associations')->where('user_id', getSubUserAndParentIds($id))->value('id');
        }

        return Audit::where('auditable_type', $auditableClass)
            ->where('auditable_id', $auditableId)
            ->where('event', 'updated')
            ->latest()
            ->get();
    }

    public function getProfileSnaphotByAuditId(string $id)
    {
        if (hasRole('snp') || hasRole('bnp') || hasRole('lsp')) {
            return $this->getNetworkProviderProfileDetails($id);
        } else if (hasRole('ia-registration')) {
            // return $this->getIAProfileDetails($id);

        }
    }

    public function getRoleNameById(string $roleId): ?string
    {
        return DB::table('roles')->where('id', $roleId)->value('name');
    }

    public function getFileDetails(string $fileId)
    {
        $file = DB::table('file_uploads')->where('id', $fileId)->first();

        if ($file) {
            return [
                'document_link' => url('storage/app/' . $file->file_path . '/' . $file->file_system_name),
                'document_name' => $file->file_name
            ];
        }

        return [];
    }

    public function getNetworkProviderProfileDetails($id)
    {
        $oldValues = Audit::where('id', $id)->value('old_values');


        $networkProvider = [];

        if ($oldValues) {
            // Decode all JSON fields
            $jsonFields = [
                'roles',
                'contact_details',
                'authorized_person_details',
                'configuration_details',
                'bank_details',
                'role_selection_details',
                'value_proposition_details',
                'commercial_model_details'
            ];

            foreach ($jsonFields as $field) {
                if (!empty($oldValues[$field])) {
                    $oldValues[$field] = json_decode($oldValues[$field], true);
                }
            }

            $networkProvider = (object) $oldValues;
        }

        if (isset($networkProvider->contact_details) && !empty($networkProvider->contact_details)) {
            // $networkProvider->contact_details = json_decode($networkProvider->contact_details, true);
            $networkProvider->email = data_get($networkProvider->contact_details, 'email');
            $networkProvider->website = data_get($networkProvider->contact_details, 'website');
            $networkProvider->whatsapp_no = data_get($networkProvider->contact_details, 'whatsapp_no');
            $networkProvider->app_store_links = data_get($networkProvider->contact_details, 'app_store_links');
            $networkProvider->primary_contact_no = data_get($networkProvider->contact_details, 'primary_contact_no');
            $networkProvider->social_media_handles = data_get($networkProvider->contact_details, 'social_media_handles');
        }

        if (isset($networkProvider->authorized_person_details) && !empty($networkProvider->authorized_person_details)) {
            // $networkProvider->authorized_person_details = json_decode($networkProvider->authorized_person_details, true);
            $networkProvider->authorized_person_name = data_get($networkProvider->authorized_person_details, '0.name');
            $networkProvider->authorized_person_designation = $this->getRoleNameById(data_get($networkProvider->authorized_person_details, '0.designation'));
            $networkProvider->authorized_person_email = data_get($networkProvider->authorized_person_details, '0.email');
            $networkProvider->authorized_person_contact_no = data_get($networkProvider->authorized_person_details, '0.phone');

            $document = $this->getFileDetails(data_get($networkProvider->authorized_person_details, '0.certificate_id'));

            if ($document) {
                $networkProvider->authorized_person_certificate = $document['document_link'];
                $networkProvider->authorized_person_certificate_name = $document['document_name'];
            }
        }

        if (isset($networkProvider->role_selection_details) && !empty($networkProvider->role_selection_details)) {
            // $networkProvider->role_selection_details = json_decode($networkProvider->role_selection_details, true);
        }

        if (isset($networkProvider->configuration_details) && !empty($networkProvider->configuration_details)) {
            // $networkProvider->configuration_details = json_decode($networkProvider->configuration_details, true);
            $networkProvider->cin = data_get($networkProvider->configuration_details, 'cin');
            $networkProvider->pan = data_get($networkProvider->configuration_details, 'pan');
            $networkProvider->gst_number = data_get($networkProvider->configuration_details, 'gst_number');
            $networkProvider->iec_number = data_get($networkProvider->configuration_details, 'iec_number');
            $networkProvider->startup_id = data_get($networkProvider->configuration_details, 'startup_id');
            $networkProvider->fssai_number = data_get($networkProvider->configuration_details, 'fssai_number');
        }

        if (isset($networkProvider->bank_details) && !empty($networkProvider->bank_details)) {
            // $networkProvider->bank_details = json_decode($networkProvider->bank_details, true);
            $networkProvider->bank_name = data_get($networkProvider->bank_details, 'bank_name');
            $networkProvider->ifsc_code = data_get($networkProvider->bank_details, 'ifsc_code');
            $networkProvider->account_number = data_get($networkProvider->bank_details, 'account_number');
        }

        if (isset($networkProvider->value_proposition_details) && !empty($networkProvider->value_proposition_details)) {
            // $networkProvider->value_proposition_details = json_decode($networkProvider->value_proposition_details, true);
            $networkProvider->flyer = data_get($networkProvider->value_proposition_details, 'flyer');
            $networkProvider->short_description = data_get($networkProvider->value_proposition_details, 'short_description');
            $networkProvider->short_video_pitch = data_get($networkProvider->value_proposition_details, 'short_video_pitch');

            $languageIds = (array) data_get($networkProvider->value_proposition_details, 'language_supported', []);
            $networkProvider->language_supported = Language::whereIn('id', $languageIds)->pluck('name')->implode(', ');

            $networkProvider->additional_services = data_get($networkProvider->value_proposition_details, 'additional_services');


            $document = $this->getFileDetails(data_get($networkProvider->value_proposition_details, 'flyer_id'));

            if ($document) {
                $networkProvider->flyer_document = $document['document_link'];
                $networkProvider->flyer_document_name = $document['document_name'];
            }

            $networkProvider->team_scheme_landing_page = data_get($networkProvider->value_proposition_details, 'team_scheme_landing_page');
        }

        if (isset($networkProvider->commercial_model_details) && !empty($networkProvider->commercial_model_details)) {
            // $networkProvider->commercial_model_details = json_decode($networkProvider->commercial_model_details, true);

            $feeTypeValue = data_get($networkProvider->commercial_model_details, 'fee_type');
            $networkProvider->fee_type = $feeTypeValue ? FeeType::tryFrom($feeTypeValue)?->label() : '';

            $networkProvider->flat_fee = data_get($networkProvider->commercial_model_details, 'flat_fee');
            $networkProvider->fee_charge = data_get($networkProvider->commercial_model_details, 'fee_charge');
            $networkProvider->other_fees = data_get($networkProvider->commercial_model_details, 'other_fees');
            $networkProvider->special_offers = data_get($networkProvider->commercial_model_details, 'special_offers');
            $networkProvider->team_scheme_offers = data_get($networkProvider->commercial_model_details, 'team_scheme_offers');
            $networkProvider->commercial_model_link = data_get($networkProvider->commercial_model_details, 'commercial_model_link');
            $networkProvider->commission_per_transaction = data_get($networkProvider->commercial_model_details, 'commission_per_transaction');
        }

        return $networkProvider;
    }
}
