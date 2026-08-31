<?php

declare(strict_types=1);

namespace App\Http\Services;

use App\Models\Address;
use Illuminate\Support\Str;
use DB;

use App\Http\Api\V1\ModulePermissionTree\ModulePermissionTreeRepository as Repository;

class CommonService
{
    private const CHILD_EXISTS = 1;
    private const CHILD_NOT_EXISTS = 0;

    public static function getDropdownList(string $table, ?string $key = null, $status = null, ?array $columns = [])
    {

        if (! $columns) {
            $columns = ['id', 'name'];
        }

        $query = \DB::table($table)
            ->select(...$columns);
        if ($table === 'locations') {
            $query->where('name', '!=', 'N/A')->where('slug', '!=', 'n-a');
        }
        if ($status == null) {
            $query->where('status', config('constant.ACTIVE'));
        }

        if ($table === 'production_categories' && $status == 'interim') {
            $query->where('slug', '!=', 'tv-reality-show-series');
        }

        if ($table === 'production_categories' && $status == 'annimation') {
            $query->where('status', config('constant.ACTIVE'));
            $query->where('slug', '!=', 'tv-reality-show-series');
        }

        if (in_array($table, ['countries', 'signed_authorities'])) {
            $query->orderBy('name', 'asc');
        }

        if ($table === 'coach_types') {
            $query->orderBy('sort_order', 'asc');
        }

        $result = $query->get();

        $list[''] = 'Select';

        if ($result) {
            foreach ($result as $row) {
                $list[$row->id] = $row->name;
            }
        }
        return ($key) ? $list[$key] : $list;
    }


    public function getDropdownNewList(string $tableName, string $statusColumn, string $odrderBy = 'ASC', string $odrderByColumn, array $selectedColumns, ?string $referenceIdValue = NULL, ?string $referenceIdColumn = NULL)
    {

        $query = DB::table($tableName)->select($selectedColumns)->where($statusColumn, config('constant.ACTIVE'));

        if ($referenceIdValue) {
            $query->where($referenceIdColumn, $referenceIdValue);
        }

        $query->orderBy($odrderByColumn, $odrderBy);

        return $query->get();
    }

    public function snp_commercial()
    {
        return ['Subdomain' => 'Subdomain', 'Channel Wise' => 'Channel Wise'];
    }

    public function AddAddress($address)
    {
        return Address::create($address);
    }

    public function getRoleId_by_slug($slug)
    {
        return DB::table('roles')
            ->select('id', 'name')
            ->where('slug', $slug)
            ->first();
    }

    public function getCategoryId_by_slug($slug)
    {
        return DB::table('categories')
            ->select('id', 'name')
            ->where('slug', $slug)
            ->get();
    }

    public function EditAddress($payload, $id)
    {
        $model = Address::findOrFail($id);
        return $model->fill($payload)->save();
    }

