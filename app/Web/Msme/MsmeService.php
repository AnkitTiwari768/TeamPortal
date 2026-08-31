<?php 
declare(strict_types=1);
namespace App\Web\Msme;
use App\Traits\DataTable;
use App\Core\BaseService;
use App\Http\Services\CommonService;
use App\Traits\HasAttribute;
use DB;
use Carbon\Carbon;
use App\Domain\NetworkProvider\NetworkProviderStatus;


class MsmeService extends BaseService
{
	use DataTable,HasAttribute;
    protected array $columns = [
        1 => 'udyam_no',
        2 => 'mobile',
		3 => 'email',
		4 => 'entrepreneur_name',
		5 => 'enterprise_name',
		6 => 'organisation_type',
		7=>'msme_classification',
		8=>'social_category',
		9=>'st.name',
		10=>'created_at'
    ];

    public function getMsmeList()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
        $search ??= $this->escape_special_characters($search);

        $query = DB::table('team_msme_schemes as ms')
        ->select('ms.id', 'ms.udyam_no', 'ms.mobile', 'ms.email', 'ms.entrepreneur_name', 'ms.enterprise_name','ms.organisation_type','ms.msme_classification','ms.social_category','st.name as state_name','ms.created_at')
		->leftJoin('states as st', 'st.id', '=', 'ms.state_id');
		
		if (!empty($filters['from_date'])) {
			$from = \Carbon\Carbon::createFromFormat('d-m-Y', $filters['from_date'])->format('Y-m-d');
		}

		if (!empty($filters['to_date'])) {
			$to = \Carbon\Carbon::createFromFormat('d-m-Y', $filters['to_date'])->format('Y-m-d');
		}


		if (!empty($from) && !empty($to)) {
			$query->whereBetween('ms.created_at', [$from, $to]);
		} elseif (!empty($from)) {
			$query->whereDate('ms.created_at', '>=', $from);
		} elseif (!empty($to)) {
			$query->whereDate('ms.created_at', '<=', $to);
		}

		
		if (isset($filters['ondc_transaction_type_id']) && !empty($filters['ondc_transaction_type_id'])) {
            $query->where('ondc_transaction_type_id', $filters['ondc_transaction_type_id']);
        }
		
		if (!empty($filters['product_category_id']) && is_array($filters['product_category_id'])) {
			foreach ($filters['product_category_id'] as $categoryId) {
				$query->orWhereJsonContains('product_category_id', $categoryId);
			}
		}

        if ($search) 
        {
            $query->where(function($query) use ($search) {
                $query->where('ms.udyam_no','like', "%$search%")
                    ->orWhere('ms.mobile','like', "%$search%")
					->orWhere('ms.email','like', "%$search%")
					->orWhere('ms.entrepreneur_name','like', "%$search%")
					->orWhere('ms.enterprise_name','like', "%$search%")
                    ->orWhere('ms.organisation_type','like', "%$search%")
					->orWhere('ms.social_category','like', "%$search%")
					->orWhere('st.state_name','like', "%$search%")
					->orWhere('ms.created_at','like', "%$search%");
            });
        }

        $query->orderBy($order, $dir); 
        
        if ($page) 
        {
            return $this->getDataTableResult(
                MsmeResource::collection($query->paginate($limit))
            );
        }

