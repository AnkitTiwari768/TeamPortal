<?php
declare(strict_types=1);
namespace App\Web\MisReport;
use App\Traits\DataTable;
use App\Core\BaseService;
use App\Http\Services\CommonService;
use App\Web\MisReport\ClaimResource;
use App\Web\User\UserService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

use App\Traits\HasAttribute;
use DB;
use Carbon\Carbon;

use App\Domain\Claim\ClaimStatus;
use App\Domain\Batch\BatchStatus;
use App\Web\MisReport\FinalStatus;

class MisReportService extends BaseService
{
	use DataTable, HasAttribute;
	protected array $columns = [
		1 => 'udyam_no',
		2 => 'mobile',
		3 => 'email',
		4 => 'entrepreneur_name',
		5 => 'enterprise_name',
		6 => 'organisation_type',
		7 => 'msme_classification',
		8 => 'social_category',
		9 => 'st.name',
		10 => 'created_at'
	];

	public function getMsmeList($onboarded = null)
	{
		[$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
		$search ??= $this->escape_special_characters($search);

		$query = DB::table('team_msme_schemes as ms')
			->select('ms.id', 'ms.user_id', 'ms.udyam_no', 'ms.gender', 'ms.total_emp', 'ms.net_investment_plant_machinery', 'ms.incorporation_date', 'ms.turnover', 'sub.name', 'ms.product_category_id', 'ms.mobile', 'ms.email', 'ms.entrepreneur_name', 'ms.enterprise_name', 'ms.organisation_type', 'ms.msme_classification', 'ms.team_id', 'ms.major_activity', 'ms.social_category', 'st.name as state_name', 'ms.created_at', 'ondc_transaction_type_id', 'av.attribute_value as transaction_type')
			->leftJoin('states as st', 'st.id', '=', 'ms.state_id')
			->leftJoin('sub_domains as sub', 'sub.id', '=', 'ms.product_category_id')
			->leftJoin('attribute_values as av', 'av.id', '=', 'ms.ondc_transaction_type_id');

		if ($onboarded) {
			$query->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
				->join('team_snp_scheme as tss', 'tsm.snp_id', '=', 'tss.id')
				->addSelect('tss.snp_name', 'tss.snp_id')
				->where('tsm.status', 1);
			if ($onboarded == 2) {
				$query->where('ms.is_uploaded', 2);
			}
			if ($onboarded == 3) {
				$query->whereNotNull('ms.is_uploaded');
			}
		}



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
		if (!empty($filters['state_id']) && is_array($filters['state_id'])) {
			$query->whereIn('ms.state_id', $filters['state_id']);
		}
		if (!empty($filters['gender'])) {
			$query->where('gender', $filters['gender']);
		}

		if (!empty($filters['msme_classification'])) {
			$query->where('msme_classification', $filters['msme_classification']);
		}

		if (!empty($filters['major_activity'])) {
			$query->where('major_activity', $filters['major_activity']);
		}
		if (isset($filters['mapping_option'])) {
			if (!empty($filters['mapping_option'])) {

				$query->where('select_snp', $filters['mapping_option']);
			} else if ($filters['mapping_option'] === 0 || $filters['mapping_option'] === '0') {
				$query->where('select_snp', $filters['mapping_option']);
			}
		}




		if ($search) {
			$query->where(function ($query) use ($search) {
				$query->where('ms.udyam_no', 'like', "%$search%")
					->orWhere('ms.team_id', 'like', "%$search%")
					->orWhere('ms.mobile', 'like', "%$search%")
					->orWhere('ms.email', 'like', "%$search%")
					->orWhere('ms.entrepreneur_name', 'like', "%$search%")
					->orWhere('ms.enterprise_name', 'like', "%$search%")
					->orWhere('ms.organisation_type', 'like', "%$search%")
					->orWhere('ms.social_category', 'like', "%$search%")
					->orWhere('st.name', 'like', "%$search%")
					->orWhere('ms.created_at', 'like', "%$search%");
			});
		}
		
		$query->orderBy($order, $dir);

		if ($page) {
			return $this->getDataTableResult(
				MisReportResource::collection($query->paginate($limit))
			);
		}

		return MisReportResource::collection($query->get());
	}


	public function getMsmeDetails($id)
	{
		$detail = DB::table('team_msme_schemes as ms')
			->leftJoin('states as s', 's.id', '=', 'ms.state_id')
			->leftJoin('locations as d', 'd.id', '=', 'ms.district_id')
			->leftJoin('attribute_values as bs', 'bs.id', '=', 'ms.current_state_business_id')
			->leftJoin('attribute_values as ott', 'ott.id', '=', 'ms.ondc_transaction_type_id')
			->select(
				'ms.*',
				's.name as state_name',
				'd.name as district_name',
				'bs.attribute_value as business_state_name',
				'ott.attribute_value as transaction_type_name',
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

		return $detail;
	}

	public function getOnboardedMseList()
	{
		[$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

		$startDate = $filters['from_date'] ?? null;
		$endDate = $filters['to_date'] ?? null;
		$search ??= $this->escape_special_characters($search);

		$query = DB::table('team_msme_schemes as ms')
			->select('ms.id', 'ms.team_id', 'ms.udyam_no', 'ms.mobile', 'ms.email', 'ms.entrepreneur_name', 'ms.enterprise_name', 'ms.organisation_type', 'ms.msme_classification', 'ms.social_category', 'ms.created_at', 'ms.product_category_id', 'st.name as state', DB::raw("GROUP_CONCAT(DISTINCT sud.name) as subdomain_names"))
			->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
			->leftJoin('states as st', 'st.id', '=', 'ms.state_id')
			->leftJoin('sub_domains as sud', function ($join) {
				$join->whereRaw("JSON_CONTAINS(ms.product_category_id, JSON_QUOTE(sud.id))");
			})
			->where('tsm.status', 1);


		if ($startDate) {
			$startDate = date('Y-m-d', strtotime($startDate));
			$query->where('ms.created_at', '>=', $startDate);
		}

		if ($endDate) {
			$endDate = (new \DateTime($endDate))->modify('+1 days')->format('Y-m-d');
			$query->where('ms.created_at', '<=', $endDate);
		}

		if (isset($filters['ondc_transaction_type_id']) && !empty($filters['ondc_transaction_type_id'])) {
			$query->where('ondc_transaction_type_id', $filters['ondc_transaction_type_id']);
		}

		if (!empty($filters['product_category_id']) && is_array($filters['product_category_id'])) {
			foreach ($filters['product_category_id'] as $categoryId) {
				$query->orWhereJsonContains('product_category_id', $categoryId);
			}
		}

		if ($search) {
			$query->where(function ($query) use ($search) {
				$query->where('ms.team_id', 'like', "%$search%")
					->orWhere('ms.udyam_no', 'like', "%$search%")
					->orWhere('ms.mobile', 'like', "%$search%")
					->orWhere('ms.email', 'like', "%$search%")
					->orWhere('ms.entrepreneur_name', 'like', "%$search%")
					->orWhere('ms.enterprise_name', 'like', "%$search%")
					->orWhere('ms.organisation_type', 'like', "%$search%")
					->orWhere('ms.created_at', 'like', "%$search%");
			});
		}

		$query->orderBy($order, $dir);

		if ($page) {
			return $this->getDataTableResult(
				MsmeResource::collection($query->paginate($limit))
			);
		}

		return MsmeResource::collection($query->get());
	}

	public function getDropdownList()
	{
		$commonService = new CommonService();
		return [
			'ondc_types' => $this->listOf('types-of-transactions-preferred-on-ondc'),
			'state_id' => $commonService->getStates(countryId: ''),
			'sub_domains' => $commonService->getDropdownNewList('sub_domains', 'status', 'ASC', 'name', array('id', 'name')),
		];
	}

	public function getClaimById(string $claimId): ?array
	{
		return (array) DB::table('claims')
			->select(
				'claims.*',
				'av.attribute_value as msme_transaction_type_name'
			)
			->leftJoin('attribute_values as av', 'av.id', '=', 'claims.msme_transaction_type')
			->where('claims.id', $claimId)
			->first();
	}
	public function getClaimTypes($claimSlug)
	{
		return DB::table('claim_types')
			->where('slug', $claimSlug)
			->pluck('name', 'id')
			->toArray();
	}

	public function getClaimTypeId($claimSlug)
	{
		return (array) DB::table('claim_types')
			->where('slug', $claimSlug)
			->pluck('name', 'id')
		;
	}


	/*public function getClaimReport(?string $claimTypeSlug = null): array
	{

		[$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
		$search ??= $this->escape_special_characters($search);
		$user = auth()->user();

		$workflowTypeId = DB::table('workflow_types')
			->where('slug', $claimTypeSlug ?? 'claim-for-catalogue-creation')
			->value('id');

		$claimTypeId = DB::table('claim_types')->where('slug', $claimTypeSlug ?? 'claim-for-catalogue-creation')->value('id');
		// dd($claimTypeId);
		$query = DB::table('claims as c')
			->select(
				'c.id',
				'tss.snp_id',
				'tss.snp_name',
				'c.application_number',
				'tms.team_id as team_registration_id',
				'tms.udyam_no as msme_udyam_number',
				'tms.entrepreneur_name as msme_name',
				'tms.msme_classification',
				'tms.major_activity',
				'atr.attribute_value as target_customer',
				'tms.seller_provider_id',
				'c.onboarding_date',
				'c.status',
				'c.is_bulk',
				'c.amount',
				'c.gst_charge',
				'c.gst_charge_amount',
				'c.date_of_sku_update',
				DB::raw("GROUP_CONCAT(DISTINCT sud.name) as subdomain_names"),
			)
			->join('claim_types as ct', 'ct.id', '=', 'c.claim_type_id')
			->join('team_snp_scheme as tss', 'tss.snp_id', '=', 'c.snp_id')
			->join('team_msme_schemes as tms', 'tms.udyam_no', '=', 'c.msme_udyam_number')
			->join('attribute_values as atr', 'atr.id', '=', 'c.msme_transaction_type')

			->leftJoin('sub_domains as sud', function ($join) {
				$join->whereRaw("JSON_CONTAINS(tms.product_category_id, JSON_QUOTE(sud.id))");
			})

			->distinct();
		$query->where('c.claim_type_id', $claimTypeId);
		$query->groupBy(
			'c.id',
			'tss.snp_id',
			'tss.snp_name',
			'c.application_number',
			'tms.team_id',
			'tms.udyam_no',
			'tms.entrepreneur_name',
			'tms.msme_classification',
			'tms.major_activity',
			'atr.attribute_value',
			'tms.seller_provider_id',
			'c.onboarding_date',
			'c.status',
			'c.is_bulk',
			'c.amount',
			'c.gst_charge',
			'c.gst_charge_amount'
		);

		$query->orderBy('c.created_at', 'desc');



		//if (isset($filters)) {
		//if (isset($filters['review_status'])) {
		//if ($filters['review_status'] === 'Pending') {
		//$query->whereIn('c.status', [
		// ClaimReviewStatus::SUBMITTED->value,
		//ClaimReviewStatus::PENDING->value,
		//ClaimReviewStatus::FORWARDED->value,
		//]);
		//} else {
		//  $action = ClaimReviewStatus::getIdByName($filters['review_status']);
		// $query->where('c.status', $action);
		//}
		//}


		//}

		// if ($search) {
		//     $query->where(function ($query) use ($search) {
		//         $query
		//             ->where('c.snp_id', 'like', "$search%")
		//             ->orWhere('c.team_registration_id', 'like', "$search%")
		//             ->orWhere('c.msme_udyam_number', 'like', "$search%")
		//             ->orWhere('c.msme_name', 'like', "$search%")
		//             ->orWhere('c.seller_provider_id', 'like', "$search%")
		//             ->orWhere('c.amount', 'like', "$search%")
		// 			->orWhere('c.gst_charge', 'like', "$search%")
		// 			->orWhere('c.gst_charge_amount', 'like', "$search%")
		//             ->orWhereRaw("DATE_FORMAT(c.onboarding_date, '%d-%m-%Y') like ?", ["$search%"]);
		//     });
		// }


		// dd($query->toSql());

		if ($page) {
			return $this->getDataTableResult(
				ClaimResource::collection($query->paginate($limit))
			);
		}

		return ClaimResource::collection($query->get());
	}*/

	public function getClaimReport(?string $claimTypeSlug = null): array
	{

		[$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
		$search ??= $this->escape_special_characters($search);

		$claimTypeId = DB::table('claim_types')
			->where('slug', $claimTypeSlug ?? 'claim-for-catalogue-creation')
			->value('id');

		$query = DB::table('claims as c')
			->select(
				'c.id',
				'tss.snp_id',
				'tss.snp_name',
				'c.application_number',
				'tms.team_id as team_registration_id',
				'tms.udyam_no as msme_udyam_number',
				'tms.entrepreneur_name as msme_name',
				'tms.msme_classification',
				'tms.major_activity',
				'atr.attribute_value as target_customer',
				'c.bpp_id',
				'c.onboarding_date',
				'c.status',
				'c.claim_status',
				'c.is_bulk',
				'c.amount',
				'c.date_of_sku_update',
				'c.submitted_at',
				DB::raw("(SELECT GROUP_CONCAT(name)
					FROM sub_domains sud
					WHERE JSON_CONTAINS(tms.product_category_id, JSON_QUOTE(sud.id))
				) as subdomain_names")
			)
			->join('team_snp_scheme as tss', 'tss.snp_id', '=', 'c.snp_id')
			->join('team_msme_schemes as tms', 'tms.udyam_no', '=', 'c.msme_udyam_number')
			->join('attribute_values as atr', 'atr.id', '=', 'c.msme_transaction_type')
			->where('c.claim_type_id', $claimTypeId);

			

			if (isset($search) && !empty($search) && $this->escape_special_characters($search)) {
				$query->where(function ($query) use ($search) {
					$query
						->where('c.snp_id', 'like', "$search%")
						->orWhere('tss.snp_name', 'like', "$search%")
						->orWhere('c.application_number', 'like', "$search%")
						->orWhere('tms.team_id', 'like', "$search%")
						->orWhere('tms.udyam_no', 'like', "$search%")
						->orWhere('tms.entrepreneur_name', 'like', "$search%")
						->orWhere('c.amount', 'like', "$search%")
						->orWhereRaw("DATE_FORMAT(c.submitted_at, '%d-%m-%Y') like ?", ["$search%"])
						->orWhereRaw("DATE_FORMAT(c.onboarding_date, '%d-%m-%Y') like ?", ["$search%"]);
				});
			}

			if (!empty($filters['from_date'])) {
           	 	$from = Carbon::createFromFormat('d-m-Y', $filters['from_date'])->format('Y-m-d');
			}

			if (!empty($filters['to_date'])) {
				$to = Carbon::createFromFormat('d-m-Y', $filters['to_date'])->format('Y-m-d');
			}

			if (!empty($from) && !empty($to)) {
				//$query->whereBetween('c.created_at', [$from, $to]);
				$query->whereBetween('c.created_at', [$from . ' 00:00:00',$to . ' 23:59:59']);
			} elseif (!empty($from)) {
				$query->whereDate('c.created_at', '>=', $from);
			} elseif (!empty($to)) {
				$query->whereDate('c.created_at', '<=', $to);
			}
			

			if (isset($filters['review_status']) && $filters['review_status'] !== '') {

				$finalStatus = FinalStatus::tryFrom((int)$filters['review_status']);

				if ($finalStatus) {
					$query->whereIn('c.claim_status', $finalStatus->statuses());
				}
			}



			$query->orderBy('c.created_at', 'desc');

			if ($page) {
				return $this->getDataTableResult(
					ClaimResource::collection($query->paginate($limit))
				);
			}

			return ClaimResource::collection($query->get());
	}

	public function getClaimReportLogistics(?string $claimTypeSlug = null): array
	{

		[$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
		$search ??= $this->escape_special_characters($search);

		$claimTypeId = DB::table('claim_types')
			->where('slug', $claimTypeSlug ?? 'claim-for-catalogue-creation')
			->value('id');

		$query = DB::table('claims as c')
			->select(
				'c.id',
				'np.np_team_id as snp_id',
				'u.first_name as snp_name',
				'c.application_number',
				'tms.team_id as team_registration_id',
				'tms.udyam_no as msme_udyam_number',
				'tms.entrepreneur_name as msme_name',
				'tms.msme_classification',
				'tms.major_activity',
				'atr.attribute_value as target_customer',
				'c.bpp_id',
				'c.onboarding_date',
				'c.status',
				'c.claim_status',
				'c.is_bulk',
				'c.amount',
				'c.date_of_sku_update',
				'c.submitted_at',
				DB::raw("(SELECT GROUP_CONCAT(name)
					FROM sub_domains sud
					WHERE JSON_CONTAINS(tms.product_category_id, JSON_QUOTE(sud.id))
				) as subdomain_names")
			)
			// ->join('team_snp_scheme as tss', 'tss.snp_id', '=', 'c.snp_id')
			->join('network_providers as np', 'np.np_team_id', '=', 'c.snp_id')
			->join('users as u', 'u.id', '=', 'np.user_id')
			->join('team_msme_schemes as tms', 'tms.udyam_no', '=', 'c.msme_udyam_number')
			->join('attribute_values as atr', 'atr.id', '=', 'c.msme_transaction_type')
			->where('c.claim_type_id', $claimTypeId);

			

			if (isset($search) && !empty($search) && $this->escape_special_characters($search)) {
				$query->where(function ($query) use ($search) {
					$query
						->where('c.snp_id', 'like', "$search%")
						->orWhere('u.first_name', 'like', "$search%")
						->orWhere('c.application_number', 'like', "$search%")
						->orWhere('tms.team_id', 'like', "$search%")
						->orWhere('tms.udyam_no', 'like', "$search%")
						->orWhere('tms.entrepreneur_name', 'like', "$search%")
						->orWhere('c.amount', 'like', "$search%")
						->orWhereRaw("DATE_FORMAT(c.submitted_at, '%d-%m-%Y') like ?", ["$search%"])
						->orWhereRaw("DATE_FORMAT(c.onboarding_date, '%d-%m-%Y') like ?", ["$search%"]);
				});
			}

			 

			if (!empty($filters['from_date'])) {
           	 	$from = Carbon::createFromFormat('d-m-Y', $filters['from_date'])->format('Y-m-d');
			}

			if (!empty($filters['to_date'])) {
				$to = Carbon::createFromFormat('d-m-Y', $filters['to_date'])->format('Y-m-d');
			}

			if (!empty($from) && !empty($to)) {
				//$query->whereBetween('c.created_at', [$from, $to]);
				$query->whereBetween('c.created_at', [$from . ' 00:00:00',$to . ' 23:59:59']);
			} elseif (!empty($from)) {
				$query->whereDate('c.created_at', '>=', $from);
			} elseif (!empty($to)) {
				$query->whereDate('c.created_at', '<=', $to);
			}
			
			if (isset($filters['review_status']) && $filters['review_status'] !== '') {

				$finalStatus = FinalStatus::tryFrom((int)$filters['review_status']);

				if ($finalStatus) {
					$query->whereIn('c.claim_status', $finalStatus->statuses());
				}
			}



			$query->orderBy('c.created_at', 'desc');

			if ($page) {
				return $this->getDataTableResult(
					ClaimResource::collection($query->paginate($limit))
				);
			}

			return ClaimResource::collection($query->get());
	}


}