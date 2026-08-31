<?php

declare(strict_types=1);

namespace App\Web\Batch;

use App\Traits\DataTable;
use App\Core\BaseService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Web\Claim\ClaimReviewStatus;
use App\Web\ApplicationWorkflow\WorkflowType;
use Carbon\Carbon;
use DB;
use App\Web\BatchWorkflow\BatchClaimWorkflowService;
use App\Traits\HasFileUpload;
use App\Traits\TimelineGenerator;
use App\Web\BatchWorkflow\HasTimeline;

class BatchService extends BaseService
{
    use HasFileUpload, TimelineGenerator, HasTimeline;

    protected BatchClaimWorkflowService $batchClaimWorkflowService;

    public function __construct(BatchClaimWorkflowService $batchClaimWorkflowService)
    {
        $this->batchClaimWorkflowService = $batchClaimWorkflowService;
    }

    public function generateBatchNumber($year, $month, $claimType): string
    {
        $snpId = getSnpId();
        $day   = now()->format('d');

        return $snpId
            . $claimType
            . $year
            . $day
            . str_pad($month, 2, '0', STR_PAD_LEFT)
            . rand(1000, 9999);
    }

    public function getClaimTypeIdBySlug(string $slug): object
    {
        $claimType = DB::table('claim_types')->where('slug', $slug)->first();
        if (!$claimType) {
            throw new \Exception("Claim type with slug '$slug' does not exist.");
        }
        return $claimType;
    }

    public function getApplicationWorkflow(WorkflowType $workflowType, int $level): mixed
    {
        return DB::table('workflows')
            ->select('workflows.*', 'workflow_types.id as workflow_type_id')
            ->join('workflow_types', 'workflows.workflow_type_id', '=', 'workflow_types.id')
            ->where('workflow_types.slug', $workflowType->value)
            ->where('level', $level)
            ->where('workflows.status', 1)
            ->first();
    }