    public function getCategories()
    {
        return DB::table('categories AS c')
            ->select('c.id', 'c.name')
            ->where('c.status', config('constant.ACTIVE'))
            ->whereRaw('
                c.level >= (
                    SELECT MIN(c2.level)
                    FROM users AS u
                    INNER JOIN user_roles AS ur ON u.id = ur.user_id 
                    INNER JOIN roles AS r ON ur.role_id = r.id
                    INNER JOIN categories AS c2 ON r.category_id = c2.id
                    WHERE u.id = ?
                )
            ', [AuthId()])
            //->where('slug','!=','govt-organization')
            ->get();
    }


    public function getCountries()
    {
        return DB::table('countries')
            ->select('id', 'name', 'iso2_code', 'iso2_code')
            ->where('status', config('constant.ACTIVE'))
            ->get();
    }

    public function getMajorComponents()
    {
        return DB::table('major-components')
            ->select('id', 'name')
            ->where('status', config('constant.ACTIVE'))
            ->get();
    }

    public function getComponents($countryId = NULL)
    {
        $query = DB::table('components')
            ->select('id', 'name')
            ->where('status', config('constant.ACTIVE'));
        if ($countryId) {
            $query->where('major_component_id', $countryId);
        }
        return $query->get();
    }

    public function getSubComponents($countryId = NULL)
    {
        $query = DB::table('sub-components')
            ->select('id', 'name')
            ->where('status', config('constant.ACTIVE'));
        if ($countryId) {
            $query->where('component_id', $countryId);
        }
        return $query->get();
    }

    public static function getInternationaCountries()
    {
        return DB::table('countries')
            ->select('id', 'name', 'iso2_code')
            ->where('slug', '!=', 'india')
            ->where('status', config('constant.ACTIVE'))
            ->get();
    }


    public function getStates($countryId = NULL)
    {
        $query = DB::table('states')
            ->select('id', 'name', 'code')
            ->where('status', config('constant.ACTIVE'));
        if ($countryId) {
            $query->where('country_id', $countryId);
        }
        return $query->get();
    }

    public function getDistricts($stateId = NULL)
    {
        $query = DB::table('locations')
            ->select('id', 'name', 'code')
            ->where('status', config('constant.ACTIVE'));
        if ($stateId) {
            $query->where('state_id', $stateId);
        }
        return $query->get();
    }

    public function getRegisterTypes()
    {
        return DB::table('register_types')
            ->select('id', 'name')
            ->where('status', config('constant.ACTIVE'))
            ->get();
    }



    public function getStatus()
    {
        return [config('constant.ACTIVE') => __('message.active'), config('constant.INACTIVE') => __('message.in_active')];
    }


    public function getGender()
    {
        return [config('constant.MALE') => 'Male', config('constant.FEMALE') => 'Female', config('constant.OTHER') => 'Other'];
    }


    public function socialCategory()
    {
        return ['General' => 'General', 'OBC' => 'OBC', 'SC' => 'SC', 'ST' => 'ST'];
    }



    public function getYesNoStatus()
    {
        return [config('constant.YES') => __('message.yes'), config('constant.NO') => __('message.no')];
    }

    public function getRegisterType()
    {
        return DB::table('register_types')
            ->select('id', 'name')
            ->where('status', config('constant.ACTIVE'))
            ->get();
    }

    public function getMinerals()
    {
        return DB::table('minerals')
            ->select('id', 'name')
            ->where('status', config('constant.ACTIVE'))
            ->get();
    }

    public function getLicencesType()
    {
        return DB::table('licence_types')
            ->select('id', 'name')
            ->where('status', config('constant.ACTIVE'))
            ->get();
    }

    public function getBlockAllotted()
    {
        return DB::table('block_allotted')
            ->select('id', 'name')
            ->where('status', config('constant.ACTIVE'))
            ->get();
    }

    public function getBlockType()
    {
        return DB::table('block_types')
            ->select('id', 'name')
            ->where('status', config('constant.ACTIVE'))
            ->get();
    }

    public function getRoles()
    {
        $query = DB::table('roles')
            ->select('id', 'name')
            ->where('status', config('constant.ACTIVE'))
            ->where('slug', '!=', 'administrator');

        if (hasRole('snp')) {
            $query->where('slug', '=', 'snp');
        } elseif (hasRole('bnp')) {
            $query->where('slug', '=', 'bnp');
        } elseif (hasRole('lsp')) {
            $query->where('slug', '=', 'lsp');
        } else {
            $query->where('slug', '!=', 'snp');
        }
        return $query->get()->toArray();
    }

    /* public function getRoles($categoryId = NULL)
    {
        $query = DB::table('roles')
            ->select('id','name')
		    ->where('status', config('constant.ACTIVE'));
		if($categoryId)
		{
			$query->where('category_id', $categoryId);
			return $query->get();
		}else{
			return [];
		}
    } */

    public function getDepartments()
    {
        return DB::table('departments')
            ->select('id', 'name')
            ->where('status', config('constant.ACTIVE'))
            ->where('slug', '!=', 'snp')
            ->get();
    }

    public function getDesignations()
    {
        return DB::table('designations')
            ->select('id', 'name')
            ->where('status', config('constant.ACTIVE'))
            // ->where('slug','!=','super-admin')
            ->get();
    }

    public function getModules()
    {
        return DB::table('modules')
            ->select('id', 'name')
            ->where('status', config('constant.ACTIVE'))
            ->get();
    }

    public function getExistData($table, $key, $value)
    {
        return DB::table($table)
            ->select($key)
            ->where($key, $value)
            ->first();
    }


    public function getNestedModules()
    {
        return $this->getRecursiveModules();
    }


    public function getRecursiveModules($parentId = null)
    {
        $modules = DB::table('modules')->select('*')->where('parent_id', $parentId)->where('status', config('constant.ACTIVE'))->get();

        $nestedModules = [];

        foreach ($modules as $module) {
            $moduleData = [
                'id' => $module->id,
                'name' => $module->name,
                'children' => $this->getRecursiveModules($module->id),
            ];

            $nestedModules[] = $moduleData;
        }

        return $nestedModules;
    }

    //Previously used for sidebar menu
    function buildHierarchy()
    {
        $result = DB::table('modules')->where('status', config('constant.ACTIVE'))->get()->toArray();
        if (count($result) > 0) {
            return   $this->buildTree(json_decode(json_encode($result), null));
        }
    }

    function buildTree(array $elements, $parentId = null)
    {
        $hierarchy = [];
        foreach ($elements as $element) {
            if ($element->parent_id == $parentId) {
                $children = $this->buildTree($elements, $element->id);
                if ($children) {
                    $element->children = $children;
                }
                $hierarchy[] = $element;
            }
        }
        return $hierarchy;
    }
    //Cutently used in sidebar menu
    public function getModuleTree(): array
    {
        return $this->buildModulePermissionTree();
    }

    private function buildModulePermissionTree(?string $parentId = null): array
    {
        $tree = [];

        if ($modules = $this->getModulesByParentId($parentId)) {
            foreach ($modules as $module) {
                $children = $this->buildModulePermissionTree($module->id);

                $permissions = [];

                if (count($children) === 0) {
                    $permissions = $this->getPermissionByModuleId($module->id);
                }

                $treeData = [
                    'id' => $module->id,
                    'name' => $module->name,
                    'url' => $module->url,
                    'icon' => $module->icon,
                    'slug' => $module->slug,
                    'have_children' => count($children) > 0 ? self::CHILD_EXISTS : self::CHILD_NOT_EXISTS,
                    'children' => $children,
                    'have_permissions' => count($permissions) > 0 ? self::CHILD_EXISTS : self::CHILD_NOT_EXISTS,
                    'permissions' => $permissions
                ];

                $tree[] = $treeData;
            }
        }

        return $tree;
    }

    public function getModulesByParentId(string | null $parentId): array
    {
        $query = DB::table('modules')
            ->select('id', 'name', 'url', 'icon', 'slug');

        if (is_null($parentId)) {
            $query->whereRaw('parent_id IS NULL');
        } else {
            $query->where('parent_id', $parentId);
        }

        $query->where('status', config('constant.ACTIVE'));
        // if (\Session::has('project_type') && ((int) session('project_type')) === \App\Enums\ProductionType::International->value) 
        // {
        //     $query->where('module_type', \App\Enums\ProductionType::International->value);
        // }

        // if (\Session::has('project_type') && ((int) session('project_type')) === \App\Enums\ProductionType::Domestic->value) 
        // {
        //     $query->where('module_type', \App\Enums\ProductionType::Domestic->value);
        // }

        if (\Session::has('project_type') && ((int) session('project_type')) === \App\Enums\ProductionType::Domestic->value) {
            $query->whereNotIn('slug', [
                'filming-permission-for-live-action-shoot',
                'official-coproduction-live-action',
                'official-coproduction-live-animation-only',
                'animation-post-production-services',
                'incentive-tracking',
                'official-coproduction-documentary'
            ]);
        }
        if (\Session::has('project_type') && ((int) session('project_type')) === \App\Enums\ProductionType::International->value) {
            $query->whereNotIn('slug', [
                'project-profile'
            ]);
        }


        return $query->orderBy('sort_order', 'asc')->get()->toArray();
    }

    public function getPermissionByModuleId(string $moduleId): array
    {
        return DB::table('permissions AS p')
            ->select('p.slug')
            ->where('p.status', (int) config('constant.ACTIVE'))
            ->where('p.module_id', $moduleId)
            ->get()
            ->toArray();
    }

    public function getUsersBySlug($slug = NULL)
    {
        $query = DB::table('users')
            ->selectRaw('users.id, CONCAT_WS(" ", users.first_name, users.middle_name, users.last_name) AS full_name');

        if ($slug) {
            $query->join('user_roles', 'user_roles.user_id', '=', 'users.id');
            $query->join('roles', 'roles.id', '=', 'user_roles.role_id');
            $query->where('roles.slug', $slug);
            $query->where('roles.status', config('constant.ACTIVE'));
        }
        $query->where('users.status', config('constant.ACTIVE'));
        return $query->get();
    }

    public function getNationalPermissionApplicationDetails($appId)
    {
        $query = DB::table('national_permission_applications as npa')
            ->join('national_permission_production_details as nppd', 'nppd.national_permission_application_id', '=', 'npa.id')
            ->selectRaw('npa.application_number,nppd.script_title,DATE_FORMAT(npa.applied_at,"%d-%m-%Y") as application_date,npa.is_sent_to_ffo,npa.is_sent_to_mha,npa.review_status,npa.mib_review_status_for_restricted,npa.is_sent_to_mib_for_restricted,is_sent_to_mib,is_sent_to_script_evaluator,is_sent_to_legal_officer,is_sent_to_mib_for_legal,script_evaluator_status,mib_review_status_for_legal,mib_review_status,mha_review_status,legal_officer_review_status,legal_hold,nppd.is_co_production_animation')
            ->where('npa.id', $appId);

        return $query->first();
    }

    public function getStateBySlug($slug = NULL)
    {
        $query = DB::table('states')
            ->selectRaw('id, name');
        $query->where('slug', $slug);
        $query->where('status', config('constant.ACTIVE'));
        return $query->first();
    }

    public function getCityBySlug($slug = NULL)
    {
        $query = DB::table('locations')
            ->selectRaw('id, name');
        $query->where('slug', $slug);
        $query->where('status', config('constant.ACTIVE'));
        return $query->first();
    }

    public function isForwarded($appId)
    {
        return \DB::table('national_permission_query_histories as nph')
            ->where('national_permission_application_id', $appId)
            //->where('is_implicit',0)
            ->whereIn('type', [3])
            ->latest()
            ->first();
    }

    public function isLegalForwarded($appId)
    {
        return \DB::table('national_permission_query_histories as nph')
            ->where('national_permission_application_id', $appId)
            //->where('is_implicit',0)
            ->whereIn('type', [5])
            ->latest()
            ->first();
    }

    public function isNPForwarded($appId)
    {
        return \DB::table('national_permission_query_histories as nph')
            ->where('national_permission_application_id', $appId)
            ->where('status', '!=', 5)
            ->whereIn('workflow_type', [1, 4])
            ->whereIn('type', [1, 4, 2])
            ->latest()
            ->first();
    }

    public function isNPrevertByFFO($appId)
    {
        return \DB::table('national_permission_query_histories as nph')
            ->where('national_permission_application_id', $appId)
            ->where('status', '!=', 5)
            ->where('workflow_type', 1)
            ->whereIn('type', [1])
            ->latest()
            ->first();
    }

    public function getUserIdByApplicationId($appID)
    {
        $query = DB::table('national_permission_applications as npa')
            ->join('applicants AS a', 'npa.applicant_id', '=', 'a.id')
            ->join('users AS u', 'a.user_id', '=', 'u.id')
            ->selectRaw('u.id')
            ->where('npa.id', $appID);
        return $query->first();
    }

    public function getUserDetails($id)
    {
        $query = DB::table('users as u')
            ->select('u.*')
            ->where('u.id', $id);

        return $query->first();
    }


    public function updateApplicantQueryIsClosed($appID, $query_type)
    {
        \DB::table('applicant_queries')->where('national_permission_application_id', $appID)->where('query_type', $query_type)->update(['is_closed' => 1]);
    }

    public function getLastRowByType($appId, $type, $workflowType)
    {
        return \DB::table('national_permission_query_histories as nph')
            ->where('national_permission_application_id', $appId)
            ->where('workflow_type', $workflowType)
            ->whereIn('type', $type)
            ->where('status', '!=', 10)
            ->latest()
            ->first();
    }

    public function getRelevantQuestionsByFormType($formType)
    {
        return \DB::table('incentive_questions as iq')
            ->join('incentive_question_form_types AS iqft', 'iqft.incentive_question_id', '=', 'iq.id')
            ->where('iqft.incentive_form_type_id', $formType)
            ->orderBy('sort_order', 'ASC')
            ->get();
    }

    public function getCheckListDocumentByFormType($formType)
    {
        return \DB::table('interim_documents as id')
            ->join('interim_document_form_types AS idft', 'idft.interim_document_id', '=', 'id.id')
            ->where('idft.incentive_form_type_id', $formType)
            ->orderBy('sort_order', 'ASC')
            ->get();
    }

    public function getFinalDocumentByFormType($formType, $disbursalType = '')
    {
        $query =  \DB::table('final_documents');
        $query =  $query->where('form_type_id', $formType);
        if ($disbursalType)
            $query =  $query->where('disbursal_type', $disbursalType);
        $query =  $query->orderBy('sort_order', 'ASC')
            ->get();
        return $query;
    }

    public function getProductionTitleByCategory($payload)
    {

        $query = DB::table('national_permission_production_details as nppd')
            ->join('national_permission_applications as npa', 'npa.id', 'nppd.national_permission_application_id')
            ->select('nppd.national_permission_application_id', 'nppd.script_title')
            ->where('nppd.production_category_id', $payload['id']);

        if ($payload['type'] == 'filming') {
            $query->where('nppd.is_co_production', 0);
            $query->where('nppd.is_co_production_animation', null);
            $query->where('npa.review_status', 1);
            $query->where('npa.line_producer_id', AuthId());
        }
        if ($payload['type'] == 'coproduction') {
            $query->where('nppd.is_co_production', 1);
            $query->where('nppd.is_co_production_animation', null);
            $query->where('npa.mib_review_status_for_legal', 1);
            $query->where('npa.review_status', 1);
            $query->where('npa.line_producer_id', AuthId());
        }
        if ($payload['type'] == 'coproductionanimation') {
            $query->where('nppd.is_co_production', 0);
            $query->where('nppd.is_co_production_animation', 1);
            $query->where('npa.mib_review_status_for_legal', 1);
            $query->where('npa.review_status', 1);
            $query->where('npa.line_producer_id', AuthId());
        }
        if ($payload['type'] == 'all') {
            $query->where('npa.review_status', 1);
            $query->where('nppd.is_co_production_animation', null);
            $query->where('npa.created_by', AuthId());
        }
        //dd($query->toSql());
        return $query->get();
    }

    public function getDocumentaryTitleByCategory($payload)
    {

        $query = DB::table('documentary_applications as da')
            ->select('da.id', 'da.script_title')
            ->where('da.review_status', 1)
            ->where('da.mib_review_status_for_legal', 1)
            //->where('da.created_by', AuthId());	
            ->where('da.line_producer_id', AuthId());
        return $query->get();
    }

    public function getCoProductionStatusByCategory($payload)
    {
        if ($payload['type'] == 'documentary') {
            $query = DB::table('documentary_applications as da')
                ->select('da.signed_authority_id as co_production_signed_authority_id', 'dl.document_name as co_production_status_letter_name', 'dl.document_original_name as co_production_status_original_name', 'da.script_title')
                ->leftjoin('documentary_letters AS dl', 'da.id', '=', 'dl.documentary_application_id');
            $query->where('da.review_status', 1);
            $query->where('da.mib_review_status_for_legal', 1);
            $query->where('da.id', $payload['title_id']);
            //$query->where('da.created_by', AuthId());		
            $query->where('da.line_producer_id', AuthId());
            return $query->first();
        }
        if ($payload['type'] == 'nationalApplication') {
            $query = DB::table('national_permission_production_details as nppd')
                ->select(
                    'nppd.co_production_signed_authority_id',
                    'dl.document_name as co_production_status_letter_name',
                    'dl.document_original_name as co_production_status_original_name',
                    'nppd.script_title'
                )
                ->join('national_permission_applications as npa', 'npa.id', '=', 'nppd.national_permission_application_id')
                ->leftjoin('document_letters AS dl', 'nppd.national_permission_application_id', '=', 'dl.national_permission_application_id');
            $query->leftJoin('document_types AS dt', function ($query) {
                $query->on('dl.document_type_id', '=', 'dt.id');
                $query->where('dt.slug', 'co-production-status');
            });


            $query->where('nppd.national_permission_application_id', $payload['title_id']);
            //$query->where('nppd.created_by', AuthId());				
            $query->where('npa.line_producer_id', AuthId());
            return $query->first();
        }
    }

    public function getInterimApplicationDetails($interim_application_id)
    {
        $query = DB::table('incentive_interim_applications')
            ->select('*')
            ->where('id', $interim_application_id);
        return $query->first();
    }

    public function getEligibilityFormData($id)
    {
        $query = DB::table('interim_eligibilities as ie')
            ->leftjoin('national_permission_production_details as nppd', 'nppd.national_permission_application_id', '=', 'ie.national_permission_application_id')
            ->select('ie.national_permission_application_id', 'ie.production_category_id', 'ie.eligibility_details', 'ie.script_title as docscript', 'nppd.script_title as npscript', 'ie.project_qpe', 'ie.signing_contract_date')
            ->where('ie.id', $id);
        return $query->first();
    }

    public function getProductionCategoryName($id)
    {
        $query = DB::table('production_categories as pc')
            ->select('pc.id', 'pc.name')
            ->where('pc.id', $id);
        return $query->first();
    }

    public function getDocumentaryLastRowByType($appId, $type, $workflowType)
    {
        return \DB::table('documentary_permission_query_histories as nph')
            ->where('documentary_applications_id', $appId)
            ->where('workflow_type', $workflowType)
            ->whereIn('type', $type)
            ->latest()
            ->first();
    }
    public function lastDocumentaryRow($appId, $historyType, $workflowType)
    {
        return \DB::table('documentary_permission_query_histories as nph')
            ->select('nph.*')
            ->where('documentary_applications_id', $appId)
            ->where('type', $historyType)->where('workflow_type', $workflowType)
            ->where('status', '!=', 5)
            ->latest()
            ->first();
    }

    public function getDocumentaryApplicationDetails($appId)
    {
        $query = DB::table('documentary_applications as npa')
            ->selectRaw('npa.application_number,npa.script_title,DATE_FORMAT(npa.applied_at,"%d-%m-%Y") as application_date,npa.is_sent_to_ffo,npa.review_status,is_sent_to_legal_officer,is_sent_to_mib_for_legal,mib_review_status_for_legal,legal_officer_review_status,created_by,legal_hold')
            ->where('npa.id', $appId);

        return $query->first();
    }

    public function isDocumentaryLegalForwarded($appId)
    {
        return \DB::table('documentary_permission_query_histories as nph')
            ->where('documentary_applications_id', $appId)
            //->where('is_implicit',0)
            ->whereIn('type', [5])
            ->latest()
            ->first();
    }

    public function getApplicationDetail(string $appId)
    {
        $query = DB::table('incentive_final_applications as ifa')
            ->selectRaw('ifa.*,DATE_FORMAT(ifa.created_at,"%d-%m-%Y") as application_date')
            ->where('ifa.id', $appId);

        return $query->first();
    }
    public function getIncentiveFinalApplicationsDetails($final_disbursal_application_id)
    {
        $query = DB::table('incentive_final_applications')
            ->select('*')
            ->where('id', $final_disbursal_application_id);
        return $query->first();
    }
    public static function getDropdownListOfDistrictByState(string $table, $state_id, $status = null, ?array $columns = [])
    {

        if (! $columns) {
            $columns = ['id', 'name'];
        }

        $query = \DB::table($table)
            ->select(...$columns);
        $query->where('state_id', $state_id);
        if ($status == null) {
            $query->where('status', config('constant.ACTIVE'));
        }

        $query->where('status', config('constant.ACTIVE'));
        if (in_array($table, ['countries', 'signed_authorities'])) {
            $query->orderBy('name', 'asc');
        }

        $result = $query->get();

        $list[''] = 'Select';

        if ($result) {
            foreach ($result as $row) {
                $list[$row->id] = $row->name;
            }
        }
        return $list;
    }

    public static function getDropdownListByState(string $table, $state_id, $status = null, ?array $columns = [])
    {

        if (! $columns) {
            $columns = ['id', 'name'];
        }

        $query = \DB::table($table)
            ->select(...$columns)
            ->join('state_to_location_categories as sl', 'sl.location_category_id', '=', 'location_categories.id');
        $query->where('sl.state_id', $state_id);


        $query->where('status', config('constant.ACTIVE'));
        if (in_array($table, ['countries', 'signed_authorities'])) {
            $query->orderBy('name', 'asc');
        }

        $result = $query->get();
        //dd($query->toSql());
        $list[''] = 'Select';

        if ($result) {
            foreach ($result as $row) {
                $list[$row->id] = $row->name;
            }
        }
        return $list;
    }

    public static function getDropdownListOfIndianState(string $table, $state_id, $status = null, ?array $columns = [])
    {

        if (! $columns) {
            $columns = ['id', 'name'];
        }
        $query = \DB::table($table)
            ->select(...$columns);

        $query->where('status', config('constant.ACTIVE'));
        $query->where('country_id', 'a572ef09-d6cf-11ee-8177-00155d022d06');


        $result = $query->get();

        $list[''] = 'Select';

        if ($result) {
            foreach ($result as $row) {
                $list[$row->id] = $row->name;
            }
        }

        return $list;
    }

    public static function getAnimationList(string $table, ?string $key = null, $status = null, ?array $columns = [])
    {

        if (! $columns) {
            $columns = ['id', 'name'];
        }

        $query = \DB::table($table)
            ->select(...$columns);
        if ($status == null) {
            $query->where('status', config('constant.ACTIVE'));
        }
        $query->where('slug', '!=', 'tv-reality-show-series');
        $result = $query->get();

        $list[''] = 'Select';

        if ($result) {
            foreach ($result as $row) {
                $list[$row->id] = $row->name;
            }
        }
        return ($key) ? $list[$key] : $list;
    }

    public function getUsersEmailById($userIds)
    {
        return \DB::table('users')
            ->leftjoin('railway_zones', 'users.zone_id', '=', 'railway_zones.id')
            ->select('users.email', 'users.mobile', 'users.first_name', 'users.last_name', 'railway_zones.name as zonename')
            ->whereIn('users.id', $userIds)
            ->get();
        //->toArray();
    }

    public function getRailwayApplicationDetails(string $appId)
    {
        $query = DB::table('railway_permission_applications as rpa')
            ->join('railway_permission_production_details as rppd', 'rppd.railway_permission_application_id', '=', 'rpa.id')
            ->selectRaw('rpa.*,DATE_FORMAT(rpa.created_at,"%d-%m-%Y") as application_date,rppd.script_title')
            ->where('rpa.id', $appId);

        return $query->first();
    }

    public function getZoneUserDetails($id)
    {
        $query = DB::table('users as u')
            ->leftjoin('railway_zones', 'u.zone_id', '=', 'railway_zones.id')
            ->select('u.*', 'railway_zones.name as zonename')
            ->where('u.id', $id);

        return $query->first();
    }

    public function getauthorizationDocumentByNpId($id)
    {
        $query = DB::table('national_permission_local_representaitives as np')
            ->select('np.authorization_letter_document_original_name', 'np.authorization_letter_document')
            ->where('np.national_permission_application_id', $id);
        return $query->first();
    }

    public function getUndertakings($id)
    {

        /*$query = DB::table('undertakings_terms as u')
                    ->leftJoin('national_application_undertakings as nu', 'nu.undertaking_id', '=', 'u.id')
                    ->select('u.id','u.undertaking_text', 'u.is_mandatory', 'nu.national_permission_application_id')
                    ->where('u.status', 1)
                    ->where(function ($q) use ($id) {
                        $q->where('nu.national_permission_application_id', $id)
                        ->orWhereNull('nu.national_permission_application_id'); // Include nulls
                    })
                    ->orderBy('u.sort_order');*/
        $query = DB::table('undertakings_terms as u')
            ->leftJoin('national_application_undertakings as nu', function ($join) use ($id) {
                $join->on('nu.undertaking_id', '=', 'u.id')
                    ->where(function ($q) use ($id) {
                        $q->where('nu.national_permission_application_id', $id)
                            ->orWhereNull('nu.national_permission_application_id');
                    });
            })
            ->select('u.id', 'u.undertaking_text', 'u.is_mandatory', 'nu.national_permission_application_id')
            ->where('u.status', 1)
            ->orderBy('u.sort_order');

        return $query->get();
    }

    public function getAlreadyAgreedOrNotUndertakings($id)
    {
        $query = DB::table('user_undertaking_acceptance as u')
            ->select('u.agree')
            ->where('u.national_permission_application_id', $id);
        return $query->first();
    }
}