        return MsmeResource::collection($query->get());
    }
	
	
	// public function getMsmeDetails($id)
    // {
    //      $detail= DB::table('team_msme_schemes as ms')
	// 	->leftJoin('states as s', 's.id', '=', 'ms.state_id')
	// 	->leftJoin('locations as d', 'd.id', '=', 'ms.district_id')
	// 	->leftJoin('attribute_values as bs', 'bs.id', '=', 'ms.current_state_business_id')
	// 	->leftJoin('attribute_values as ott', 'ott.id', '=', 'ms.ondc_transaction_type_id')
	// 	->select(
	// 		'ms.*',
	// 		's.name as state_name',
	// 		'd.name as district_name',
	// 		'bs.attribute_value as business_state_name',
	// 		'ott.attribute_value as transaction_type_name',
	// 		DB::raw("DATE_FORMAT(ms.created_at, '%d-%m-%Y') as created_at"),
	// 		DB::raw("DATE_FORMAT(ms.incorporation_date, '%d-%m-%Y') as incorporation_date")
	// 	)
	// 	->where('ms.id', $id)
	// 	->first();
		

	// 	// Step 2: Decode JSON product category IDs
	// 	$productCategoryIds = json_decode($detail->product_category_id ?? '[]', true);

	// 	// Step 3: Fetch related product categories with sub_domains
	// 	$productCategories = DB::table('sub_domains as pc')
	// 		->whereIn('pc.id', $productCategoryIds)
	// 		->pluck('pc.name') // Get array of names
	// 		->implode(', ');   // Convert to comma-separated string

	// 	$detail->product_categories = $productCategories;

	// 	return $detail;
    // }
	
    public function getMsmeDetails($id)
    {
         $detail= DB::table('team_msme_schemes as ms')
		->leftJoin('states as s', 's.id', '=', 'ms.state_id')
		->leftJoin('locations as d', 'd.id', '=', 'ms.district_id')
		->leftJoin('attribute_values as bs', 'bs.id', '=', 'ms.current_state_business_id')
		->leftJoin('attribute_values as ott', 'ott.id', '=', 'ms.ondc_transaction_type_id')
		->leftJoin('industrial_associations as ia', 'ia.user_id', '=', 'ms.created_by')
		->leftJoin('team_snp_scheme as creator_snp', 'creator_snp.user_id', '=', 'ms.created_by')
		->select(
			'ms.*',
			's.name as state_name',
			'd.name as district_name',
			'bs.attribute_value as business_state_name',
			'ott.attribute_value as transaction_type_name',
			'ia.organization_name as association_name',
			'creator_snp.organization_name as creator_snp_name',
            'ms.is_user_sso',
			DB::raw("DATE_FORMAT(ms.created_at, '%d-%m-%Y') as created_at"),
			DB::raw("DATE_FORMAT(ms.incorporation_date, '%d-%m-%Y') as incorporation_date")
		)
		->where('ms.id', $id)
		->first();
		

		// Step 2: Decode JSON product category IDs
		$productCategoryIds = json_decode($detail->product_category_id ?? '[]', true);

		// Step 3: Fetch related product categories with sub_domains
		$productCategories = DB::table('sub_domains as pc')
			->whereIn('pc.id', $productCategoryIds)
			->pluck('pc.name') // Get array of names
			->implode(', ');   // Convert to comma-separated string

		$detail->product_categories = $productCategories;
		$detail->source_of_registration = $detail->association_name ?? $detail->creator_snp_name ?? 'Self';

		return $detail;
    }
	
	public function getDropdownList()
    {
        $commonService = new CommonService();
        return [
		 'ondc_types'=>$this->listOf('types-of-transactions-preferred-on-ondc'),
		 'sub_domains' => $commonService->getDropdownNewList('sub_domains','status','ASC','name',array('id','name')),
        ];
    }
	
	protected array $snpColumns = [
        1 => 'np.np_team_id',
        2 => 'ss.snp_name',
        3 => 'state_name',
        4 => 'domain_names',
        5 => 'ss.organization_name',
    ];

    /*public function getSnpList()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $userId = authId();
        $msme = DB::table('team_msme_schemes')
            ->where('user_id', $userId)
            ->first();
            //dd($msme);

        $stateIds = $msme->state_id ? [$msme->state_id] : [];

        $txnTypeIds = $msme->ondc_transaction_type_id ? [$msme->ondc_transaction_type_id] : [];

        $subDomainIds = $msme->product_category_id ? json_decode($msme->product_category_id, true) : [];
        // dd($subDomainIds);

        $ondcDomainMappingIds = DB::table('sub_domains')->whereIn('id', $subDomainIds)->pluck('ondc_domain_id')->toArray();
        

        $search ??= $this->escape_special_characters($search);

        $query = DB::table('team_snp_scheme as ss')
            ->select(
                'ss.id',
                'np.np_team_id as snp_id',
                'ss.snp_name as snp_name',
                'ss.organization_name as organization_name',
                'ss.created_at',
                DB::raw('GROUP_CONCAT(DISTINCT sd.name ORDER BY sd.name SEPARATOR ", ") as domain_names'),
                DB::raw('GROUP_CONCAT(DISTINCT st.name ORDER BY st.name SEPARATOR ", ") as state_name'),
            )
            
            ->join('network_providers as np', function ($join) {
                $join->on('np.id', '=', 'ss.network_provider_id')
                    ->where('np.status', NetworkProviderStatus::APPROVE->value);
            })
            
            ->leftJoin('states as st', function ($join) {
                $join->whereRaw(
                    'JSON_CONTAINS(ss.state_id, JSON_QUOTE(st.id))'
                );
            })

            ->leftJoin('sub_domains as sd', function ($join) {
                $join->whereRaw(
                    'JSON_CONTAINS(ss.sub_domain, JSON_QUOTE(sd.ondc_domain_id))'
                );
            })
            
            ->groupBy(
                'ss.id',
                'np.np_team_id',
                'ss.snp_name',
                'ss.organization_name',
                'ss.created_at'
            )
            
            ->where(function ($q) use ($stateIds) {
                foreach ($stateIds as $stateId) {
                    $q->orWhereRaw(
                        'JSON_CONTAINS(ss.state_id, ?)',
                        ['"' . (string)$stateId . '"']
                    );
                }
            })


            ->where(function ($q) use ($txnTypeIds) {
                foreach ($txnTypeIds as $txnTypeId) {
                    $q->orWhereRaw(
                        'JSON_CONTAINS(ss.transaction_type, ?)',
                        ['"' . (string)$txnTypeId . '"']
                    );
                }
            })


           
            ->where(function ($q) use ($ondcDomainMappingIds) {
                foreach ($ondcDomainMappingIds as $subDomainId) {
                    $q->orWhereRaw(
                        'JSON_CONTAINS(ss.sub_domain, ?)',
                        ['"' . $subDomainId . '"']
                    );
                }
            })
                ;
            
            if (isset($filters['transaction_type']) && !empty($filters['transaction_type'])) {
                $query->whereRaw(
                    'JSON_CONTAINS(ss.transaction_type, ?)',
                    ['"' . $filters['transaction_type'] . '"']
                );
            }
            if (!empty($filters['sub_domain']) && is_array($filters['sub_domain'])) {
                foreach ($filters['sub_domain'] as $categoryId) {
                    $query->orWhereRaw(
                        'JSON_CONTAINS(ss.sub_domain, ?)',
                        ['"' . $categoryId . '"']
                    );
                }
            }

            if ($search) {
                $query->havingRaw(
                    '(
                        np.np_team_id LIKE ?
                        OR ss.snp_name LIKE ?
                        OR ss.organization_name LIKE ?
                        OR state_name LIKE ?
                        OR domain_names LIKE ?
                    )',
                    [
                        "%$search%",
                        "%$search%",
                        "%$search%",
                        "%$search%",
                        "%$search%",
                    ]
                );
            }

            if (!empty($selectedIds)) {
                $query->whereIn('ss.id', $selectedIds);
            }
            if (!empty($order) && isset($this->snpColumns[$order])) {

                $column = $this->snpColumns[$order];

                if (in_array($column, ['state_name', 'domain_names'])) {
                    $query->orderBy(DB::raw($column), $dir);
                } else {
                    $query->orderBy($column, $dir);
                }

            } else {
                $query->orderBy('ss.created_at', 'desc');
            }
            if ($page) 
            {
                return $this->getDataTableResult(
                    SnpListResource::collection($query->paginate($limit))
                );
            }
            
            return SnpListResource::collection($query->get());
    }*/


    public function getSnpList()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $userId = authId();

        $msme = DB::table('team_msme_schemes')
            ->where('user_id', $userId)
            ->first();

        if (!$msme) {
            return $page
                ? $this->getDataTableResult(SnpListResource::collection(collect()))
                : SnpListResource::collection(collect());
        }

        $stateId = (string) $msme->state_id;
        $transactionType = (string) $msme->ondc_transaction_type_id;
        $subDomains = json_decode($msme->product_category_id, true) ?? [];

        $nationalId = '5bc85de0-0292-11f1-922a-00155d022d06';

        $search ??= $this->escape_special_characters($search);

        $query = DB::table('team_snp_scheme as ss')
            ->join('network_providers as np', 'np.id', '=', 'ss.network_provider_id')

            ->leftJoin('states as st', function ($join) {
                $join->whereRaw('JSON_CONTAINS(ss.state_id, JSON_QUOTE(st.id))');
            })

            ->leftJoin('sub_domains as sd', function ($join) {
                $join->whereRaw('JSON_CONTAINS(ss.sub_domain, JSON_QUOTE(sd.ondc_domain_id))');
            })

            ->select(
                'ss.id',
                'np.np_team_id as snp_id',
                'ss.snp_name',
                'ss.organization_name',
                'ss.created_at',
                DB::raw('GROUP_CONCAT(DISTINCT st.name ORDER BY st.name SEPARATOR ", ") as state_name'),
                DB::raw('GROUP_CONCAT(DISTINCT sd.name ORDER BY sd.name SEPARATOR ", ") as domain_names')
            )

            ->where(function ($main) use ($stateId, $transactionType, $subDomains, $nationalId) {

                // National SNP
                $main->where(function ($q) use ($transactionType, $subDomains, $nationalId) {

                    $q->whereJsonContains('ss.transaction_type', $transactionType);

                    if (!empty($subDomains)) {
                        $q->where(function ($sub) use ($subDomains) {
                            foreach ($subDomains as $domain) {
                                $sub->orWhereJsonContains('ss.sub_domain', $domain);
                            }
                        });
                    }

                    $q->whereJsonContains('ss.state_id', $nationalId);
                });

                // State SNP
                $main->orWhere(function ($q) use ($transactionType, $subDomains, $stateId) {

                    $q->whereJsonContains('ss.transaction_type', $transactionType);

                    if (!empty($subDomains)) {
                        $q->where(function ($sub) use ($subDomains) {
                            foreach ($subDomains as $domain) {
                                $sub->orWhereJsonContains('ss.sub_domain', $domain);
                            }
                        });
                    }

                    $q->whereJsonContains('ss.state_id', $stateId);
                });
            })

            ->where(function ($q) {
                $q->where('ss.status', 2)
                ->orWhere('np.status', NetworkProviderStatus::APPROVE->value);
            })

            ->groupBy(
                'ss.id',
                'np.np_team_id',
                'ss.snp_name',
                'ss.organization_name',
                'ss.created_at'
            );

        if (!empty($filters['transaction_type'])) {
            $query->whereJsonContains('ss.transaction_type', $filters['transaction_type']);
        }

        if (!empty($filters['sub_domain'])) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters['sub_domain'] as $domain) {
                    $q->orWhereJsonContains('ss.sub_domain', $domain);
                }
            });
        }

        if ($search) {
            $query->havingRaw(
                '(np.np_team_id LIKE ?
                OR ss.snp_name LIKE ?
                OR ss.organization_name LIKE ?
                OR state_name LIKE ?
                OR domain_names LIKE ?)',
                [
                    "%{$search}%",
                    "%{$search}%",
                    "%{$search}%",
                    "%{$search}%",
                    "%{$search}%"
                ]
            );
        }

        if (!empty($order) && isset($this->snpColumns[$order])) {

            $column = $this->snpColumns[$order];

            if (in_array($column, ['state_name', 'domain_names'])) {
                $query->orderBy(DB::raw($column), $dir);
            } else {
                $query->orderBy($column, $dir);
            }

        } else {
            $query->orderByDesc('ss.created_at');
        }

        if ($page) {
            return $this->getDataTableResult(
                SnpListResource::collection($query->paginate($limit))
            );
        }

        return SnpListResource::collection($query->get());
    }


    public function relevantSnpViewDetails(string $id){
        $data = DB::table('team_snp_scheme as ss')
            ->select(
                'ss.*',
                'np.*',
                'np.np_team_id as snp_id',
                DB::raw('GROUP_CONCAT(DISTINCT sd.name ORDER BY sd.name SEPARATOR ", ") as domain_names'),
                DB::raw('GROUP_CONCAT(DISTINCT st.name ORDER BY st.name SEPARATOR ", ") as state_name')
            )
            ->join('network_providers as np', function ($join) {
                $join->on('np.id', '=', 'ss.network_provider_id')
                    ->where('np.status', NetworkProviderStatus::APPROVE->value);
            })
            ->leftJoin('states as st', function ($join) {
                $join->whereRaw(
                    'JSON_CONTAINS(ss.state_id, JSON_QUOTE(st.id))'
                );
            })
            ->leftJoin('sub_domains as sd', function ($join) {
                $join->whereRaw(
                    'JSON_CONTAINS(ss.sub_domain, JSON_QUOTE(sd.ondc_domain_id))'
                );
            })
            ->where('ss.id', $id)
            ->groupBy(
                'ss.id',
                'np.id',
                'np.np_team_id'
            )
            ->first();

            if (!$data) {
                return null;
            }

            $data->contact_details           = json_decode($data->contact_details ?? '{}', true);
            $data->authorized_person_details = json_decode($data->authorized_person_details ?? '[]', true);
            $data->configuration_details     = json_decode($data->configuration_details ?? '{}', true);
            $data->bank_details              = json_decode($data->bank_details ?? '{}', true);
            $data->role_selection_details    = json_decode($data->role_selection_details ?? '[]', true);
            $data->value_proposition_details = json_decode($data->value_proposition_details ?? '{}', true);
            $data->commercial_model_details  = json_decode($data->commercial_model_details ?? '{}', true);

            return $data;
    }







}