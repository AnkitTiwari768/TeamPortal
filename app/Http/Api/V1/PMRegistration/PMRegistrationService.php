<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\PMRegistration;

use App\Traits\DataTable;
use App\Http\Services\CommonService;
use App\Http\Api\V1\User\User;
use App\Contracts\GrantType;
use Illuminate\Support\Facades\DB;
use Mail;	
use App\Traits\HasAttribute;
use Illuminate\Support\Facades\Http;
use DateTime;
use Illuminate\Support\Str;



class PMRegistrationService 
{
    use DataTable, HasAttribute;



	protected array $columns = [
		1 => 'p.owner_name',
		2 => 'p.store_name',
		3 => 'p.email',
		4 => 'p.mobile',
		5 => 'p.created_at',
		6 => 'av.type_of_business',
	];

	public function getPMVUsersData($status = NULL)
	{
		[$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

		

		$search ??= $this->escape_special_characters($search);

		$query = DB::table('pm_registrations as p')
			->leftJoin('attribute_values as av', 'av.id', '=', 'p.type_of_business')
            ->leftJoin('pm_vishwakarma_categories as cat', 'p.type_of_business', '=', 'cat.id')
			->select(
				'p.*',
				'av.attribute_value as type_of_business',
                'cat.name as category_name',
			);

		if ($search) {
			$query->where(function ($q) use ($search) {
				$q->where('p.owner_name', 'like', "%$search%")
				->orWhere('p.store_name', 'like', "%$search%")
				->orWhere('p.mobile', 'like', "%$search%")
				->orWhere('p.email', 'like', "%$search%")
				->orWhere('av.attribute_value', 'like', "%$search%");
			});
		}

        if (request()->has('is_bulk') && request('is_bulk') !== '') {
            $query->where('p.is_bulk', request('is_bulk'));
        }

		$order = $this->columns[$order] ?? 'p.created_at';
		$query->orderBy($order, $dir);

		if ($page) {
			return $this->getDataTableResult(
				PMRegistrationResource::collection($query->paginate($limit))
			);
		}

		return PMRegistrationResource::collection($query->get());
	}


    public function getDocuments(array $type): array
    {
        $query = DB::table('document_categories as dc')
            ->select(
                'dc.id',
                'dc.name as document_category_name',
                'dc.slug as document_category_slug',
                'dc.file_size',
                'dc.informations',
                'dc.rules',
            )
            ->whereIn('dc.slug', $type)
            ->orderBy('dc.sort_order', 'asc');

        return $query->get()->toArray();
    }

    public function store(array $payload): bool
    {
        return DB::transaction(function () use ($payload) {

            $uuid = (string) Str::uuid();
            $pmData = [
                'id' => $uuid,
                'owner_name' => $payload['owner_name'],
                'store_name' => $payload['store_name'],
                'mobile' => $payload['mobile'],
                'email' => $payload['email'],
                'pan_no' => $payload['pan_no'],
                'pin_code' => $payload['pin_code'],
                'address' => $payload['address'],
                'type_of_business' => $payload['type_of_business'],
                'other' => $payload['other'] ?? null,
                'remark' => $payload['remark'] ?? null,

                'cancelled_cheque_file' => $payload['cancelled-cheque'] ?? null,
                'cancelled_cheque_file_id' => $payload['cancelled-cheque-id'] ?? null,
                'vishwakarma_form_copy_file' => $payload['vishwakarma-form-copy'] ?? null,
                'vishwakarma_form_copy_file_id' => $payload['vishwakarma-form-copy-id'] ?? null,

                'status' => 1,
                'created_at' => currentDateTime(),
                'updated_at' => currentDateTime(),
            ];

            DB::table('pm_registrations')->insert($pmData);

            $userData = [
                'id' => $uuid,
                'username' => $pmData['email'],
                'first_name' => $pmData['owner_name'],
                'mobile' => $pmData['mobile'],
                'email' => $pmData['email'],
                'status' => 0,
                'created_at' => currentDateTime()
            ];

            DB::table('users')->insert($userData);

            return true;
        });
    }

    public function getDropdownList()
    {
        $commonService = new CommonService();

        return [
            'states' => $commonService->getDropdownNewList('states','status','ASC','name',['id','name']),
            'business_type' => $this->orderByAlpha('type_of_business'),
            'ondc_types' => $this->listOf('types-of-transactions-preferred-on-ondc'),
            'status' => $commonService->getStatus()
        ];
    }


    public function orderByAlpha(string $type): array
    {
        $list = $this->listOf($type);
        asort($list);
        return $list;
    }

    public function productCategories(): array
    {
        return DB::table('pm_vishwakarma_categories')->orderBy('name', 'asc')->pluck('name', 'id')->toArray();
    }
	public function getPMVUserDatail($id)
	{
		$data = DB::table('pm_registrations as p')
			->leftJoin('attribute_values as av', 'av.id', '=', 'p.type_of_business')
			->leftJoin('file_uploads as fu', function ($join) {
                $join->on('fu.id', '=', 'p.cancelled_cheque_file_id')
                    ->orOn('fu.id', '=', 'p.vishwakarma_form_copy_file_id');
            })
			->select(
				'p.*',
				'av.attribute_value as type_of_business_name',
                 DB::raw("
                    MAX(CASE 
                        WHEN fu.id = p.cancelled_cheque_file_id 
                        THEN fu.file_name 
                    END) as cancelled_cheque_file_name
                "),

                DB::raw("
                    MAX(CASE 
                        WHEN fu.id = p.vishwakarma_form_copy_file_id 
                        THEN fu.file_name 
                    END) as vishwakarma_form_file_name
                ")
            )
			->where('p.id', $id)
			->first();

		if (!$data) {
			return [];
		}

		return $data;
	}


    
    /**
     * Get total registered PM Vishwakarma users count.
     *
     * @return array
     */
    public function getRegistrationCountData(): array
    {
      try {
          
            // Counts for other tables
            $pmRegistrationsCount = DB::table('pm_registrations')->count();
            $teamMsmeSchemesCount = DB::table('team_msme_schemes')->whereNotNull('major_activity')->where('major_activity', '!=', '')->count();
            $teamClaimsSubmittedCount = DB::table('claims')->count();
        
          
            // Return structured response
            return [
                'total_pm_registrations' => $pmRegistrationsCount,
                'total_msme_schemes' => $teamMsmeSchemesCount, 
                'total_claims_submitted' => $teamClaimsSubmittedCount,
                'total_lsp' => $this->getTotalCountLsp(),
                'total_bnp' => $this->getTotalCoutbnp(),  
                'total_snp' => $this->getTotaLRegisteredSmp()          
           ];

        } catch (\Exception $e) {

            return [
                'status' => false,
                'message' => 'FAILED TO RETRIEVE DATA',
                'data' => null,
                'error' => $e->getMessage() // optional for debugging
            ];
        }
    }

    public function getTotalCountLsp()
    {
        $roleId=$this->getRoleIdBySnpSlug('lsp');        
         return DB::table('network_providers')->whereJsonContains('roles', $roleId)->where('status', 2)->count();
    }
    public function getTotalCoutbnp()
    {
        $roleId=$this->getRoleIdBySnpSlug('bnp');        
       return DB::table('network_providers')->whereJsonContains('roles', $roleId)->where('status', 2)->count(); 
    }

    public function getTotaLRegisteredSmp()
    {
        $roleId=$this->getRoleIdBySnpSlug('snp');
        $totalSnp = DB::table('team_snp_scheme')->where('status', 2)->count();
        
      $totalNP = DB::table('network_providers')
            ->whereJsonContains('roles', $roleId)
            ->where('status', 2)
            ->whereNotIn('id', function ($query) {
                $query->select('network_provider_id')
                    ->from('team_snp_scheme');
            })->count();
        $totalNP = $totalNP;
        $total = $totalSnp + $totalNP;
        return $total;
    }

    public function getRoleIdBySnpSlug($slug){
		return \DB::table('roles')->where('slug', $slug)->value('id');
	}
}