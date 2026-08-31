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
use App\Web\MisReport\AssociationListResource;

use App\Domain\IARegistration\IAStatus;
use App\Domain\IARegistration\IndustrialAssociation;
use App\Domain\MIS\SnpWiseMISReportAction;

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


	// public function getMsmeMisList($onboarded = null)
	// {
	// 	[$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
	// 	$search ??= $this->escape_special_characters($search);

	// 	$query = DB::table('team_msme_schemes as ms')
	// 		->select(
	// 		'ms.id', 'ms.user_id', 'ms.udyam_no', 'ms.gender', 'ms.total_emp', 'ms.net_investment_plant_machinery', 'ms.incorporation_date', 'ms.turnover', 'sub.name', 'ms.product_category_id', 'ms.mobile', 'ms.email', 'ms.entrepreneur_name', 'ms.enterprise_name', 'ms.organisation_type', 'ms.msme_classification', 'ms.team_id', 'ms.major_activity', 'ms.social_category', 'st.name as state_name', 'ms.created_at', 'ondc_transaction_type_id', 'av.attribute_value as transaction_type',
	// 		DB::raw("
    //         CASE
    //             WHEN ia.organization_name IS NOT NULL
    //                  AND ia.organization_name != ''
    //             THEN ia.organization_name

    //             WHEN creator_snp.organization_name IS NOT NULL
    //                  AND creator_snp.organization_name != ''
    //             THEN creator_snp.organization_name

    //             ELSE 'Self'
    //         END AS source_of_registration
    //     ")
	// 		)
	// 		->leftJoin('states as st', 'st.id', '=', 'ms.state_id')
	// 		->leftJoin('sub_domains as sub', 'sub.id', '=', 'ms.product_category_id')
	// 		->leftJoin('attribute_values as av', 'av.id', '=', 'ms.ondc_transaction_type_id')
	// 		->leftJoin('industrial_associations as ia', 'ia.user_id', '=', 'ms.created_by')
	// 		->leftJoin('team_snp_scheme as creator_snp', 'creator_snp.user_id', '=', 'ms.created_by')
	// 		->whereNotNull('ms.major_activity')
    //         ->where('ms.major_activity', '!=', '');

	// 	if (!empty($filters['from_dates'])) {
	// 		$from = \Carbon\Carbon::createFromFormat('d-m-Y', $filters['from_dates'])->format('Y-m-d');
	// 	}

	// 	if (!empty($filters['to_dates'])) {
	// 		$to = \Carbon\Carbon::createFromFormat('d-m-Y', $filters['to_dates'])->format('Y-m-d');
	// 	}

	// 	if (!empty($from) && !empty($to)) {
	// 		$query->whereBetween('ms.created_at', [$from, $to]);
	// 	} 

	// 	if (isset($filters['ondc_transaction_type_id']) && !empty($filters['ondc_transaction_type_id'])) {
	// 		$query->where('ondc_transaction_type_id', $filters['ondc_transaction_type_id']);
	// 	}

	// 	if (!empty($filters['product_category_id']) && is_array($filters['product_category_id'])) {
	// 			$query->where(function ($q) use ($filters) {
	// 				foreach ($filters['product_category_id'] as $categoryId) {
	// 					$q->orWhereJsonContains('ms.product_category_id', $categoryId);
	// 				}
	// 			});
	// 		}
	// 	if (!empty($filters['state_id']) && is_array($filters['state_id'])) {
	// 		$query->whereIn('ms.state_id', $filters['state_id']);
	// 	}
	// 	if (!empty($filters['gender'])) {
	// 		$query->where('gender', $filters['gender']);
	// 	}

	// 	if (!empty($filters['msme_classification'])) {
	// 		$query->where('msme_classification', $filters['msme_classification']);
	// 	}

	// 	if (!empty($filters['major_activity'])) {
	// 		$query->where('major_activity', $filters['major_activity']);
	// 	}
	// 	if (!empty($filters['source_of_registration'])) {

	// 		if ($filters['source_of_registration'] == 'Association') {
	// 			$query->whereNotNull('ia.organization_name')
	// 				->where('ia.organization_name', '!=', '');
	// 		}

	// 		if ($filters['source_of_registration'] == 'SNP') {
	// 			$query->whereNull('ia.organization_name')
	// 				->whereNotNull('creator_snp.organization_name')
	// 				->where('creator_snp.organization_name', '!=', '');
	// 		}

	// 		if ($filters['source_of_registration'] == 'Self') {
	// 			$query->where(function ($q) {
	// 				$q->whereNull('ia.organization_name')
	// 				->whereNull('creator_snp.organization_name');
	// 			});
	// 		}
	// 	}

	// 	$type = $filters['msme_status'] ?? null;
	// 	if (!empty($type)) {
	// 		if ($type == 'open') {
	// 			$query
	// 				->where('ms.select_snp', 0)
	// 				->whereNull('ms.bpp_id');
	// 		}
	// 		if ($type == 'direct') {
	// 			$query->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
	// 			->where('ms.select_snp', 1)
	// 			->where('tsm.status', 0)
	// 			->whereNotNull('ms.major_activity')
	// 			->where('ms.major_activity', '!=', '');
	// 		}
	// 		if ($type == 'bulk') {
	// 			$query->whereNotNull('ms.is_bulk_import');
	// 		}		

	// 		// if ($type == 'onboarded') {
	// 		// 	$query->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
	// 		// 	->where('tsm.status', 1)
	// 		// 	->whereNotNull('ms.major_activity')
	// 		// 	->where('ms.major_activity', '!=', '');
	// 		// }
	// 		if ($type == 'onboarded') {
	// 			$query 
	// 				->join(
	// 					'team_snpmsme_mapping as tsm',
	// 					'tsm.msme_id',
	// 					'=',
	// 					'ms.id'
	// 				)
	// 				->join(
	// 					'team_snp_scheme as tss',
	// 					'tsm.snp_id',
	// 					'=',
	// 					'tss.id'
	// 				)
	// 				->where('tsm.status', 1)
	// 				->whereNotNull('ms.major_activity')
	// 				->where('ms.major_activity', '!=', '');
	// 		}
	// 	}
	// 	if (isset($filters['mapping_option']) && $filters['mapping_option'] !== '') {
	// 		if ($filters['mapping_option'] == '1') {
	// 			$query->where('select_snp', 1);
	// 		}
	// 		if ($filters['mapping_option'] == '0') {
	// 			$query->where('select_snp', 0)
	// 				->whereNotNull('bpp_id')
	// 				->where('bpp_id', '!=', '');
	// 		}
	// 	}
	// 	if ($search) {
	// 		$query->where(function ($query) use ($search) {
	// 			$query->where('ms.udyam_no', 'like', "%$search%")
	// 				->orWhere('ms.team_id', 'like', "%$search%")
	// 				->orWhere('ms.mobile', 'like', "%$search%")
	// 				->orWhere('ms.email', 'like', "%$search%")
	// 				->orWhere('ms.entrepreneur_name', 'like', "%$search%")
	// 				->orWhere('ms.enterprise_name', 'like', "%$search%")
	// 				->orWhere('ms.organisation_type', 'like', "%$search%")
	// 				->orWhere('ms.social_category', 'like', "%$search%")
	// 				->orWhere('st.name', 'like', "%$search%")
	// 				->orWhere('ms.msme_classification', 'like', "%$search%")
	// 				->orWhere('ms.incorporation_date', 'like', "%$search%")
	// 				->orWhere('ms.total_emp', 'like', "%$search%")
	// 				->orWhere('ms.gender', 'like', "%$search%")
	// 				->orWhere('ms.turnover', 'like', "%$search%")
	// 				->orWhere('av.attribute_value', 'like', "%$search%")
	// 				->orWhere('ms.major_activity', 'like', "%$search%")
	// 				->orWhere('ia.organization_name', 'like', "%$search%")
	// 				->orWhere('creator_snp.organization_name', 'like', "%$search%")
	// 				->orWhereRaw("
	// 						EXISTS (
	// 							SELECT 1
	// 							FROM sub_domains sud
	// 							WHERE JSON_CONTAINS(
	// 								ms.product_category_id,
	// 								JSON_QUOTE(sud.id)
	// 							)
	// 							AND sud.name LIKE ?
	// 						)
	// 					", ["$search%"])
	// 				->orWhereRaw("DATE_FORMAT(ms.created_at, '%d-%m-%Y') like ?", ["$search%"]);
	// 		});
	// 	}

	// 	$query->orderBy($order, $dir);


	// 	if ($page) {
	// 		return $this->getDataTableResult(
	// 			MisReportResource::collection($query->paginate($limit))
	// 		);
	// 	}

	// 	return MisReportResource::collection($query->get());
	// }

		public function getMsmeMisList($onboarded = null)
	{
		[$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
		$search ??= $this->escape_special_characters($search);

		$query = DB::table('team_msme_schemes as ms')
			->select(
			'ms.id', 'ms.user_id', 'ms.udyam_no', 'ms.gender', 'ms.total_emp', 'ms.net_investment_plant_machinery', 'ms.incorporation_date', 'ms.turnover', 'sub.name', 'ms.product_category_id', 'ms.mobile', 'ms.email', 'ms.entrepreneur_name', 'ms.enterprise_name', 'ms.organisation_type', 'ms.msme_classification', 'ms.team_id', 'ms.major_activity', 'ms.social_category', 'st.name as state_name', 'ms.created_at', 'ondc_transaction_type_id', 'av.attribute_value as transaction_type',
			DB::raw("
            CASE
                WHEN ia.organization_name IS NOT NULL
                     AND ia.organization_name != ''
                THEN ia.organization_name

                WHEN creator_snp.organization_name IS NOT NULL
                     AND creator_snp.organization_name != ''
                THEN creator_snp.organization_name

                ELSE 'Self'
            END AS source_of_registration
        ")
			)
			->leftJoin('states as st', 'st.id', '=', 'ms.state_id')
			->leftJoin('sub_domains as sub', 'sub.id', '=', 'ms.product_category_id')
			->leftJoin('attribute_values as av', 'av.id', '=', 'ms.ondc_transaction_type_id')
			->leftJoin('industrial_associations as ia', 'ia.user_id', '=', 'ms.created_by')
			->leftJoin('team_snp_scheme as creator_snp', 'creator_snp.user_id', '=', 'ms.created_by')
			->whereNotNull('ms.major_activity')
            ->where('ms.major_activity', '!=', '');

		if (!empty($filters['from_dates'])) {
			$from = \Carbon\Carbon::createFromFormat('d-m-Y', $filters['from_dates'])->format('Y-m-d');
		}

		if (!empty($filters['to_dates'])) {
			$to = \Carbon\Carbon::createFromFormat('d-m-Y', $filters['to_dates'])->format('Y-m-d');
		}

		if (!empty($from) && !empty($to)) {
			$query->whereBetween('ms.created_at', [$from, $to]);
		} 

		// if (isset($filters['ondc_transaction_type_id']) && !empty($filters['ondc_transaction_type_id'])) {
		// 	$query->where('ondc_transaction_type_id', $filters['ondc_transaction_type_id']);
		// }
		// if (isset($filters['ondc_transaction_type_id']) && !empty($filters['ondc_transaction_type_id'])) {
		// 	$transactionType = (string) $filters['ondc_transaction_type_id'];
		// 	// Both selected => B2B + B2C + Both
		// 	if ($transactionType === 'b44fb78b-d49e-11f0-922a-00155d022d06') {

		// 		$query->whereIn('ms.ondc_transaction_type_id', [
		// 			'9e7e1e8b-5578-11f0-81dc-00155d022d06', // B2B
		// 			'36523ead-533d-11f0-81dc-00155d022d06', // B2C
		// 			'b44fb78b-d49e-11f0-922a-00155d022d06', // Both
		// 		]);
		// 	} else {

		// 		// B2B / B2C selected => only selected type
		// 		$query->where(
		// 			'ms.ondc_transaction_type_id',
		// 			$transactionType
		// 		);
		// 	}
		// }

		if (isset($filters['ondc_transaction_type_id']) && !empty($filters['ondc_transaction_type_id'])) {
				$transactionType = (string) $filters['ondc_transaction_type_id'];
				if ($transactionType === 'b44fb78b-d49e-11f0-922a-00155d022d06') {
					$query->whereIn(
						'ms.ondc_transaction_type_id',
						[
							'9e7e1e8b-5578-11f0-81dc-00155d022d06', // B2B
							'36523ead-533d-11f0-81dc-00155d022d06', // B2C
							'b44fb78b-d49e-11f0-922a-00155d022d06', // Both
						]
					);
				} else {
					$query->where(function ($subQuery) use ($transactionType) {
						$subQuery->where(
							'ms.ondc_transaction_type_id',
							$transactionType
						)->orWhere(
							'ms.ondc_transaction_type_id',
							'b44fb78b-d49e-11f0-922a-00155d022d06' // Both
						);
					});
				}
			}

		if (!empty($filters['product_category_id']) && is_array($filters['product_category_id'])) {
				$query->where(function ($q) use ($filters) {
					foreach ($filters['product_category_id'] as $categoryId) {
						$q->orWhereJsonContains('ms.product_category_id', $categoryId);
					}
				});
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
		if (!empty($filters['source_of_registration'])) {

			if ($filters['source_of_registration'] == 'Association') {
				$query->whereNotNull('ia.organization_name')
					->where('ia.organization_name', '!=', '');
			}

			if ($filters['source_of_registration'] == 'SNP') {
				$query->whereNull('ia.organization_name')
					->whereNotNull('creator_snp.organization_name')
					->where('creator_snp.organization_name', '!=', '');
			}

			if ($filters['source_of_registration'] == 'Self') {
				$query->where(function ($q) {
					$q->whereNull('ia.organization_name')
					->whereNull('creator_snp.organization_name');
				});
			}
		}

		// SNP filter — reuses the same role-selection matching logic as the
		// SNP Wise MIS Report and the SNP dashboard "Open" card, so selecting
		// the same SNP/Category/Transaction Type here yields the same count.
		if (!empty($filters['snp_id'])) {
			$snpUserId = DB::table('team_snp_scheme')->where('id', $filters['snp_id'])->value('user_id');
			$roleSelections = [];
			if ($snpUserId) {
				$roleSelectionDetails = DB::table('network_providers')->where('user_id', $snpUserId)->value('role_selection_details');
				$roleSelections = $roleSelectionDetails ? json_decode($roleSelectionDetails, true) : [];
			}
			if (empty($roleSelections)) {
				$query->whereRaw('1 = 0');
			} else {
				SnpWiseMISReportAction::applyRoleSelectionOpenFilters(
					$query,
					$roleSelections,
					SnpWiseMISReportAction::getNationalStateId()
				);
			}
		}

		$type = $filters['msme_status'] ?? null;
		if (!empty($type)) {
			if ($type == 'open') {
				$query
					->where('ms.select_snp', 0)
					->whereNull('ms.bpp_id');
			}
			if ($type == 'direct') {
				$query->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
				->where('ms.select_snp', 1)
				->where('tsm.status', 0)
				->whereNotNull('ms.major_activity')
				->where('ms.major_activity', '!=', '');
			}
			if ($type == 'bulk') {
				$query->whereNotNull('ms.is_bulk_import');
			}

			// if ($type == 'onboarded') {
			// 	$query->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
			// 	->where('tsm.status', 1)
			// 	->whereNotNull('ms.major_activity')
			// 	->where('ms.major_activity', '!=', '');
			// }
			if ($type == 'onboarded') {
				$query
					->join(
						'team_snpmsme_mapping as tsm',
						'tsm.msme_id',
						'=',
						'ms.id'
					)
					->join(
						'team_snp_scheme as tss',
						'tsm.snp_id',
						'=',
						'tss.id'
					)
					->where('tsm.status', 1)
					->whereNotNull('ms.major_activity')
					->where('ms.major_activity', '!=', '');
			}
		}
		if (isset($filters['mapping_option']) && $filters['mapping_option'] !== '') {
			if ($filters['mapping_option'] == '1') {
				$query->where('select_snp', 1);
			}
			if ($filters['mapping_option'] == '0') {
				$query->where('select_snp', 0)
					->whereNotNull('bpp_id')
					->where('bpp_id', '!=', '');
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
					->orWhere('ms.msme_classification', 'like', "%$search%")
					->orWhere('ms.incorporation_date', 'like', "%$search%")
					->orWhere('ms.total_emp', 'like', "%$search%")
					->orWhere('ms.gender', 'like', "%$search%")
					->orWhere('ms.turnover', 'like', "%$search%")
					->orWhere('av.attribute_value', 'like', "%$search%")
					->orWhere('ms.major_activity', 'like', "%$search%")
					->orWhere('ia.organization_name', 'like', "%$search%")
					->orWhere('creator_snp.organization_name', 'like', "%$search%")
					->orWhereRaw("
							EXISTS (
								SELECT 1
								FROM sub_domains sud
								WHERE JSON_CONTAINS(
									ms.product_category_id,
									JSON_QUOTE(sud.id)
								)
								AND sud.name LIKE ?
							)
						", ["$search%"])
					->orWhereRaw("DATE_FORMAT(ms.created_at, '%d-%m-%Y') like ?", ["$search%"]);
			});
		}

		$query->orderBy($order, $dir);


		if ($page) {
			$paginated = $query->paginate($limit);
			$this->attachSnpIds($paginated->getCollection());

			return $this->getDataTableResult(
				MisReportResource::collection($paginated)
			);
		}

		$rows = $query->get();
		$this->attachSnpIds($rows);

		return MisReportResource::collection($rows);
	}

	/**
	 * Batch-attach a comma-separated "snp_id" (e.g. "SNP0057, SNP0068") to each row,
	 * in a single query for the whole result set instead of one correlated subquery
	 * per row (team_snpmsme_mapping.msme_id has no index, so per-row lookups are slow).
	 */
	private function attachSnpIds($rows): void
	{
		$ids = collect($rows)->pluck('id')->filter()->unique()->values();

		if ($ids->isEmpty()) {
			return;
		}

		// Chunk the lookup so a large (e.g. unpaginated) result set can't exceed
		// the SQL driver's bound-parameter limit on a single whereIn(...).
		$snpIdsByMsme = $ids->chunk(1000)->reduce(function ($carry, $chunk) {
			$mappings = DB::table('team_snpmsme_mapping as tsm')
				->join('team_snp_scheme as tss', 'tss.id', '=', 'tsm.snp_id')
				->whereIn('tsm.msme_id', $chunk)
				->select('tsm.msme_id', 'tss.snp_id')
				->get();

			return $carry->concat($mappings);
		}, collect())
			->groupBy('msme_id')
			->map(function ($group) {
				return $group->pluck('snp_id')->unique()->sort()->implode(', ');
			});

		foreach ($rows as $row) {
			$row->snp_id = $snpIdsByMsme->get($row->id);
		}
	}

	public function getMsmeList($onboarded = null)
	{
		[$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
		$search ??= $this->escape_special_characters($search);

		$query = DB::table('team_msme_schemes as ms')
			->select('ms.id', 'ms.user_id', 'ms.udyam_no', 'ms.gender', 'ms.total_emp', 'ms.net_investment_plant_machinery', 'ms.incorporation_date', 'ms.turnover', 'sub.name', 'ms.product_category_id', 'ms.mobile', 'ms.email', 'ms.entrepreneur_name', 'ms.enterprise_name', 'ms.organisation_type', 'ms.msme_classification', 'ms.team_id', 'ms.major_activity', 'ms.social_category', 'st.name as state_name', 'ms.created_at', 'ondc_transaction_type_id', 'av.attribute_value as transaction_type',     DB::raw("
            CASE
                WHEN ia.organization_name IS NOT NULL
                     AND ia.organization_name != ''
                THEN ia.organization_name

                WHEN creator_snp.organization_name IS NOT NULL
                     AND creator_snp.organization_name != ''
                THEN creator_snp.organization_name

                ELSE 'Self'
            END AS source_of_registration
        ")
    			)
			->leftJoin('states as st', 'st.id', '=', 'ms.state_id')
			->leftJoin('sub_domains as sub', 'sub.id', '=', 'ms.product_category_id')
			->leftJoin('attribute_values as av', 'av.id', '=', 'ms.ondc_transaction_type_id')

			 // Source Of Registration Joins
			->leftJoin('industrial_associations as ia', 'ia.user_id', '=', 'ms.created_by')
			->leftJoin('team_snp_scheme as creator_snp', 'creator_snp.user_id', '=', 'ms.created_by')

			->whereNotNull('ms.major_activity')
            ->where('ms.major_activity', '!=', '');

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

		if (hasRole('snp')) {
			$snp = $this->getSnpDetail(AuthId());
			$snpId = $snp->id;

			$stateIds = json_decode($snp->state_id, true) ?: [];
			$txnIds   = json_decode($snp->transaction_type, true) ?: [];
			$subIds   = json_decode($snp->sub_domain, true) ?: [];

			$nationalId = '5bc85de0-0292-11f1-922a-00155d022d06';
			$query->leftJoin('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
			->where(function ($q) use ($snpId, $stateIds, $txnIds, $subIds, $nationalId) {

				// ================= OPEN =================
				$q->where(function ($q1) use ($stateIds, $txnIds, $subIds, $nationalId) {

					$q1->where('ms.select_snp', 0)
					->whereNull('ms.bpp_id');

					if (!in_array($nationalId, $stateIds) && !empty($stateIds)) {
						$q1->whereIn('ms.state_id', $stateIds);
					}

					if (!empty($txnIds)) {
						$q1->whereIn('ms.ondc_transaction_type_id', $txnIds);
					}

					if (!empty($subIds)) {
						$q1->where(function ($qq) use ($subIds) {
							foreach ($subIds as $id) {
								$qq->orWhereJsonContains('ms.product_category_id', $id);
							}
						});
					}

				})

				// DIRECT
				->orWhere(function ($q2) use ($snpId) {
					$q2->where('ms.select_snp', 1)
					->where(function ($inner) use ($snpId) {
						$inner->where('tsm.snp_id', $snpId)
								->where('tsm.status', 0);
					});
				})

				// ONBOARDED
				->orWhere(function ($q3) use ($snpId) {
					$q3->where(function ($inner) use ($snpId) {
							$inner->where('tsm.snp_id', $snpId)
								->where('tsm.status', 1);
						})
						->whereNotNull('ms.major_activity')
						->where('ms.major_activity', '!=', '');
				});

			})
			->distinct();
		}


		if (!empty($filters['from_dates'])) {
			$from = \Carbon\Carbon::createFromFormat('d-m-Y', $filters['from_dates'])->format('Y-m-d');
		}

		if (!empty($filters['to_dates'])) {
			$to = \Carbon\Carbon::createFromFormat('d-m-Y', $filters['to_dates'])->format('Y-m-d');
		}

		if (!empty($from) && !empty($to)) {
			$query->whereBetween('ms.created_at', [$from, $to]);
		} 
		/*elseif (!empty($from)) {
			$query->whereDate('ms.created_at', '>=', $from);
		} elseif (!empty($to)) {
			$query->whereDate('ms.created_at', '<=', $to);
		}*/

		if (!empty($filters['source_of_registration'])) {

			if ($filters['source_of_registration'] == 'Association') {
				$query->whereNotNull('ia.organization_name')
					->where('ia.organization_name', '!=', '');
			}

			if ($filters['source_of_registration'] == 'SNP') {
				$query->whereNull('ia.organization_name')
					->whereNotNull('creator_snp.organization_name')
					->where('creator_snp.organization_name', '!=', '');
			}

			if ($filters['source_of_registration'] == 'Self') {
				$query->where(function ($q) {
					$q->whereNull('ia.organization_name')
					->whereNull('creator_snp.organization_name');
				});
			}
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
		/*if (isset($filters['mapping_option'])) {
			if (!empty($filters['mapping_option'])) {

				$query->where('select_snp', $filters['mapping_option']);
			} else if ($filters['mapping_option'] === 0 || $filters['mapping_option'] === '0') {
				$query->where('select_snp', $filters['mapping_option']);
			}
		}*/

		if (isset($filters['mapping_option']) && $filters['mapping_option'] !== '') {

			// 1 = MSE Initiated
			if ($filters['mapping_option'] == '1') {
				$query->where('select_snp', 1);
					// ->where(function ($q) {
					// 	$q->whereNull('bpp_id')
					// 		->orWhere('bpp_id', '');
					// });
			}

			// 0 = SNP Initiated
			if ($filters['mapping_option'] == '0') {
				$query->where('select_snp', 0)
					->whereNotNull('bpp_id')
					->where('bpp_id', '!=', '');
			}
		}

		$type = $filters['msme_status'] ?? null;
		if (!empty($type)) {

				if ($type == 'open') {
					$query->where('ms.select_snp', 0)
					->whereNull('ms.bpp_id')
					->whereNotNull('ms.major_activity')->where('ms.major_activity', '!=', '');
				}

				if ($type == 'direct') {
					$query->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
					->where('ms.select_snp', 1)
					->where('tsm.status', 0)
					->whereNotNull('ms.major_activity')
					->where('ms.major_activity', '!=', '');
				}

				if ($type == 'bulk') {
					$query->whereNotNull('ms.is_bulk_import');
				}

			

				if ($type == 'onboarded') {
					$query->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
					->where('tsm.status', 1)
					->whereNotNull('ms.major_activity')
					->where('ms.major_activity', '!=', '');
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
					->orWhere('ms.msme_classification', 'like', "%$search%")
					->orWhere('ms.incorporation_date', 'like', "%$search%")
					->orWhere('ms.total_emp', 'like', "%$search%")
					->orWhere('ms.gender', 'like', "%$search%")
					->orWhere('ms.turnover', 'like', "%$search%")
					->orWhere('av.attribute_value', 'like', "%$search%")
					->orWhere('ms.major_activity', 'like', "%$search%")
					->orWhere('ia.organization_name', 'like', "%$search%")
					->orWhere('creator_snp.organization_name', 'like', "%$search%")
					->orWhereRaw("
							EXISTS (
								SELECT 1
								FROM sub_domains sud
								WHERE JSON_CONTAINS(
									ms.product_category_id,
									JSON_QUOTE(sud.id)
								)
								AND sud.name LIKE ?
							)
						", ["$search%"])
					->orWhereRaw("DATE_FORMAT(ms.created_at, '%d-%m-%Y') like ?", ["$search%"]);
					//->orWhere('ms.created_at', 'like', "%$search%");
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
			     // Source Of Registration Joins
			->leftJoin('industrial_associations as ia', 'ia.user_id', '=', 'ms.created_by')
			->leftJoin('team_snp_scheme as creator_snp', 'creator_snp.user_id', '=', 'ms.created_by')

			->select(
				'ms.*',
				's.name as state_name',
				'd.name as district_name',
				'bs.attribute_value as business_state_name',
				'ott.attribute_value as transaction_type_name',
				 DB::raw("
					CASE
						WHEN ia.organization_name IS NOT NULL
							AND ia.organization_name != ''
						THEN ia.organization_name

						WHEN creator_snp.organization_name IS NOT NULL
							AND creator_snp.organization_name != ''
						THEN creator_snp.organization_name

						ELSE 'Self'
					END as source_of_registration
				"),

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
			'snp_list' => $this->getSnpListForFilter(),
		];
	}

	/**
	 * SNP options for the mis-reports-msme filter — same source (team_snp_scheme,
	 * status = 2) used to build the SNP Wise MIS Report rows, so the two stay aligned.
	 */
	public function getSnpListForFilter(): array
	{
		return DB::table('team_snp_scheme as tss')
			->whereNotNull('tss.snp_id')
			->where('tss.status', 2)
			->orderBy('tss.organization_name', 'asc')
			->get(['tss.id', 'tss.snp_id', 'tss.organization_name'])
			->mapWithKeys(function ($row) {
				$label = trim(($row->snp_id ?? '') . ' - ' . ($row->organization_name ?? ''), ' -');
				return [$row->id => $label];
			})
			->toArray();
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
		$columns = [
			1 => 'c.application_number',
			2 => 'tms.team_id',
			3 => 'c.submitted_at',
		];

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
			->join(
				DB::raw('(
					SELECT 
						udyam_no,
						MAX(team_id) as team_id,
						MAX(entrepreneur_name) as entrepreneur_name,
						MAX(msme_classification) as msme_classification,
						MAX(major_activity) as major_activity,
						MAX(product_category_id) as product_category_id
					FROM team_msme_schemes
					GROUP BY udyam_no
				) as tms'),
				'tms.udyam_no',
				'=',
				'c.msme_udyam_number'
			)
			->join('attribute_values as atr', 'atr.id', '=', 'c.msme_transaction_type')
			->where('c.claim_type_id', $claimTypeId)
			->whereNotIn('c.claim_status', [0, -1]);

			
			
	
			if ($search) {
				$query->where(function ($query) use ($search) {
					$query
						->where('c.snp_id', 'like', "$search%")
						->orWhere('tss.snp_name', 'like', "$search%")
						->orWhere('c.application_number', 'like', "$search%")
						->orWhere('tms.team_id', 'like', "$search%")
						->orWhere('tms.udyam_no', 'like', "$search%")
						->orWhere('tms.entrepreneur_name', 'like', "$search%")
						->orWhere('c.amount', 'like', "$search%")
						->orWhere('tms.msme_classification', 'like', "$search%")
						->orWhere('tms.major_activity', 'like', "$search%")
						->orWhere('atr.attribute_value', 'like', "$search%")
						->orWhereRaw("
							EXISTS (
								SELECT 1
								FROM sub_domains sud
								WHERE JSON_CONTAINS(
									tms.product_category_id,
									JSON_QUOTE(sud.id)
								)
								AND sud.name LIKE ?
							)
						", ["$search%"])
						->orWhereRaw("DATE_FORMAT(c.submitted_at, '%d-%m-%Y') like ?", ["$search%"])
						->orWhereRaw("DATE_FORMAT(c.onboarding_date, '%d-%m-%Y') like ?", ["$search%"]);
				});
			}

			if (!empty($filters['from_dates'])) {
           	 	$from = Carbon::createFromFormat('d-m-Y', $filters['from_dates'])->format('Y-m-d');
			}

			if (!empty($filters['to_dates'])) {
				$to = Carbon::createFromFormat('d-m-Y', $filters['to_dates'])->format('Y-m-d');
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

			/*if (isset($filters['review_status']) && $filters['review_status'] !== '') {
				$statusClass = in_array($claimTypeSlug, [
					'claim-for-catalogue-creation',
					'claim-for-accounts-management'
				]) ? FinalStatusOld::class : FinalStatus::class;

				$finalStatus = $statusClass::tryFrom((int)$filters['review_status']);

				if ($finalStatus && !empty($finalStatus->statuses())) {
					$query->whereIn('c.claim_status', $finalStatus->statuses());
				}
			}*/

			//$query->orderBy('c.created_at', 'desc');


			$orderColumn = $this->columns[$order] ?? 'c.submitted_at';
			$dir = $dir ?: 'desc';

			$query->orderBy($orderColumn, $dir);

			if ($page) {
				return $this->getDataTableResult(
					ClaimResource::collection($query->paginate($limit))
				);
			}

			return ClaimResource::collection($query->get());

			/*$resource = in_array($claimTypeSlug, [
				'claim-for-catalogue-creation',
				'claim-for-accounts-management'
			]) ? ClaimResourceOld::class : ClaimResource::class;

			if ($page) {
				return $this->getDataTableResult(
					$resource::collection($query->paginate($limit))
				);
			}

			return $resource::collection($query->get());*/
	}

	public function getClaimReportLogistics(?string $claimTypeSlug = null): array
	{

		[$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
		$columns = [
			1 => 'c.application_number',
			2 => 'tms.team_id',
			3 => 'c.submitted_at',
		];

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
			->join('network_providers as np', 'np.np_team_id', '=', 'c.snp_id')
			->join('users as u', 'u.id', '=', 'np.user_id')
			->join(
				DB::raw('(
					SELECT 
						udyam_no,
						MAX(team_id) as team_id,
						MAX(entrepreneur_name) as entrepreneur_name,
						MAX(msme_classification) as msme_classification,
						MAX(major_activity) as major_activity,
						MAX(product_category_id) as product_category_id
					FROM team_msme_schemes
					GROUP BY udyam_no
				) as tms'),
				'tms.udyam_no',
				'=',
				'c.msme_udyam_number'
			)
			->join('attribute_values as atr', 'atr.id', '=', 'c.msme_transaction_type')
			->where('c.claim_type_id', $claimTypeId)
			->whereNotIn('c.claim_status', [0, -1]);

			

			if ($search) {
				$query->where(function ($query) use ($search) {
					$query
						->where('c.snp_id', 'like', "$search%")
						->orWhere('u.first_name', 'like', "$search%")
						->orWhere('c.application_number', 'like', "$search%")
						->orWhere('tms.team_id', 'like', "$search%")
						->orWhere('tms.udyam_no', 'like', "$search%")
						->orWhere('tms.entrepreneur_name', 'like', "$search%")
						->orWhere('c.amount', 'like', "$search%")
						->orWhere('tms.msme_classification', 'like', "$search%")
						->orWhere('tms.major_activity', 'like', "$search%")
						->orWhere('atr.attribute_value', 'like', "$search%")
						->orWhereRaw("
							EXISTS (
								SELECT 1
								FROM sub_domains sud
								WHERE JSON_CONTAINS(
									tms.product_category_id,
									JSON_QUOTE(sud.id)
								)
								AND sud.name LIKE ?
							)
						", ["$search%"])
						->orWhereRaw("DATE_FORMAT(c.submitted_at, '%d-%m-%Y') like ?", ["$search%"])
						->orWhereRaw("DATE_FORMAT(c.onboarding_date, '%d-%m-%Y') like ?", ["$search%"]);
				});
			}

			if (!empty($filters['from_dates'])) {
           	 	$from = Carbon::createFromFormat('d-m-Y', $filters['from_dates'])->format('Y-m-d');
			}

			if (!empty($filters['to_dates'])) {
				$to = Carbon::createFromFormat('d-m-Y', $filters['to_dates'])->format('Y-m-d');
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


			$orderColumn = $this->columns[$order] ?? 'c.submitted_at';
			$dir = $dir ?: 'desc';

			$query->orderBy($orderColumn, $dir);
			//$query->orderBy('c.created_at', 'desc');

			if ($page) {
				return $this->getDataTableResult(
					ClaimResource::collection($query->paginate($limit))
				);
			}

			return ClaimResource::collection($query->get());
	}


	public function getMsmeBulkList(): array
	{
		[$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

		$search ??= $this->escape_special_characters($search);

		$columns = [
			0 => 't.id',
			1 => 't.udyam_no',
			2 => 't.mobile',
			3 => 't.ondc_transaction_type_id',
			4 => 't.status',
			5 => 't.created_at',
			6 => 't.product_category_id',
			6 => 'u.first_name',
		];

		$orderColumn = $columns[$order] ?? 't.created_at';
		$orderDir = in_array(strtolower($dir), ['asc', 'desc']) ? $dir : 'desc';

		$query = DB::table('team_msme_scheme_drafts as t')
			->select(
				't.id',
				't.udyam_no',
				't.mobile',
				't.ondc_transaction_type_id',
				't.status',
				't.created_at',
				't.product_category_id',
				'u.first_name as name',
			)
			->join('users as u','u.id','=','t.created_by');

		if (hasRole('snp') || hasRole('ia-registration')) {
			//$query->where('t.created_by', AuthId());
			 $query->where(function ($q) {
				$q->where('t.created_by', (string) authId());

				if (auth()->user()->parent_user_id) {
					$q->orWhere('t.created_by', (string) auth()->user()->parent_user_id);
				}
    });
		}

		if (!empty($search)) {
			$query->where(function ($q) use ($search) {
				$q->orWhere('t.udyam_no', 'like', "$search%")
					->orWhere('t.mobile', 'like', "$search%")
					->orWhere('t.ondc_transaction_type_id', 'like', "$search%")
					->orWhere('t.product_category_id', 'like', "$search%")
					->orWhere('t.status', 'like', "$search%")
					->orWhere('u.first_name', 'like', "$search%")
					->orWhereRaw("DATE_FORMAT(t.created_at, '%d-%m-%Y') like ?", ["$search%"]);
			});
		}


		if (!empty($filters['review_status'])) {
			$query->where('t.status', $filters['review_status']);
		}

		if (!empty($filters['owner_role'])) {
			$query->where('t.role_type', $filters['owner_role']);
		}

		if (!empty($filters['from_dates'])) {
			$from = Carbon::createFromFormat('d-m-Y', $filters['from_dates'])->format('Y-m-d');
		}

		if (!empty($filters['to_dates'])) {
			$to = Carbon::createFromFormat('d-m-Y', $filters['to_dates'])->format('Y-m-d');
		}

		if (!empty($from) && !empty($to)) {
			$query->whereBetween('t.created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
		}
		/*elseif (!empty($from)) {
			$query->whereDate('t.created_at', '>=', $from);
		} elseif (!empty($to)) {
			$query->whereDate('t.created_at', '<=', $to);
		}*/

		$query->orderBy($orderColumn, $orderDir);

		if ($page) {
			return $this->getDataTableResult(
				MsmeBulkResource::collection($query->paginate($limit))
			);
		}

		return MsmeBulkResource::collection($query->get());
	}


	public function getEntityType(): array
	{
		return DB::table('attribute_values as av')
			->leftJoin('attributes as a', 'a.id', '=', 'av.attribute_id')
			->where('a.code', 'entity-type')
			->orderByRaw('LOWER(av.attribute_value) ASC')
			->pluck('av.attribute_value', 'av.id')
			->toArray();
	}

	public function associationRegistrationList()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
        $search ??= $this->escape_special_characters($search);

        $query = DB::table('industrial_associations as ia')
            ->join('attribute_values as av', 'ia.entity_type', '=', 'av.id')
            ->leftJoin('file_uploads as fu', 'ia.authorization_document_id', '=', 'fu.id')
            ->select(
                'ia.*',
                'av.attribute_value as entity_type',
                'fu.file_path as authorization_document_path'

            );

        if (!empty($filters['from_dates'])) {
            $from = Carbon::createFromFormat('d-m-Y', $filters['from_dates'])->format('Y-m-d');
        }

        if (!empty($filters['to_dates'])) {
            $to = Carbon::createFromFormat('d-m-Y', $filters['to_dates'])->format('Y-m-d');
        }

        if (!empty($from) && !empty($to)) {
            $query->whereBetween('ia.created_at', [$from, $to]);
        } 

		if (!empty($filters['review_status'])) {
			$query->where('ia.status', $filters['review_status']);
		}

		if (!empty($filters['state_id'])) {
			$query->where('ia.state_id', $filters['state_id']);
		}

		if (!empty($filters['entity_type'])) {
			$query->where('av.id', $filters['entity_type']);
		}
		
		/*elseif (!empty($from)) {
            $query->whereDate('ia.created_at', '>=', $from);
        } elseif (!empty($to)) {
            $query->whereDate('ia.created_at', '<=', $to);
        }*/

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('ia.organization_name', 'like', "%{$search}%")
                    ->orWhere('av.attribute_value', 'like', "%{$search}%")
                    ->orWhere('ia.entity_email', 'like', "%{$search}%")
                    ->orWhere('ia.contact_number', 'like', "%{$search}%")
                    ->orWhere('ia.registration_number', 'like', "%{$search}%")
                    ->orWhere('ia.website', 'like', "%{$search}%")
                    ->orWhere('ia.contact_person_name', 'like', "%{$search}%")
                    ->orWhere('ia.contact_person_phone', 'like', "%{$search}%")
                    ->orWhere('ia.contact_person_email', 'like', "%{$search}%")
                    ->orWhere('ia.created_at', 'like', "%{$search}%");
            });
        }

		$sortableColumns = [
			'id'                    => 'ia.id',
			'organization_name'     => 'ia.organization_name',
			'entity_type'           => 'av.attribute_value',
			'entity_email'          => 'ia.entity_email',
			'contact_number'        => 'ia.contact_number',
			'registration_number'   => 'ia.registration_number',
			'website'               => 'ia.website',
			'contact_person_name'   => 'ia.contact_person_name',
			'contact_person_phone'  => 'ia.contact_person_phone',
			'contact_person_email'  => 'ia.contact_person_email',
			'created_at'            => 'ia.created_at',
		];

		$orderColumn = $sortableColumns[$order] ?? 'ia.id';
		$direction   = strtolower($dir) === 'asc' ? 'asc' : 'desc';

		$query->orderBy($orderColumn, $direction);

        if ($page) {
            return $this->getDataTableResult(
                AssociationListResource::collection($query->paginate($limit))
            );
        }

        return AssociationListResource::collection($query->get());
    }


	public function iaViewDetails(string $id): AssociationListResource
	{
		$association = IndustrialAssociation::query()
			->from('industrial_associations as ia')
			->join('attribute_values as av', 'ia.entity_type', '=', 'av.id')
			->leftJoin('file_uploads as fu', 'ia.authorization_document_id', '=', 'fu.id')
			->leftJoin('states as st', 'st.id', '=', 'ia.state_id')
			->leftJoin('locations as dt', 'dt.id', '=', 'ia.district_id')
			->select(
				'ia.*',
				'av.attribute_value as entity_type',
				'st.name as state_name',
				'dt.name as district_name',
				'fu.file_path as authorization_document_path'
			)
			->where('ia.id', $id)
			->firstOrFail();

		return new AssociationListResource($association);
	}

    public function msmeStatus()
    {
        return [
            '' => 'Select',
            'open' => 'Open MSE',
            'direct' => 'Direct Selection by MSE',
            'onboarded' => 'Onboarded MSEs',
        ];
    }

	/*public function getSnpName()
	{
		return DB::table('team_snp_scheme as tss')
			->leftJoin('network_providers as np', 'np.id', '=', 'tss.network_provider_id')
			->leftJoin('users as u', 'u.id', '=', 'tss.user_id')
			->groupBy('snp_id','snp_name')
			//->where('tss.status', 1)
			->whereNotNull('tss.snp_id')
			->orderBy('tss.snp_name')
			->pluck('tss.organization_name', 'tss.id')
			->toArray();
	}*/


	public function getSnpName()
	{
		return DB::table('team_snp_scheme as tss')
			->join('users as u', 'u.id', '=', 'tss.user_id')
			->leftJoin('network_providers as np', 'np.id', '=', 'tss.network_provider_id')
			->whereNotNull('tss.snp_id')
			->selectRaw('MAX(tss.id) as id, u.first_name')
			->groupBy('u.id', 'u.first_name')
			->orderBy('u.first_name', 'asc')
			->pluck('u.first_name', 'id')
			->toArray();
	}


	public function getSnpWiseMsmeList()
	{
		[$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
		$search ??= $this->escape_special_characters($search);

		$type  = $filters['msme_status'] ?? null;
		$snpId = $filters['snp_id'] ?? null;


		/*if (!empty($snpId)) {

			$hasMapping = DB::table('team_snpmsme_mapping')
				->where('snp_id', $snpId)
				->exists();

			if (!$hasMapping) {

				if ($page) {
					return $this->getDataTableResult(
						MisReportResource::collection(collect()->paginate($limit))
					);
				}

				return MisReportResource::collection(collect());
			}
		}*/

		$query = DB::table('team_msme_schemes as ms')
			->select(
				'ms.id',
				'ms.user_id',
				'ms.udyam_no',
				'ms.gender',
				'ms.total_emp',
				'ms.net_investment_plant_machinery',
				'ms.incorporation_date',
				'ms.turnover',
				'sub.name as category_name',
				'ms.product_category_id',
				'ms.mobile',
				'ms.email',
				'ms.entrepreneur_name',
				'ms.enterprise_name',
				'ms.organisation_type',
				'ms.msme_classification',
				'ms.team_id',
				'ms.major_activity',
				'ms.social_category',
				'st.name as state_name',
				'ms.created_at',
				'ms.ondc_transaction_type_id',
				'av.attribute_value as transaction_type'
			)
			->leftJoin('states as st', 'st.id', '=', 'ms.state_id')
			->leftJoin('sub_domains as sub', 'sub.id', '=', 'ms.product_category_id')
			->leftJoin('attribute_values as av', 'av.id', '=', 'ms.ondc_transaction_type_id')
			->whereNotNull('ms.major_activity')
			->where('ms.major_activity', '!=', '');


		if (!empty($filters['from_dates']) && !empty($filters['to_dates'])) {
			$from = \Carbon\Carbon::createFromFormat('d-m-Y', $filters['from_dates'])->format('Y-m-d');
			$to   = \Carbon\Carbon::createFromFormat('d-m-Y', $filters['to_dates'])->format('Y-m-d');

			$query->whereBetween('ms.created_at', [$from, $to]);
		}

		// $filterWithSnp = false;
		$filterWithSnp =  !empty($snpId);;

		if (!empty($snpId)) {
			$filterWithSnp = true;
		}

		$filterWithSnp = in_array($type, ['direct', 'onboarded']);
		// $filterWithSnp = !empty($snpId) && in_array($type, ['direct', 'onboarded']);

		// if ($filterWithSnp) {
		// 	$query->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id');
		// }
		
		if (!empty($snpId) && in_array($type, ['direct', 'onboarded'])) {
			$query->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
				->where('tsm.snp_id', $snpId);
		}

		elseif (!empty($snpId) && empty($type)) {
			$query->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
				->where('tsm.snp_id', $snpId);
		}

		if (!empty($type)) {

			switch ($type) {

				case 'open':


				$query->where('ms.select_snp',0)
					->whereNull('ms.bpp_id');

				if (!empty($snpId)) {

					$snp = $this->getSnpDetail($snpId,'id');

					if ($snp) {

						$stateIds = json_decode($snp->state_id,true) ?: [];
						$txnIds   = json_decode($snp->transaction_type,true) ?: [];
						$subIds   = json_decode($snp->sub_domain,true) ?: [];

						$nationalId = '5bc85de0-0292-11f1-922a-00155d022d06';

						if (!in_array($nationalId,$stateIds) && !empty($stateIds)) {
							$query->whereIn('ms.state_id',$stateIds);
						}

						if (!empty($txnIds)) {
							$query->whereIn('ms.ondc_transaction_type_id',$txnIds);
						}

						if (!empty($subIds)) {
							$query->where(function($q) use ($subIds){
								foreach($subIds as $id){
									$q->orWhereJsonContains('ms.product_category_id',$id);
								}
							});
						}
					}
				}

				break;

				case 'direct':

					$query->where('ms.select_snp', 1)
						->where('tsm.status', 0);

					// if (!empty($snpId)) {
					// 	$query->where('tsm.snp_id', $snpId);
					// }

				break;

				case 'onboarded':

					$query->where('tsm.status', 1)
						->whereNotNull('ms.major_activity')
						->where('ms.major_activity', '!=', 'Trading')
						->whereNull('ms.is_catalogue_claim_generated');

					// if (!empty($snpId)) {
					// 	$query->where('tsm.snp_id', $snpId);
					// }

				break;
			}

		} 

		if ($search) {
			$query->where(function ($q) use ($search) {
				$q->where('ms.udyam_no', 'like', "%{$search}%")
				->orWhere('ms.team_id', 'like', "%{$search}%")
				->orWhere('ms.mobile', 'like', "%{$search}%")
				->orWhere('ms.email', 'like', "%{$search}%")
				->orWhere('ms.entrepreneur_name', 'like', "%{$search}%")
				->orWhere('ms.enterprise_name', 'like', "%{$search}%")
				->orWhere('ms.organisation_type', 'like', "%{$search}%")
				->orWhere('ms.social_category', 'like', "%{$search}%")
				->orWhere('st.name', 'like', "%{$search}%")
				->orWhere('ms.msme_classification', 'like', "%{$search}%")
				->orWhere('ms.incorporation_date', 'like', "%{$search}%")
				->orWhere('ms.total_emp', 'like', "%{$search}%")
				->orWhere('ms.gender', 'like', "%{$search}%")
				->orWhere('ms.turnover', 'like', "%{$search}%")
				->orWhere('av.attribute_value', 'like', "%{$search}%")
				->orWhereRaw("
						EXISTS (
							SELECT 1
							FROM sub_domains sud
							WHERE JSON_CONTAINS(
								ms.product_category_id,
								JSON_QUOTE(sud.id)
							)
							AND sud.name LIKE ?
						)
					", ["%{$search}%"])
				->orWhereRaw("DATE_FORMAT(ms.created_at, '%d-%m-%Y') LIKE ?", ["%{$search}%"]);
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
	

	public function getSnpDetail($id,$column = 'user_id')
    {
        $detail = DB::table('team_snp_scheme as snp')
            ->select('snp.id', 'snp.user_id', 'snp.sub_domain', 'snp.transaction_type', 'snp.state_id')
            ->where($column, $id)
            ->first();
        return $detail;
    }


}