<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Registration;

use App\Traits\DataTable;
use App\Domain\NetworkProvider\NetworkProviderStatus;
use App\Http\Services\CommonService;
use App\Http\Api\V1\User\User;
use App\Http\Api\V1\Registration\Registration;
use App\Contracts\GrantType;
use Illuminate\Support\Facades\DB;
use Mail;
use App\Traits\HasAttribute;
use Illuminate\Support\Facades\Http;
use DateTime;


class RegistrationService
{
	use DataTable, HasAttribute;


	public function store(array $payload, ?string $roleId = null)
	{
		return DB::transaction(function () use ($payload) {
			$uuid = uuid();
			$userId = uuid();
			$msmeData = [
				'id' => $uuid,
				'user_id' => $userId,
				//'team_id' => 'TEAM' . rand(10000, 99999),
				'team_id' =>  getNextTeamId() ?: null,
				'entrepreneur_name' => $payload['entrepreneur_name'],
				'enterprise_name' => $payload['enterprise_name'],
				'udyam_no' => $payload['udyam_no'] ?? null,
				'mobile' => $payload['mobile'],
				'email' => $payload['email'],
				'product_category_id' => json_encode($payload['product_category_id']),
				'product_details' => $payload['product_details'],
				'state_id' => $payload['state_id'],
				'ondc_transaction_type_id' => $payload['ondc_transaction_type_id'],
				'select_snp' => $payload['select_snp'],
				'status' => 0,
				'agree' => 1,
				'is_user_sso' => $payload['is_user_sso'] ?? null,
				'sso_type' => $payload['sso_type'] ?? null,
				'is_msme_registration' => 1,
				'created_at' => currentDateTime(),
				'updated_at' => currentDateTime()
			];

			DB::table('team_msme_schemes')->insert($msmeData);

			$user_mapping = [
				'id' => $userId,
				'username' => $payload['email'],
				'first_name' => $payload['entrepreneur_name'],
				'mobile' => $payload['mobile'],
				'email' => $payload['email'],
				//'status' => 0,
				'status' => ($payload['select_snp'] == 1) ? 1 : 0,
				'is_msme' => 1,
				'is_user_sso' => $payload['is_user_sso'] ?? null,
				'sso_type' => $payload['sso_type'] ?? null,
				'created_at' => currentDateTime()
			];

			DB::table('users')->insert($user_mapping);

			if ($payload['select_snp'] == 1) {
				$snp_mapping = [
					'id' => uuid(),
					'snp_id' => $payload['snp_id'],
					'msme_id' => $uuid,
					'created_at' => currentDateTime()
				];

				DB::table('team_snpmsme_mapping')->insert($snp_mapping);
			}

			return $payload['select_snp'];
		});
	}


	public function getDropdownList()
	{
		$commonService = new CommonService();
		return [
			'states' => $commonService->getDropdownNewList('states', 'status', 'ASC', 'name', array('id', 'name')),
			'current_state_business' => $this->listOf('current-state-of-your-business'),
			'ondc_types' => $this->listOf('types-of-transactions-preferred-on-ondc'),
			'sub_domains' => $commonService->getDropdownNewList('sub_domains', 'status', 'ASC', 'name', array('id', 'name')),

			'yesno' => $commonService->getYesNoStatus(),
			'status' => $commonService->getStatus()
		];
	}

	// public function getUdyamDetails($udyam_no, $mobile)
	// {
	// 	//$response = Http::get("https://udyogaadhaar.gov.in/sv/Udyam_NsicB2BService.svc/GetUdyam/$udyam_no,$mobile,b2bmrt-VGVzdEBoeXc2MA==");
		
	// 	$udyam_token=config('constant.UDYAM_TOKEN');		
	// 	$response = Http::get("https://udyogaadhaar.gov.in/sv/Udyam_NsicB2BService.svc/GetUdyam/$udyam_no,$mobile,$udyam_token");

	// 	if ($response->successful()) {
	// 		$xml = $response->body();
	// 		$xmlObject = simplexml_load_string($xml);
	// 		$json = json_encode($xmlObject);
	// 		//echo "<pre/>";print_r(json_decode($json, true));exit;
	// 		return json_decode($json, true);
	// 	} else {
	// 		return response()->json([
	// 			'error' => 'Failed to retrieve data',
	// 			'status' => $response->status()
	// 		]);
	// 	}
	// }

