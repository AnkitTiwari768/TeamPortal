<?php

declare(strict_types=1);

namespace App\Web\Dashboard;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

trait DashboardTrait
{

	public function getBlDashboardDetails($slug, $year = null, $fromDate = null, $toDate = null, $type = null)
	{
		return [
			'total_registered_sbl' => $this->getTotalRegisteredSbl($slug, $year, $fromDate, $toDate, $type),
			'total_pending_sbl'    => $this->getTotalPendingSbl($slug, $year, $fromDate, $toDate, $type),
			'total_verified_sbl'   => $this->getTotalVerifiedSbl($slug, $year, $fromDate, $toDate, $type),
			'total_rejected_sbl'   => $this->getTotalRejectedSbl($slug, $year, $fromDate, $toDate, $type),
			'total_reverted_sbl'   => $this->getTotalRevertedSbl($slug, $year, $fromDate, $toDate, $type),


		];
	}


	public function getTotalRegisteredSbl($slug, $year, $fromDate, $toDate, $type)
	{

		$roleId = $this->getRoleIdBySlug($slug);

		$total = DB::table('network_providers')->whereJsonContains('roles', $roleId);

		if (!empty($fromDate) && !empty($toDate)) {
			$total->whereBetween('created_at', [
				Carbon::parse($fromDate)->startOfDay(),
				Carbon::parse($toDate)->endOfDay(),
			]);
		} else {
			if ($type != 1) {

				$total->whereYear('created_at', $year);
			}
		}

		$total = $total->count();

		return $total;
	}


	public function getTotalPendingSbl($slug, $year, $fromDate, $toDate, $type)
	{
		$roleId = $this->getRoleIdBySlug($slug);

		$total = DB::table('network_providers')
			->whereJsonContains('roles', $roleId)
			->where('status', 1);


		if (!empty($fromDate) && !empty($toDate)) {

			$total->whereBetween('created_at', [
				Carbon::parse($fromDate)->startOfDay(),
				Carbon::parse($toDate)->endOfDay(),
			]);
		} else {

			if ($type != 1) {
				$total->whereYear('created_at', $year);
			}
		}

		$total = $total->count();

		return $total;
	}

	/* ---------------- VERIFIED ---------------- */

	public function getTotalVerifiedSbl($slug, $year, $fromDate, $toDate, $type)
	{
		$roleId = $this->getRoleIdBySlug($slug);

		$total = DB::table('network_providers')
			->whereJsonContains('roles', $roleId)
			->where('status', 2);

		if (!empty($fromDate) && !empty($toDate)) {

			$total->whereBetween('created_at', [
				Carbon::parse($fromDate)->startOfDay(),
				Carbon::parse($toDate)->endOfDay(),
			]);
		} else {

			if ($type != 1) {
				$total->whereYear('created_at', $year);
			}
		}
		$total = $total->count();

		return $total;
	}

	/* ---------------- REJECTED ---------------- */

	public function getTotalRejectedSbl($slug, $year, $fromDate, $toDate, $type)
	{


		$roleId = $this->getRoleIdBySlug($slug);

		$total = DB::table('network_providers')
			->whereJsonContains('roles', $roleId)
			->where('status', 3);

		if (!empty($fromDate) && !empty($toDate)) {

			$total->whereBetween('created_at', [
				Carbon::parse($fromDate)->startOfDay(),
				Carbon::parse($toDate)->endOfDay(),
			]);
		} else {

			if ($type != 1) {
				$total->whereYear('created_at', $year);
			}
		}

		$total = $total->count();


		return $total;
	}

	/* ---------------- REVERTED ---------------- */

	public function getTotalRevertedSbl($slug, $year, $fromDate, $toDate, $type)
	{
		$roleId = $this->getRoleIdBySlug($slug);

		$total = DB::table('network_providers')
			->whereJsonContains('roles', $roleId)
			->where('status', 4);

		if (!empty($fromDate) && !empty($toDate)) {
			$total->whereBetween('created_at', [
				Carbon::parse($fromDate)->startOfDay(),
				Carbon::parse($toDate)->endOfDay(),
			]);
		} else {
			if ($type != 1) {
				$total->whereYear('created_at', $year);
			}
		}

		$total = $total->count();

		return $total;
	}

	public function getRoleIdBySlug($slug)
	{
		return DB::table('roles')->where('slug', $slug)->value('id');
	}