    public function store(array $payload): bool
    {

        return DB::transaction(function () use ($payload) {
            $batchId = uuid();

            $today = Carbon::now();

            // Calculate current month and financial year
            $month = $today->format('m');
            $year  = ($today->month >= 4)
                ? $today->year . '-' . ($today->year + 1)
                : ($today->year - 1) . '-' . $today->year;

            $claimTypeDetails = $this->getClaimTypeIdBySlug($payload['claim_type_id']);

            $batchNumber = $this->generateBatchNumber($year, $month, $claimTypeDetails->short_name);
            //Insert into batch
            DB::table('batches')->insert([
                'id'              => $batchId,
                'claim_type_id'   => $claimTypeDetails->id,
                'financial_year'  => $year,
                'month'           => $month,
                'batch_number'    => $batchNumber,
                'is_sent_ca'      => 1,
                'status_id'       => ClaimReviewStatus::PENDING->value,
                'submitted_at'    => now(),
                'submitted_by'    => now(),
                'created_by'      => AuthId(),
                'created_at'      => now(),
            ]);

            // Insert into batch_workflow
            //get data from workflows
            $nextWorkflow = $this->getApplicationWorkflow(WorkflowType::CLAIM_CATALOGUE, 1);
            //dd($nextWorkflow);
            //get CA user of current snp
            $caId = DB::table('team_snpca_mapping')->select('ca_user_id')->where('snp_user_id', AuthId())->first();
            //dd($caId);
            DB::table('batch_workflow')->insert([
                'id'         => uuid(),
                'batch_id'   => $batchId,
                'workflow_id' => $nextWorkflow->id,
                'workflow_level' => $nextWorkflow->level,
                'from_role_id'   => authRoleId(),
                'to_role_id'   => $nextWorkflow->role_id,
                'from_user_id' => AuthId(),
                'to_user_id' => $caId->ca_user_id,
                'is_declaration_agreed' => isset($payload['is_declaration_agreed']) ? $payload['is_declaration_agreed'] : NULL,
                'status_id'  => ClaimReviewStatus::PENDING->value,
                'created_by' => AuthId(),
                'created_at' => now(),
            ]);
            // Insert into batch_review_statuses

            //dd($nextWorkflow);
            //get CA user of current snp
            $caId = DB::table('team_snpca_mapping')->select('ca_user_id')->where('snp_user_id', AuthId())->first();
            //dd($caId);
            DB::table('batch_review_statuses')->insert([
                'id'         => uuid(),
                'batch_id'   => $batchId,
                'role_id'   => $nextWorkflow->role_id,
                'user_id' => $caId->ca_user_id,
                'comments' => 'SNP To CA',
                'status_id'  => ClaimReviewStatus::PENDING->value,
                'created_by' => AuthId(),
                'created_at' => now(),
            ]);

            // Insert into batch_claims
            foreach ($payload['claim_id'] as $claimId) {
                DB::table('batch_claims')->insert([
                    'id'         => uuid(),
                    'batch_id'   => $batchId,
                    'claim_id'   => $claimId,
                    'status_id'  => ClaimReviewStatus::PENDING->value,
                    'ca_review_status' => ClaimReviewStatus::PENDING->value,
                    'ca_review_status_updated_at' => now(),
                    'ca_review_status_updated_by' => AuthId(),
                    'created_by' => AuthId(),
                    'created_at' => now(),
                ]);
                // Insert into batch_claim_histories

                DB::table('batch_claim_histories')->insert([
                    'id'         => uuid(),
                    'batch_id'   => $batchId,
                    'claim_id'   => $claimId,
                    'status_id'  => ClaimReviewStatus::PENDING->value,
                    'created_by' => AuthId(),
                    'created_at' => now(),
                ]);

                // Insert into claim_review_statuses

                DB::table('claim_review_statuses')->insert([
                    'id'         => uuid(),
                    'batch_id'   => $batchId,
                    'claim_id'   => $claimId,
                    'role_id'    => $nextWorkflow->role_id,
                    'user_id'    => $caId->ca_user_id,
                    'status_id'  => ClaimReviewStatus::PENDING->value,
                    'created_by' => AuthId(),
                    'created_at' => now(),
                ]);

                // Insert into batch_claim_workflow

                DB::table('batch_claim_workflow')->insert([
                    'id'         => uuid(),
                    'batch_id'   => $batchId,
                    'claim_id'   => $claimId,
                    'from_role_id'    => authRoleId(),
                    'to_role_id'  => $nextWorkflow->role_id,
                    'from_user_id'    => AuthId(),
                    'to_user_id' => $caId->ca_user_id,
                    'status_id'  => ClaimReviewStatus::PENDING->value,
                    'comments' => 'SNP To CA',
                    'created_by' => AuthId(),
                    'created_at' => now(),
                ]);

                // Update into claims table
                DB::table('claims')->where('id', $claimId)->update(
                    [
                        'is_sent_ca' => 1,
                        'status' => ClaimReviewStatus::PENDING->value,
                        'review_status' => ClaimReviewStatus::PENDING->value,
                        'review_status_updated_by' => AuthId(),
                        'review_status_updated_at' => now(),
                        'ca_review_status' => ClaimReviewStatus::PENDING->value,
                        'ca_review_status_updated_at' => now(),
                        'ca_review_status_updated_by' => AuthId(),
                        'is_batch' => 1
                    ]
                );
            }

            $this->createTimeline([
                'batch_id' => $batchId,
                'comments' => 'Batch created and sent to CA',
                'status' => 'created',
                'is_shown_to_ca' => true,
                'claims' => $this->createBatchClaimTimelineDetails(
                    batchId: $batchId,
                    claimIds: $payload['claim_id'],
                    status: 'submitted'
                )
            ]);


            return true;
        });
    }

    public function getRevertedClaims($batchId)
    {
        /*return DB::table('batch_claims')
            ->select('claim_id')
            ->where('batch_id', $batchId)
            ->where('status_id',ClaimReviewStatus::REVERTED->value)
            ->get();*/
        return DB::table('batch_claims')
            ->where('batch_id', $batchId)
            ->pluck('claim_id');
    }

