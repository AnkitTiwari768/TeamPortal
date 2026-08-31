<?php

declare(strict_types=1);

namespace App\Web\BatchWorkflow;

use App\Web\Claim\ClaimReviewStatus;
use Illuminate\Support\Facades\DB;

class ProcessBySnpToNsic
{
	use HasTimeline;

	public function execute(array $data)
	{
		DB::transaction(function () use ($data) {

			$batchId = $data['batch_id'];
			$claimId = $data['claim_id'];

			DB::table('claims')->where('id', $claimId)->update([
				'status' => ClaimReviewStatus::PENDING->value,
				'review_status' => ClaimReviewStatus::PENDING->value,
				'is_edited' => 0,
				'nsic_review_status' => ClaimReviewStatus::PENDING->value
			]);

			$updatedBatchClaims = ['nsic_review_status' => ClaimReviewStatus::PENDING->value];

			if (hasRole('snp')) {
				$updatedBatchClaims += [
					'status_id' => ClaimReviewStatus::PENDING->value,
					'is_revert_to_snp' => false
				];


				$updatedBatch = [];

				$snpCount = $this->getSNPRevertedCount($batchId);

				if ($snpCount == 1) {
					$updatedBatch += ['is_reverted_to_snp' => false];
				}

				$updatedBatch += ['is_resend_to_nsic' => true];

				$this->createTimeline([
					'batch_id' => $batchId,
					'comments' => $data['comments'],
					'status' => 'resend',
					'claims' => $this->createBatchClaimTimelineDetails(
						batchId: $batchId,
						claimIds: [$claimId],
						status: 'resend'
					)
				]);
			}

			if (hasRole('ondc-admin')) {
				$updatedBatchClaims += [
					'status_id' => ClaimReviewStatus::PENDING->value,
					'is_revert_to_ondc' => false
				];


				$updatedBatch = [];

				$ondcCount = $this->getONDCRevertedCount($batchId);

				if ($ondcCount == 1) {
					$updatedBatch += ['is_reverted_to_ondc' => false];
				}

				$updatedBatch += ['is_resend_to_nsic' => true];

				$this->createTimeline([
					'batch_id' => $batchId,
					'comments' => $data['comments'],
					'status' => 'resend',
					'claims' => $this->createBatchClaimTimelineDetails(
						batchId: $batchId,
						claimIds: [$claimId],
						status: 'resend'
					)
				]);
			}


			$roleAction = $this->isBatchActionTakenByNSICRole($batchId);
			if ($roleAction == 0) {
				DB::table('batch_review_statuses')
					->where('batch_id', $batchId)
					->where('role_id', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58') // nsic role id
					->update(['status_id' => ClaimReviewStatus::PENDING->value]);
			}

			if ($roleAction == 1) {
				//$updatedBatch += ['is_resend_to_nsic' => true];
			}


			DB::table('batch_claims')
				->where('batch_id', $batchId)
				->where('claim_id', $claimId)
				->update($updatedBatchClaims);


			if (!empty($updatedBatch))
				DB::table('batches')->where('id', $batchId)->update($updatedBatch);
		});
	}

	public function isBatchActionTakenByNSICRole($batchId)
	{
		return DB::table('batch_review_statuses')
			->where('batch_id', $batchId)
			->whereIn('status_id', [ClaimReviewStatus::APPROVED->value])
			->where('role_id', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58')  //nsic role id
			->count();
	}



	public function getSNPRevertedCount($batchId)
	{
		return DB::table('batch_claims')->where('batch_id', $batchId)
			->where('status_id', ClaimReviewStatus::REVERTED->value)
			->where('is_revert_to_snp', 1)
			->count();
	}


	public function getONDCRevertedCount($batchId)
	{
		return DB::table('batch_claims')->where('batch_id', $batchId)
			->where('status_id', ClaimReviewStatus::REVERTED->value)
			->where('is_revert_to_ondc', 1)
			->count();
	}
}