	private function applyCommonFilters($query, $year, $fromDate, $toDate, $type, $createdBy)
	{
		// created_by filter
		if ($createdBy) {
			$query->where(function ($q) use ($createdBy) {
				$q->where('c.created_by', $createdBy);

				if (auth()->user()->parent_user_id) {
					$q->orWhere('c.created_by', auth()->user()->parent_user_id);
				}
			});
		}

		// date filters
		if ($fromDate && $toDate) {
			$query->whereBetween('c.created_at', [
				Carbon::parse($fromDate)->startOfDay(),
				Carbon::parse($toDate)->endOfDay(),
			]);
		} else {
			if ($type != 1 && $year) {
				$query->whereYear('c.created_at', $year);
			}
		}

		return $query;
	}

	private function batchBaseQuery($year, $fromDate, $toDate, $type, $createdBy)
	{
		$query = DB::table('dy_batches as b')
			->leftJoin('dy_batch_claims as bc', 'bc.batch_id', '=', 'b.id')
			->leftJoin('claims as c', 'c.id', '=', 'bc.claim_id');

		return $this->applyCommonFilters(
			$query,
			$year,
			$fromDate,
			$toDate,
			$type,
			$createdBy
		);
	}

	private function claimBaseQuery($year, $fromDate, $toDate, $type, $createdBy)
	{
		$query = DB::table('dy_batch_claims as bc')
			->join('claims as c', 'c.id', '=', 'bc.claim_id')
			->join('dy_batches as b', 'b.id', '=', 'bc.batch_id');

		return $this->applyCommonFilters(
			$query,
			$year,
			$fromDate,
			$toDate,
			$type,
			$createdBy
		);
	}

	private function amountBaseQuery($year, $fromDate, $toDate, $type, $createdBy)
	{
		$query = DB::table('claims as c')
			->join('dy_batch_claims as bc', 'bc.claim_id', '=', 'c.id')
			->join('dy_batches as b', 'b.id', '=', 'bc.batch_id');

		return $this->applyCommonFilters(
			$query,
			$year,
			$fromDate,
			$toDate,
			$type,
			$createdBy
		);
	}

	private function getAmount($query)
	{
		return $query->sum('c.amount') ?: 0;
	}

	public function getTotalSubmittedByNPs($year, $fromDate, $toDate, $type, $createdBy)
	{
		return $this->batchBaseQuery($year, $fromDate, $toDate, $type, $createdBy)
			->whereNull('b.deleted_at')
			->distinct('b.id')
			->count('b.id');
	}

	public function getTotalClaimsSubmittedByNPs($year, $fromDate, $toDate, $type, $createdBy)
	{
		return $this->claimBaseQuery($year, $fromDate, $toDate, $type, $createdBy)
			->where(function ($q) {
				$q->whereNull('bc.is_deleted')
					->orWhere('bc.is_deleted', 0);
			})
			->count();
	}

	public function getTotalBatchesPendingWithONDC($year, $fromDate, $toDate, $type, $createdBy)
	{
		return $this->batchBaseQuery($year, $fromDate, $toDate, $type, $createdBy)
			->where('b.status', 4)
			->distinct('b.id')
			->count('b.id');
	}

	public function getTotalClaimsPendingWithONDC($year, $fromDate, $toDate, $type, $createdBy)
	{
		return $this->claimBaseQuery($year, $fromDate, $toDate, $type, $createdBy)
			->whereIn('bc.status', [2, 4])
			->where(function ($q) {
				$q->whereNull('bc.is_deleted')
					->orWhere('bc.is_deleted', 0);
			})
			->count();
	}

	public function getTotalBatchesPendingWithNSIC($year, $fromDate, $toDate, $type, $createdBy)
	{
		return $this->batchBaseQuery($year, $fromDate, $toDate, $type, $createdBy)
			->whereIn('b.status', [7, 15])
			->distinct('b.id')
			->count('b.id');
	}

	public function getTotalClaimsPendingWithNSIC($year, $fromDate, $toDate, $type, $createdBy)
	{
		return $this->claimBaseQuery($year, $fromDate, $toDate, $type, $createdBy)
			->whereIn('bc.status', [7, 15])
			->where(function ($q) {
				$q->whereNull('bc.is_deleted')
					->orWhere('bc.is_deleted', 0);
			})
			->count();
	}