    public function updateBatch(array $payload): bool
    {

        return DB::transaction(function () use ($payload) {
            $batchId = $payload['batch_id'];
            //get data from workflows
            $nextWorkflow = $this->getApplicationWorkflow(WorkflowType::CLAIM_CATALOGUE, 1);
            //get CA user of current snp
            $caId = DB::table('team_snpca_mapping')->select('ca_user_id')->where('snp_user_id', AuthId())->first();

            //Get reverted claims for which snp is resending to ca
            $claims = $this->getRevertedClaims($batchId);



            foreach ($claims as $claimId) {
                //$claimId = $claim->claim_id; 

                $is_edited = DB::table('claims')
                    ->select('is_edited')
                    ->where('id', $claimId)
                    ->first()->is_edited;
                //dd($is_edited);
                if ($is_edited === 1) {
                    // Insert into batch_claim_workflow
                    DB::table('batch_claim_workflow')->insert([
                        'id'         => uuid(),
                        'batch_id'   => $batchId,
                        'claim_id'   => $claimId,
                        'from_role_id'    => authRoleId(),
                        'to_role_id'  => $nextWorkflow->role_id,
                        'from_user_id'    => AuthId(),
                        'to_user_id' => $caId->ca_user_id,
                        'status_id'  => ClaimReviewStatus::PENDING->value,
                        'comments' => 'SNP To CA',
                        'created_by' => AuthId(),
                        'created_at' => now(),
                    ]);
                    //update claims table
                    DB::table('claims')->where('id', $claimId)->update(
                        [
                            'status' => ClaimReviewStatus::PENDING->value,
                            'review_status' => ClaimReviewStatus::PENDING->value,
                            'review_status_updated_by' => AuthId(),
                            'review_status_updated_at' => now(),
                            'ca_review_status' => ClaimReviewStatus::PENDING->value,
                            'ca_review_status_updated_at' => now(),
                            'ca_review_status_updated_by' => AuthId(),
                            'is_edited' => NULL
                        ]
                    );
                }
            }
            // Update into batches table
            DB::table('batches')->where('id', $batchId)->update(
                [
                    'status_id' => ClaimReviewStatus::PENDING->value,
                    'updated_by' => AuthId(),
                    'updated_at' => now(),
                ]
            );

            // Update into batch_claims table
            DB::table('batch_claims')->where('batch_id', $batchId)->where('status_id', ClaimReviewStatus::REVERTED->value)->update(
                [
                    'status_id' => ClaimReviewStatus::PENDING->value,
                    'ca_review_status' => ClaimReviewStatus::PENDING->value,
                    'ca_review_status_updated_at' => now(),
                    'ca_review_status_updated_by' => AuthId(),
                    'updated_by' => AuthId(),
                    'updated_at' => now(),
                ]
            );

            // Update into batch_review_statuses
            DB::table('batch_review_statuses')->where('batch_id', $batchId)
                ->where('role_id', $nextWorkflow->role_id)
                ->where('user_id', $caId->ca_user_id)
                ->update([
                    'status_id'  => ClaimReviewStatus::PENDING->value,
                    'updated_at' => now(),
                    'updated_by' => AuthId(),
                ]);

            // Insert into batch_workflow

            DB::table('batch_workflow')->insert([
                'id'         => uuid(),
                'batch_id'   => $batchId,
                'workflow_id' => $nextWorkflow->id,
                'workflow_level' => $nextWorkflow->level,
                'from_role_id'   => authRoleId(),
                'to_role_id'   => $nextWorkflow->role_id,
                'from_user_id' => AuthId(),
                'to_user_id' => $caId->ca_user_id,
                'is_declaration_agreed' => isset($payload['is_declaration_agreed']) ? $payload['is_declaration_agreed'] : NULL,
                'status_id'  => ClaimReviewStatus::PENDING->value,
                'created_by' => AuthId(),
                'created_at' => now(),
            ]);


            $this->createTimeline([
                'batch_id' => $batchId,
                'comments' => $payload['comments'],
                'status' => 'resend',
                'is_shown_to_ca' => true,
                'claims' => $this->createBatchClaimTimelineDetails(
                    batchId: $batchId,
                    claimIds: $claims->toArray(),
                    status: 're-submitted'
                )
            ]);

            return true;
        });
    }