	public function getUdyamDetails($udyam_no, $mobile)
	{
		$udyam_token = config('constant.UDYAM_TOKEN');

		$response = Http::withOptions([
			'verify' => false,
			'curl' => [
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
			]
		])->get("https://udyogaadhaar.gov.in/sv/Udyam_NsicB2BService.svc/GetUdyam/$udyam_no,$mobile,$udyam_token");

		if ($response->successful()) {
			$xml = $response->body();
			$xmlObject = simplexml_load_string($xml);
			$json = json_encode($xmlObject);
			//echo "<pre/>";print_r(json_decode($json, true));exit;
			return json_decode($json, true);
		} else {
			return response()->json([
				'error' => 'Failed to retrieve data',
				'status' => $response->status()
			]);
		}
	}

public function getSelectSnpDetails($stateId, $transactionType, $subDomain)
{
    $stateId = (string) $stateId;
    $transactionType = (string) $transactionType;
    $subDomain = array_map('strval', (array) $subDomain);

    $nationalId = '5bc85de0-0292-11f0-922a-00155d022d06';

    $stateName = DB::table('states')
        ->where('id', $stateId)
        ->value('name');

    $domainNames = [];

    if (!empty($subDomain)) {
        $domainNames = DB::table('sub_domains')
            ->whereIn('id', $subDomain)
            ->pluck('name')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    $transactionTypeName = match ($transactionType) {
        '36523ead-533d-11f0-81dc-00155d022d06' => 'Business to Consumer (B2C)',
        '9e7e1e8b-5578-11f0-81dc-00155d022d06' => 'Business to Business (B2B)',
        'b44fb78b-d49e-11f0-922a-00155d022d06' => 'Both',
        default => $transactionType,
    };

    $query = DB::table('team_snp_scheme as tss')
        ->leftJoin(
            'network_providers as np',
            'tss.network_provider_id',
            '=',
            'np.id'
        )
        ->whereNotNull('np.role_selection_details')
        ->where(
            'np.status',
            NetworkProviderStatus::APPROVE->value
        )
        ->where(function ($query) use (
            $domainNames,
            $transactionTypeName,
            $stateName
        ) {
            foreach ($domainNames as $domainName) {
                $query->orWhere(function ($q) use (
                    $domainName,
                    $transactionTypeName,
                    $stateName
                ) {
                    if ($transactionTypeName === 'Business to Consumer (B2C)') {
                        $q->where(function ($role) use (
                            $domainName,
                            $stateName
                        ) {
                            $role->whereRaw(
                                "JSON_CONTAINS(np.role_selection_details, ?)",
                                [
                                    json_encode([
                                        'domain_name' => $domainName,
                                        'transaction_type_name' => 'Business to Consumer (B2C)',
                                        'serviceability_name' => $stateName,
                                    ]),
                                ]
                            )->orWhereRaw(
                                "JSON_CONTAINS(np.role_selection_details, ?)",
                                [
                                    json_encode([
                                        'domain_name' => $domainName,
                                        'transaction_type_name' => 'Business to Consumer (B2C)',
                                        'serviceability_name' => 'National',
                                    ]),
                                ]
                            )->orWhereRaw(
                                "JSON_CONTAINS(np.role_selection_details, ?)",
                                [
                                    json_encode([
                                        'domain_name' => $domainName,
                                        'transaction_type_name' => 'Both',
                                        'serviceability_name' => $stateName,
                                    ]),
                                ]
                            )->orWhereRaw(
                                "JSON_CONTAINS(np.role_selection_details, ?)",
                                [
                                    json_encode([
                                        'domain_name' => $domainName,
                                        'transaction_type_name' => 'Both',
                                        'serviceability_name' => 'National',
                                    ]),
                                ]
                            );
                        });
                    } elseif ($transactionTypeName === 'Business to Business (B2B)') {
                        $q->where(function ($role) use (
                            $domainName,
                            $stateName
                        ) {
                            $role->whereRaw(
                                "JSON_CONTAINS(np.role_selection_details, ?)",
                                [
                                    json_encode([
                                        'domain_name' => $domainName,
                                        'transaction_type_name' => 'Business to Business (B2B)',
                                        'serviceability_name' => $stateName,
                                    ]),
                                ]
                            )->orWhereRaw(
                                "JSON_CONTAINS(np.role_selection_details, ?)",
                                [
                                    json_encode([
                                        'domain_name' => $domainName,
                                        'transaction_type_name' => 'Business to Business (B2B)',
                                        'serviceability_name' => 'National',
                                    ]),
                                ]
                            )->orWhereRaw(
                                "JSON_CONTAINS(np.role_selection_details, ?)",
                                [
                                    json_encode([
                                        'domain_name' => $domainName,
                                        'transaction_type_name' => 'Both',
                                        'serviceability_name' => $stateName,
                                    ]),
                                ]
                            )->orWhereRaw(
                                "JSON_CONTAINS(np.role_selection_details, ?)",
                                [
                                    json_encode([
                                        'domain_name' => $domainName,
                                        'transaction_type_name' => 'Both',
                                        'serviceability_name' => 'National',
                                    ]),
                                ]
                            );
                        });
                    } elseif ($transactionTypeName === 'Both') {
                        $q->where(function ($role) use (
                            $domainName,
                            $stateName
                        ) {
                            $role->whereRaw(
                                "JSON_CONTAINS(np.role_selection_details, ?)",
                                [
                                    json_encode([
                                        'domain_name' => $domainName,
                                        'transaction_type_name' => 'Both',
                                        'serviceability_name' => $stateName,
                                    ]),
                                ]
                            )->orWhereRaw(
                                "JSON_CONTAINS(np.role_selection_details, ?)",
                                [
                                    json_encode([
                                        'domain_name' => $domainName,
                                        'transaction_type_name' => 'Both',
                                        'serviceability_name' => 'National',
                                    ]),
                                ]
                            );
                        });
                    }
                });
            }
        })
        ->select(
            'tss.snp_id',
            'tss.organization_id as snp_organization_id',
            'tss.organization_name as snp_organization_name',
            'tss.short_description as snp_short_description',
            'tss.sub_domain as snp_domain',
            'tss.id',
            'np.commercial_model_details',
            'np.np_team_id',
            'np.organization_name',
            'np.contact_details',
            'np.role_selection_details',
            'np.value_proposition_details'
        )
        ->distinct();

    $data = $query->get();

    $result = collect($data)
        ->map(function ($item) {
            $contactDetails = $item->contact_details
                ? json_decode($item->contact_details, true)
                : [];

            $roleDetails = $item->role_selection_details
                ? json_decode($item->role_selection_details, true)
                : [];

            $valueDetails = $item->value_proposition_details
                ? json_decode($item->value_proposition_details, true)
                : [];

            $commercialDetails = $item->commercial_model_details
                ? json_decode($item->commercial_model_details, true)
                : [];

            $productCategories = collect($roleDetails)
                ->pluck('domain_name')
                ->filter()
                ->unique()
                ->values()
                ->implode(',');

            if (!$productCategories && $item->snp_domain) {
                $domainIds = json_decode($item->snp_domain, true);

                if (is_array($domainIds) && !empty($domainIds)) {
                    $productCategories = DB::table('sub_domains')
                        ->whereIn('id', $domainIds)
                        ->pluck('name')
                        ->implode(',');
                }
            }

            return [
                'id' => $item->id ?? null,
                'snp_id' => $item->np_team_id ?? $item->snp_id,
                'organisation_name' => $item->organization_name
                    ?? $item->snp_organization_name,
                'website' => $contactDetails['website'] ?? null,
                'product_category' => $productCategories,
                'short_description' => $valueDetails['short_description']
                    ?? $item->snp_short_description,
                'commercial_model' => $commercialDetails ?: null,
                'languages_supported' => $valueDetails['language_supported_name']
                    ?? null,
            ];
        })
        ->values()
        ->toArray();

    return $result;
}


// 	public function getSelectSnpDetails($stateId, $transactionType, $subDomain){
		
// 		// ✅ UUID safety (VERY IMPORTANT)
// 		$stateId = (string) $stateId;
// 		$transactionType = (string) $transactionType;
// 		$subDomain = array_map('strval', (array) $subDomain);

// 		$nationalId = '5bc85de0-0292-11f1-922a-00155d022d06';

// 		$query = DB::table('team_snp_scheme as tss')
//         ->leftJoin('network_providers as np', 'tss.network_provider_id', '=', 'np.id')

//         // ✅ MAIN LOGIC (National OR State)
//         ->where(function ($mainQuery) use ($transactionType, $subDomain, $stateId, $nationalId) {

//             // 🔹 Case 1: NATIONAL SNP
//             $mainQuery->where(function ($q) use ($transactionType, $subDomain, $nationalId, $stateId) {

//                 // MUST: transaction_type
//                 $q->where(function($sq) use ($transactionType) {
// 					$sq
// 						->whereJsonContains('tss.transaction_type', $transactionType)
// 						->orWhereJsonContains('tss.transaction_type', 'b44fb78b-d49e-11f0-922a-00155d022d06');
// 				}); // Both

//                 // MUST: sub_domain (ANY match)
//                 if (!empty($subDomain)) {
//                     $q->where(function ($sub) use ($subDomain) {
//                         foreach ($subDomain as $sd) {
//                             $sub->orWhereJsonContains('tss.sub_domain', $sd);
//                         }
//                     });
//                 }

//                 // ONLY NATIONAL
//                 $q->where(function($sq) use ($nationalId, $stateId) {
// 					$sq
// 						->whereJsonContains('tss.state_id', $stateId)
// 						->orWhereJsonContains('tss.state_id', $nationalId);
// 				}); // Both

// 				// $q->whereJsonContains('tss.state_id', $nationalId);
//             });

//             // 🔹 Case 2: STATE SNP
//             // $mainQuery->orWhere(function ($q) use ($transactionType, $subDomain, $stateId) {

//             //     // MUST: transaction_type
//             //     $q->whereJsonContains('tss.transaction_type', $transactionType);

//             //     // MUST: sub_domain (ANY match)
//             //     if (!empty($subDomain)) {
//             //         $q->where(function ($sub) use ($subDomain) {
//             //             foreach ($subDomain as $sd) {
//             //                 $sub->whereJsonContains('tss.sub_domain', $sd);
//             //             }
//             //         });
//             //     }

//             //     // ONLY SELECTED STATE
//             //     $q->whereJsonContains('tss.state_id', $stateId);
//             // });

//         })

//         // ✅ STATUS FILTER
//         ->where(function ($query) {
//             $query
//                 ->where('tss.status', 2)
//                 ->orWhere('np.status', NetworkProviderStatus::APPROVE->value);
//         })

//         // ✅ SELECT
//         ->select(
//             'tss.snp_id',
//             'tss.organization_id as snp_organization_id',
//             'tss.organization_name as snp_organization_name',
//             'tss.short_description as snp_short_description',
//             'tss.sub_domain as snp_domain',
//             'tss.id',

//             'np.commercial_model_details',
//             'np.np_team_id',
//             'np.organization_name',
//             'np.contact_details',
//             'np.role_selection_details',
//             'np.value_proposition_details'
//         );

// 		//dd($query->toSql(), $query->getBindings());
//     $data = $query->get();
	

//     // ✅ MAPPING
//     $result = collect($data)->map(function ($item) {

//         $contactDetails    = $item->contact_details ? json_decode($item->contact_details, true) : [];
//         $roleDetails       = $item->role_selection_details ? json_decode($item->role_selection_details, true) : [];
//         $valueDetails      = $item->value_proposition_details ? json_decode($item->value_proposition_details, true) : [];
//         $commercialDetails = $item->commercial_model_details ? json_decode($item->commercial_model_details, true) : [];

//         // ✅ Product categories
//         $productCategories = collect($roleDetails)
//             ->pluck('domain_name')
//             ->filter()
//             ->unique()
//             ->values()
//             ->implode(',');

//         // ✅ fallback from sub_domain table
//         if (!$productCategories && $item->snp_domain) {
//             $productCategories = DB::table('sub_domains')
//                 ->whereIn('id', json_decode($item->snp_domain, true))
//                 ->pluck('name')
//                 ->implode(',');
//         }

//         return [
//             'id' => $item->id ?? null,
//             'snp_id' => $item->np_team_id ?? $item->snp_id,
//             'organisation_name' => $item->organization_name ?? $item->snp_organization_name,
//             'website' => $contactDetails['website'] ?? null,
//             'product_category' => $productCategories,
//             'short_description' => $valueDetails['short_description'] ?? $item->snp_short_description,
//             'commercial_model' => $commercialDetails ?: null,
//             'languages_supported' => $valueDetails['language_supported_name'] ?? null,
//         ];
//     })->toArray();

//     return $result;
// }
 

	// public function getSelectSnpDetails($stateId, $transactionType, $subDomain)
	// {
	// 	$query = DB::table('team_snp_scheme as tss')
	// 		->leftJoin('network_providers as np', 'tss.network_provider_id', '=', 'np.id')
	// 		->where(function ($query) use ($stateId) {
	// 			$query
	// 				->whereJsonContains('tss.state_id', $stateId)
	// 				->orWhereJsonContains('tss.state_id', '5bc85de0-0292-11f1-922a-00155d022d06');
	// 		})
	// 		->whereJsonContains('tss.transaction_type', $transactionType)
	// 		->select(
	// 			'tss.snp_id',
	// 			'tss.organization_id as snp_organization_id',
	// 			'tss.organization_name as snp_organization_name',
	// 			'tss.short_description as snp_short_description',
	// 			'tss.short_description as snp_short_description',
	// 			'tss.sub_domain as snp_domain',
	// 			'tss.id',
	// 			'np.commercial_model_details',
	// 			'np.np_team_id',
	// 			'np.organization_name',
	// 			'np.contact_details',
	// 			'np.role_selection_details',
	// 			'np.value_proposition_details'
	// 		);

	// 	foreach ($subDomain as $sd) {
	// 		$query->whereJsonContains('tss.sub_domain', $sd);
	// 	}

	// 	$query->where(function ($query) {
	// 		$query
	// 			->where('tss.status', 2)
	// 			->orWhere('np.status', NetworkProviderStatus::APPROVE->value);
	// 	});

	// 	$data = $query->get();

	// 	$decodedData = [];

	// 	$result = collect($data)->map(function ($item) {

	// 		$contactDetails = $item->contact_details ? json_decode($item->contact_details, true) : [];
	// 		$roleDetails = $item->role_selection_details ? json_decode($item->role_selection_details, true) : [];

	// 		$valueDetails = $item->value_proposition_details ? json_decode($item->value_proposition_details, true) : [];
	// 		$commercialDetails = $item->commercial_model_details ? json_decode($item->commercial_model_details, true) : [];

	// 		$productCategories = collect($roleDetails)->pluck('domain_name')->unique()->values()->implode(',');

	// 		if (! $productCategories) {
	// 			$productCategories = DB::table('sub_domains')->whereIn('id', json_decode($item->snp_domain, true))->pluck('name')->implode(',');
	// 		}

	// 		return [
	// 			'id' => $item->id ?? null,
	// 			'snp_id' => $item->np_team_id ? $item->np_team_id : $item->snp_id,
	// 			'organisation_name' => $item->organization_name ?? $item->snp_organization_name,
	// 			'website' => $contactDetails['website'] ?? null,
	// 			// Multiple domains possible
	// 			'product_category' => $productCategories,

	// 			'short_description' => $valueDetails['short_description'] ?? $item->snp_short_description,

	// 			'commercial_model' => $commercialDetails ?? null,

	// 			'languages_supported' => $valueDetails['language_supported_name'] ?? null,
	// 		];
	// 	})->toArray();

	// 	return $result;
	// }


	// public function getSelectSnpDetails($state_id, $transaction_type, $sub_domain)
	// {
	// 	//dd($sub_domain);
	// 	//$encodedSubDomain = json_encode($sub_domain);
	// 	//$subDomains = getSubDomainIdsByOndcDomains($encodedSubDomain);
	// 	//$subDomainIds   = !empty($subDomains) ? $subDomains : $sub_domain;

	// 	$subDomainIds   = $sub_domain ? $sub_domain : [];
	// 	$stateSlugs = DB::table('states')->whereIn('id', [$state_id])->pluck('slug')->toArray();

	// 	$query = DB::table('team_snp_scheme as tss')
	// 		->leftJoin('network_providers as np', 'tss.network_provider_id', '=', 'np.id')
	// 		->select(
	// 			'tss.snp_id',
	// 			'tss.organization_id as snp_organization_id',
	// 			'tss.organization_name as snp_organization_name',
	// 			'tss.short_description as snp_short_description',
	// 			'tss.short_description as snp_short_description',
	// 			'tss.sub_domain as snp_domain',
	// 			'tss.id',
	// 			'np.commercial_model_details',
	// 			'np.np_team_id',
	// 			'np.organization_name',
	// 			'np.contact_details',
	// 			'np.role_selection_details',
	// 			'np.value_proposition_details'
	// 		);

	// 		$query->where('tss.status', 1);
	// 		$query->orWhere('np.status', NetworkProviderStatus::APPROVE->value);

	// 		/* ->where(function ($query) {
	// 			return $query
	// 				->where('tss.status', 1)
	// 				->orWhere('np.status', NetworkProviderStatus::APPROVE->value);
	// 		}); */


	// 	if (! in_array('national', $stateSlugs)) { 
	// 		$query->where(function($query) use ($state_id){
	// 			return $query->whereRaw("JSON_CONTAINS(tss.state_id, ?)", ['"' . $state_id . '"'])
	// 				->orWhereRaw("JSON_CONTAINS(tss.state_id, ?)", ['5bc85de0-0292-11f1-922a-00155d022d06']);
	// 		});
	// 	} else {
	// 		$query->whereRaw("JSON_CONTAINS(tss.state_id, ?)", ['5bc85de0-0292-11f1-922a-00155d022d06']);	
	// 	}


	// 	$query->whereRaw("JSON_CONTAINS(tss.transaction_type, ?)", ['"' . $transaction_type . '"']);
	// 	 if (!empty($subDomainIds)) {
	// 		$query->where(function ($q) use ($subDomainIds) {
	// 			foreach ($subDomainIds as $domain) {
	// 				$q->orWhereRaw("JSON_CONTAINS(tss.sub_domain, ?)", ['"' . $domain . '"']);
	// 			}
	// 		});
	// 	 } 


	// 	$data = $query->get();

	// 	$decodedData = [];
	// 	$result = collect($data)->map(function ($item) {

	// 		$contactDetails = $item->contact_details ? json_decode($item->contact_details, true) : [];
	// 		$roleDetails = $item->role_selection_details ? json_decode($item->role_selection_details, true) : [];

	// 		$valueDetails = $item->value_proposition_details ? json_decode($item->value_proposition_details, true) : [];
	// 		$commercialDetails = $item->commercial_model_details ? json_decode($item->commercial_model_details, true) : [];

	// 		$productCategories = collect($roleDetails)->pluck('domain_name')->unique()->values()->implode(',');

	// 		if (! $productCategories) {
	// 			$productCategories = DB::table('sub_domains')->whereIn('id', json_decode($item->snp_domain, true))->pluck('name')->implode(',');
	// 		}

	// 		return [
	// 			'id' => $item->id ?? null,
	// 			'snp_id' => $item->np_team_id ? $item->np_team_id : $item->snp_id,
	// 			'organisation_name' => $item->organization_name ?? $item->snp_organization_name,
	// 			'website' => $contactDetails['website'] ?? null,
	// 			// Multiple domains possible
	// 			'product_category' => $productCategories,

	// 			'short_description' => $valueDetails['short_description'] ?? $item->snp_short_description,

	// 			'commercial_model' => $commercialDetails ?? null,

	// 			'languages_supported' => $valueDetails['language_supported_name'] ?? null,
	// 		];
	// 	})->toArray();



	// 	//dd($result);

	// 	return $result;
	// }



	public function getSnpDetails($id)
	{
		return DB::table('team_snp_scheme')->where('user_id', $id)->value('id');
	}

	public function getStateId($table, $state_code)
	{
		return DB::table($table)->where('code', $state_code)->first()->id;
	}
}