	public function getTotalBatchesPendingWithNPs($year, $fromDate, $toDate, $type, $createdBy)
	{
		return $this->batchBaseQuery($year, $fromDate, $toDate, $type, $createdBy)
			->where('b.status', 13)
			->distinct('b.id')
			->count('b.id');
	}

	public function getTotalClaimsPendingWithNPs($year, $fromDate, $toDate, $type, $createdBy)
	{
		return $this->claimBaseQuery($year, $fromDate, $toDate, $type, $createdBy)
			->where('bc.status', 13)
			->where(function ($q) {
				$q->whereNull('bc.is_deleted')
					->orWhere('bc.is_deleted', 0);
			})
			->count();
	}

	public function getTotalBatchesPendingWithFinance($year, $fromDate, $toDate, $type, $createdBy)
	{
		return $this->batchBaseQuery($year, $fromDate, $toDate, $type, $createdBy)
			->where('b.status', 16)
			->distinct('b.id')
			->count('b.id');
	}

	public function getTotalBatchesPending($year, $fromDate, $toDate, $type, $createdBy)
	{
		return $this->batchBaseQuery($year, $fromDate, $toDate, $type, $createdBy)
			->whereIn('b.status', [2, 4, 7, 13, 15, 16])
			->distinct('b.id')
			->count('b.id');
	}

	public function getTotalClaimsPendingWithFinance($year, $fromDate, $toDate, $type, $createdBy)
	{
		return $this->claimBaseQuery($year, $fromDate, $toDate, $type, $createdBy)
			->where('bc.status', 16)
			->where(function ($q) {
				$q->whereNull('bc.is_deleted')
					->orWhere('bc.is_deleted', 0);
			})
			->count();
	}

	public function getTotalClaimsPending($year, $fromDate, $toDate, $type, $createdBy)
	{
		return $this->claimBaseQuery($year, $fromDate, $toDate, $type, $createdBy)
			->whereIn('bc.status', [2, 4, 7, 13, 15, 16])
			->where(function ($q) {
				$q->whereNull('bc.is_deleted')
					->orWhere('bc.is_deleted', 0);
			})
			->count();
	}

	public function getTotalApprovedBatches($year, $fromDate, $toDate, $type, $createdBy)
	{
		return $this->batchBaseQuery($year, $fromDate, $toDate, $type, $createdBy)
			->where('b.status', 17)
			->distinct('b.id')
			->count('b.id');
	}

	public function getTotalPaymentCompletedBatches($year, $fromDate, $toDate, $type, $createdBy)
	{
		return $this->batchBaseQuery($year, $fromDate, $toDate, $type, $createdBy)
			->where('b.status', 19)
			->distinct('b.id')
			->count('b.id');
	}

	public function getTotalApprovedClaims($year, $fromDate, $toDate, $type, $createdBy)
	{
		return $this->claimBaseQuery($year, $fromDate, $toDate, $type, $createdBy)
			->where('bc.status', 17)
			->where(function ($q) {
				$q->whereNull('bc.is_deleted')
					->orWhere('bc.is_deleted', 0);
			})
			->count();
	}

	public function getTotalPaymentCompletedClaims($year, $fromDate, $toDate, $type, $createdBy)
	{
		return $this->claimBaseQuery($year, $fromDate, $toDate, $type, $createdBy)
			->where('bc.status', 19)
			->where(function ($q) {
				$q->whereNull('bc.is_deleted')
					->orWhere('bc.is_deleted', 0);
			})
			->count();
	}

	public function getTotalRejectedBatches($year, $fromDate, $toDate, $type, $createdBy)
	{
		return $this->batchBaseQuery($year, $fromDate, $toDate, $type, $createdBy)
			->whereIn('b.status', [18, 9, 6])
			->distinct('b.id')
			->count('b.id');
	}

	public function getTotalRejectedClaims($year, $fromDate, $toDate, $type, $createdBy)
	{
		return $this->claimBaseQuery($year, $fromDate, $toDate, $type, $createdBy)
			->whereIn('bc.status', [18, 9, 6])
			->where('bc.is_deleted', 1)
			->count();
	}

	public function getTotalAmountSubmittedByNPs(
		$year,
		$fromDate,
		$toDate,
		$type,
		$createdBy
	) {
		return $this->getAmount(
			$this->amountBaseQuery(
				$year,
				$fromDate,
				$toDate,
				$type,
				$createdBy
			)
				->whereNull('b.deleted_at')
				->where(function ($q) {
					$q->whereNull('bc.is_deleted')
						->orWhere('bc.is_deleted', 0);
				})
		);
	}