    public function getAllClaims($batchId)
    {
        return DB::table('batch_claims')
            ->where('batch_id', $batchId)
            ->pluck('claim_id')
            ->toArray();
    }

    public function sendBatchToONDC(array $payload)
    {
        return DB::transaction(function () use ($payload) {

            $batchId = $payload['batch_id'];

            $workflowTypeId = $this->batchClaimWorkflowService->getWorkflowTypeId(WorkflowType::CLAIM_CATALOGUE);

            $nextWorkflow = $this->batchClaimWorkflowService->getNextBatchWorkflow(AuthId(), $workflowTypeId);

            $claims = DB::table('batch_claims')
                ->where('batch_id', $batchId)
                ->where('ca_review_status', ClaimReviewStatus::APPROVED->value)
                ->pluck('claim_id')
                ->toArray();

            $batchWorkflow = $batchReviewStatuses = $batchClaimWorkflow = $updatedClaims = [];

            $updatedBatch = [
                'is_sent_ondc' => 1,
                'updated_by' => AuthId(),
                'updated_at' => now(),
            ];

            $batchDocuments = [
                'id' => uuid(),
                'batch_id' => $batchId,
                'document_category_id' => $payload['document_category_id'],
                'file_upload_id' => static::getFileUploadIdBySystemName($payload['file_upload_id']),
                'status' => true,
                'uploaded_at' => now(),
                'uploaded_by' => AuthId()
            ];

            foreach ($nextWorkflow as $workflow) {
                $batchWorkflow[] = [
                    'id'         => uuid(),
                    'batch_id'   => $batchId,
                    'workflow_id' => $workflow->id,
                    'workflow_level' => $workflow->level,
                    'from_role_id'   => authRoleId(),
                    'to_role_id'   => $workflow->role_id,
                    'from_user_id' => AuthId(),
                    'to_user_id' => '',
                    'is_declaration_agreed' => 1,
                    'status_id'  => ClaimReviewStatus::PENDING->value,
                    'created_by' => AuthId(),
                    'created_at' => now(),
                ];

                $batchReviewStatuses[] = [
                    'id'         => uuid(),
                    'batch_id'   => $batchId,
                    'role_id'   => $workflow->role_id,
                    'user_id' => '',
                    'comments' => 'SNP To ONDC',
                    'status_id'  => ClaimReviewStatus::PENDING->value,
                    'created_by' => AuthId(),
                    'created_at' => now(),
                ];

                foreach ($claims as $claimId) {
                    $batchClaimWorkflow[] = [
                        'id'         => uuid(),
                        'batch_id'   => $batchId,
                        'claim_id'   => $claimId,
                        'from_role_id'    => authRoleId(),
                        'to_role_id'  => $workflow->role_id,
                        'from_user_id'    => AuthId(),
                        'to_user_id' => '',
                        'status_id'  => ClaimReviewStatus::PENDING->value,
                        'comments' => 'SNP To ONDC',
                        'created_by' => AuthId(),
                        'created_at' => now(),
                    ];
                }
            }

            $updatedClaims = [
                'ondc_review_status' => ClaimReviewStatus::PENDING->value,
                'ondc_review_status_updated_at' => now(),
                'ondc_review_status_updated_by' => AuthId(),
                'is_sent_ondc' => 1
            ];

            $updatedBatchClaims = [
                'ondc_review_status' => ClaimReviewStatus::PENDING->value,
                'ondc_review_status_updated_at' => now(),
                'ondc_review_status_updated_by' => AuthId()
            ];



            /**
             * Forward to ONDC Transaction goes here
             */
            DB::table('batches')->where('id', $batchId)->update($updatedBatch);
            DB::table('batch_workflow')->insert($batchWorkflow);
            DB::table('batch_review_statuses')->insert($batchReviewStatuses);
            DB::table('batch_documents')->insert($batchDocuments);
            DB::table('batch_claim_workflow')->insert($batchClaimWorkflow);
            DB::table('claims')->whereIn('id', $claims)->update($updatedClaims);
            DB::table('batch_claims')->whereIn('claim_id', $claims)->where('batch_id', $batchId)->update($updatedBatchClaims);

            $this->createTimeline([
                'batch_id' => $batchId,
                'comments' => $payload['comments'],
                'status' => 'forwarded',
                'claims' => $this->createBatchClaimTimelineDetails(
                    batchId: $batchId,
                    claimIds: $claims,
                    status: 'forwarded'
                )
            ]);
        });
    }