	public function getTotalAmountPendingWithNPs(
		$year,
		$fromDate,
		$toDate,
		$type,
		$createdBy
	) {
		return $this->getAmount(
			$this->amountBaseQuery(
				$year,
				$fromDate,
				$toDate,
				$type,
				$createdBy
			)
				->where('bc.status', 13)
				->whereNull('b.deleted_at')
				->where(function ($q) {
					$q->whereNull('bc.is_deleted')
						->orWhere('bc.is_deleted', 0);
				})
		);
	}

	public function getTotalAmountPendingWithONDC(
		$year,
		$fromDate,
		$toDate,
		$type,
		$createdBy
	) {
		return $this->getAmount(
			$this->amountBaseQuery(
				$year,
				$fromDate,
				$toDate,
				$type,
				$createdBy
			)
				->whereNull('b.deleted_at')
				->where('b.status', 4)
				->whereIn('bc.status', [2, 4])
				->where(function ($q) {
					$q->whereNull('bc.is_deleted')
						->orWhere('bc.is_deleted', 0);
				})
		);
	}

	public function getTotalAmountPendingWithNSIC(
		$year,
		$fromDate,
		$toDate,
		$type,
		$createdBy
	) {
		return $this->getAmount(
			$this->amountBaseQuery(
				$year,
				$fromDate,
				$toDate,
				$type,
				$createdBy
			)
				->whereNull('b.deleted_at')
				->whereIn('b.status', [7, 15])
				->whereIn('bc.status', [7, 15])
				->where(function ($q) {
					$q->whereNull('bc.is_deleted')
						->orWhere('bc.is_deleted', 0);
				})
		);
	}

	public function getTotalAmountPending(
		$year,
		$fromDate,
		$toDate,
		$type,
		$createdBy
	) {
		return $this->getAmount(
			$this->amountBaseQuery(
				$year,
				$fromDate,
				$toDate,
				$type,
				$createdBy
			)
				->whereNull('b.deleted_at')
				->whereIn('b.status', [2, 4, 7, 13, 15, 16])
				->whereIn('bc.status',  [2, 4, 7, 13, 15, 16])
				->where(function ($q) {
					$q->whereNull('bc.is_deleted')
						->orWhere('bc.is_deleted', 0);
				})
		);
	}

	public function getTotalAmountPendingWithFinance(
		$year,
		$fromDate,
		$toDate,
		$type,
		$createdBy
	) {
		return $this->getAmount(
			$this->amountBaseQuery(
				$year,
				$fromDate,
				$toDate,
				$type,
				$createdBy
			)
				->whereNull('b.deleted_at')
				->where('b.status', 16)
				->where('bc.status', 16)
				->where(function ($q) {
					$q->whereNull('bc.is_deleted')
						->orWhere('bc.is_deleted', 0);
				})
		);
	}

	public function getTotalApprovedAmount(
		$year,
		$fromDate,
		$toDate,
		$type,
		$createdBy
	) {
		return $this->getAmount(
			$this->amountBaseQuery(
				$year,
				$fromDate,
				$toDate,
				$type,
				$createdBy
			)
				->whereNull('b.deleted_at')
				->where('b.status', 17)
				->where('bc.status', 17)
				->where(function ($q) {
					$q->whereNull('bc.is_deleted')
						->orWhere('bc.is_deleted', 0);
				})
		);
	}

	public function getTotalPaymentCompletedAmount(
		$year,
		$fromDate,
		$toDate,
		$type,
		$createdBy
	) {
		return $this->getAmount(
			$this->amountBaseQuery(
				$year,
				$fromDate,
				$toDate,
				$type,
				$createdBy
			)
				->whereNull('b.deleted_at')
				->where('b.status', 19)
				->where('bc.status', 19)
				->where(function ($q) {
					$q->whereNull('bc.is_deleted')
						->orWhere('bc.is_deleted', 0);
				})
		);
	}

	public function getTotalRejectedAmount(
		$year,
		$fromDate,
		$toDate,
		$type,
		$createdBy
	) {
		return $this->getAmount(
			$this->amountBaseQuery(
				$year,
				$fromDate,
				$toDate,
				$type,
				$createdBy
			)
				->whereNotNull('bc.deleted_at')
				->where('bc.is_deleted', 1)
				->whereIn('bc.status', [18, 9, 6])
		);
	}
}