    public function moveToDrafts($payload)
    {
        $batchId = $payload['batch_id'];
        $claimId = $payload['claim_id'];
        $reviewStatus = $payload['review_status'];
        $reviewStatus = ClaimReviewStatus::getIdByName($reviewStatus);

        return DB::transaction(function () use ($batchId, $claimId, $reviewStatus) {

            $batchDetails = DB::table('batches')
                ->select('is_reverted_by_ondc', 'is_rejected_by_ondc', 'is_rejected_to_snp', 'is_reverted_by_ca')
                ->where('id', $batchId)
                ->first();

            $updatedClaims = [
                'status' => ClaimReviewStatus::DRAFT->value,
                'review_status' => NULL,
                'ca_review_status' => NULL,
                'ondc_review_status' => NULL,
                'nsic_review_status' => NULL,
                'nsicfinance_review_status' => NULL,
                'is_sent_ca' => NULL,
                'is_sent_ondc' => NULL,
                'is_sent_nsic' => NULL,
                'is_sent_nsicfinance' => NULL,
                'is_batch' => NULL,
                'is_edited' => NULL
            ];

            if ($reviewStatus === ClaimReviewStatus::REVERTED->value && $batchDetails->is_reverted_by_ondc) {

                $revertedCount = DB::table('batch_claims')->where('batch_id', $batchId)->where('status_id', ClaimReviewStatus::REVERTED->value)->where('is_revert_to_snp', 0)->where('is_deleted', 1)->whereNull('is_moved_to_drafts')->get()->count();

                if ($revertedCount && $revertedCount == 1) {
                    $updatedBatch = [
                        //'is_reverted_by_ondc'=> NULL,
                        'is_moved_to_draft_for_reverted' => 1,
                        'updated_by' => AuthId(),
                        'updated_at' => now(),
                    ];
                    DB::table('batches')->where('id', $batchId)->update($updatedBatch);
                }
            }

            if ($reviewStatus === ClaimReviewStatus::REVERTED->value && $batchDetails->is_reverted_by_ca) {

                $revertedCount = DB::table('batch_claims')->where('batch_id', $batchId)->where('status_id', ClaimReviewStatus::REVERTED->value)->where('is_revert_by_ca', 1)->where('is_deleted', 1)->whereNull('is_moved_to_drafts')->get()->count();

                if ($revertedCount && $revertedCount == 1) {
                    $updatedBatch = [
                        //'is_reverted_by_ondc'=> NULL,
                        'is_moved_to_draft_for_ca' => 1,
                        'updated_by' => AuthId(),
                        'updated_at' => now(),
                    ];
                    DB::table('batches')->where('id', $batchId)->update($updatedBatch);
                }
            }

            //For rejected case
            if ($reviewStatus === ClaimReviewStatus::REJECTED->value) {
                $rejectedCount = DB::table('batch_claims')->where('batch_id', $batchId)->where('status_id', ClaimReviewStatus::REJECTED->value)->where('is_deleted', 1)->whereNull('is_moved_to_drafts')->get()->count();

                if ($rejectedCount && $rejectedCount == 1) {
                    $updatedBatch = [
                        //'is_rejected_by_ondc'=> NULL,
                        'is_moved_to_draft_for_rejected' => 1,
                        'updated_by' => AuthId(),
                        'updated_at' => now(),
                    ];
                    DB::table('batches')->where('id', $batchId)->update($updatedBatch);
                }
            }

            DB::table('claims')->where('id', $claimId)->update($updatedClaims);
            DB::table('batch_claims')->where('claim_id', $claimId)->update(['is_moved_to_drafts' => 1]);

            /*if($reviewStatus === ClaimReviewStatus::REJECTED)
            {
                $batchClaimDetails = DB::table('batch_claims')->where('claim_id', $claimId)->first();



                if($batchClaimDetails->is_reject_to_snp == 0)
                {
                    $rejectedByOndcCount = DB::table('batch_claims')->where('batch_id', $batchId)->where('status_id',ClaimReviewStatus::REJECTED->value)->where('is_reject_to_snp', 0)->where('is_deleted',1)->get()->count();
                    
                    if($rejectedByOndcCount && $rejectedByOndcCount == 1){
                        $updatedBatch = [
                            //'is_rejected_by_ondc'=> NULL,
                            'updated_by' => AuthId(),
                            'updated_at' => now(),
                        ];
                        DB::table('batches')->where('id', $batchId)->update($updatedBatch);
                    }
                }else{
                    $rejectedByNsicCount = DB::table('batch_claims')->where('batch_id', $batchId)->where('status_id',ClaimReviewStatus::REJECTED->value)->where('is_reject_to_snp', 1)->where('is_deleted',1)->get()->count();
                
                    if($rejectedByNsicCount && $rejectedByNsicCount == 1){
                        $updatedBatch = [
                            //'is_rejected_to_snp'=> NULL,
                            'updated_by' => AuthId(),
                            'updated_at' => now(),
                        ];
                        DB::table('batches')->where('id', $batchId)->update($updatedBatch);
                    }
                }

            }*/
        });



        //DB::table('batch_claims')->where('claim_id', $claimId)->where('batch_id', $batchId)->update;

        /*$batchId = $payload['batch_id'];
        $reviewStatus = $payload['review_status'];
        $reviewStatus = ClaimReviewStatus::getIdByName($reviewStatus);
        
        return DB::transaction(function() use ($batchId, $reviewStatus) {

            $batchDetails = DB::table('batches')
                ->select('is_reverted_by_ondc','is_rejected_by_ondc')
                ->where('id', $batchId)
                ->first();

            $updatedBatch = $updatedClaims = [];

            //Claim Data
            $updatedClaims = [
                'status' => ClaimReviewStatus::DRAFT->value,
                'review_status' => ClaimReviewStatus::DRAFT->value,
                'ca_review_status'=> NULL,
                'ondc_review_status'=> NULL,
                'nsic_review_status'=>NULL,
                'nsicfinance_review_status' => NULL,
                'is_sent_ca' => NULL,
                'is_sent_ondc' => NULL,
                'is_sent_nsic' => NULL,
                'is_sent_nsicfinance' => NULL,
                'is_batch' => NULL
            ];
           
            if ($reviewStatus === ClaimReviewStatus::REVERTED->value && $batchDetails->is_reverted_by_ondc) {
                $updatedBatch = [
                    'is_reverted_by_ondc'=> NULL,
                    'updated_by' => AuthId(),
                    'updated_at' => now(),
                ];

                $claims = DB::table('batch_claims')->where('status_id',ClaimReviewStatus::REVERTED->value)
                        ->where('is_deleted',1)->where('batch_id',$batchId)->pluck('claim_id')->toArray();

            }
            if($reviewStatus === ClaimReviewStatus::REJECTED->value && $batchDetails->is_rejected_by_ondc)
            {
                $updatedBatch = [
                    'is_rejected_by_ondc'=> NULL,
                    'updated_by' => AuthId(),
                    'updated_at' => now(),
                ];

                $claims = DB::table('batch_claims')->where('status_id',ClaimReviewStatus::REJECTED->value)
                        ->where('is_deleted',1)->where('batch_id',$batchId)->pluck('claim_id')->toArray();
            }
            //dump($updatedBatch,' ',$updatedClaims, ' ', $claims); dd(1);
            DB::table('batches')->where('id', $batchId)->update($updatedBatch);
            DB::table('claims')->whereIn('id', $claims)->update($updatedClaims);
            DB::table('batch_claims')->whereIn('claim_id', $claims)->delete();
        });*/
    }
}
