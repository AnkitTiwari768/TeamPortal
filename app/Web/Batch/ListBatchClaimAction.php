<?php

declare(strict_types=1);

namespace App\Web\Batch;

use App\Traits\DataTable;
use App\Web\Claim\ClaimResource;
use App\Web\User\UserService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use App\Web\Claim\ClaimReviewStatus;

class ListBatchClaimAction
{
    use DataTable;

    private const SHARED_WITH_CA = 'Shared With CA';
    private const CERTIFIED_BY_CA = 'Certified By CA';

    public function __construct(
        private UserService $userService
    ) {}


    public function getClaimIdBySlug(string $slug)
    {
        return DB::table('claim_types')->where('slug', $slug)->value('id');
    }

    public function execute(?string $claimTypeSlug = null)
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
        
        $search ??= $this->escape_special_characters($search);
        $user = auth()->user();

        $claimTypeId = $this->getClaimIdBySlug($claimTypeSlug ?? 'claim-for-catalogue-creation');
        
        if ($filters['review_status'] === self::SHARED_WITH_CA || $filters['review_status'] === self::CERTIFIED_BY_CA) {
            $action = $filters['review_status'];
        } else {
            $action = ClaimReviewStatus::getIdByName($filters['review_status']);
        }

        $userRoles = $this->userService->getUserRoles($user->id);

        $query = DB::table('batches as b')
            ->select(
                'b.*',
                'snp.snp_name',
                'snp.snp_id',
                'brs.status_id as batchStatus'
            )
            ->join('batch_review_statuses as brs', 'brs.batch_id', '=', 'b.id')
            ->join('team_snp_scheme as snp', 'snp.user_id', '=', 'b.created_by');

        if (hasRole('snp')) {
            if ($action === self::SHARED_WITH_CA) {
                
                $query
                    ->addSelect(
                        DB::raw('(SELECT COUNT(*) FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.is_deleted IS NULL) as total_number_of_claims'),
                        DB::raw('(SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                            SELECT claim_id FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.is_deleted IS NULL)) as total_amount')
                    );

                $query
                    ->where('b.is_sent_ca', true)
                    ->whereNULL('b.is_reverted_by_ca')
                    ->whereNULL('b.is_ca_certified');
            } elseif ($action === self::CERTIFIED_BY_CA) {
                $query
                    ->addSelect(
                        DB::raw('(SELECT COUNT(*) FROM batch_claims as bc WHERE batch_id = b.id AND bc.is_deleted IS NULL ) as total_number_of_claims'),
                        DB::raw('(SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                            SELECT claim_id FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.is_deleted IS NULL )) as total_amount'),
                            DB::raw('true AS show_sent_to_ondc')
                    );
                
                $query
                    ->where('b.is_ca_certified', true)
                    ->whereNULL('b.is_sent_ondc');
            } elseif ($action === ClaimReviewStatus::PENDING->value) {
                $query
                    ->addSelect(
                        /*DB::raw(
                            '(
                                SELECT COUNT(*) 
                                FROM batch_claims as bc WHERE bc.batch_id = b.id 
                                AND (bc.is_deleted = 1 OR bc.is_reject_to_snp = 1) 
                                AND bc.status_id = 4
                            ) as total_number_of_claims'
                        ),*/
                        DB::raw(
                            '(
                                CASE
                                    WHEN EXISTS (
                                        SELECT 1
                                        FROM batch_claims AS bc
                                        WHERE bc.batch_id = b.id
                                          AND (
                                            (bc.is_reject_to_snp = 1 AND b.is_rejected_to_snp = 1)
                                            OR (bc.is_revert_to_snp = 1 AND b.is_reverted_to_snp = 1)
                                            OR (bc.is_revert_to_nsic = 1 AND b.is_reverted_to_nsic = 1)
                                          )
                                       
                                    )
                                    THEN (
                                        SELECT COUNT(*)
                                        FROM batch_claims AS bc
                                        JOIN claims as c ON bc.claim_id = c.id
                                        WHERE bc.batch_id = b.id
                                        AND (bc.status_id = 6 OR bc.is_revert_to_ondc = 1 OR bc.is_revert_to_nsic = 1) 
                                        AND (bc.is_revert_to_snp <> 1 OR bc.is_reject_to_snp <> 1)
                                        AND c.status NOT IN (3,4) AND c.review_status NOT IN (3,4)
                                    )
                                    ELSE (
                                        SELECT COUNT(*)
                                        FROM batch_claims AS bc
                                        JOIN claims as c ON bc.claim_id = c.id
                                        WHERE bc.batch_id = b.id
                                        AND bc.is_deleted IS NULL
                                        AND c.status NOT IN (3,4) AND c.review_status NOT IN (3,4)
                                    )
                                END
                            ) as total_number_of_claims'
                        ),
                        /*DB::raw('(
                            SELECT SUM(c.amount) 
                            FROM claims as c 
                            WHERE c.id IN (
                                SELECT claim_id 
                                FROM batch_claims as bc 
                                WHERE bc.batch_id = b.id 
                                AND (bc.is_deleted = 1 OR bc.is_reject_to_snp = 1) 
                                AND bc.status_id = 4
                            )
                        ) as total_amount')*/
                        DB::raw(
                            '(
                                CASE
                                    WHEN EXISTS (
                                        SELECT 1
                                        FROM batch_claims AS bc
                                        WHERE bc.batch_id = b.id
                                        AND (
                                            (bc.is_reject_to_snp = 1 AND b.is_rejected_to_snp = 1)
                                            OR (bc.is_revert_to_snp = 1 AND b.is_reverted_to_snp = 1)
                                            OR (bc.is_revert_to_nsic = 1 AND b.is_reverted_to_nsic = 1)
                                          )
                                    )
                                    THEN (
                                        SELECT COALESCE(SUM(c.amount), 0)
                                        FROM claims AS c
                                        WHERE c.id IN (
                                            SELECT claim_id
                                            FROM batch_claims AS bc
                                            JOIN claims as c ON bc.claim_id = c.id
                                            WHERE bc.batch_id = b.id
                                            AND (bc.status_id = 6 OR bc.is_revert_to_ondc = 1 OR bc.is_revert_to_nsic) 
                                            AND (bc.is_revert_to_snp <> 1 OR bc.is_reject_to_snp <> 1)
                                            AND c.status NOT IN (3,4) AND c.review_status NOT IN (3,4)
                                        )
                                    )
                                    ELSE (
                                        SELECT COALESCE(SUM(c.amount), 0)
                                        FROM claims AS c
                                        WHERE c.id IN (
                                            SELECT claim_id
                                            FROM batch_claims AS bc
                                            JOIN claims as c ON bc.claim_id = c.id
                                            WHERE bc.batch_id = b.id
                                              AND bc.is_deleted IS NULL
                                              AND c.status NOT IN (3,4) AND c.review_status NOT IN (3,4)
                                        )
                                    )
                                END
                            ) as total_amount'
                        )
                    );

                $query
                    ->where(function ($query) {
                        $query
                            ->where('b.status_id', ClaimReviewStatus::PENDING->value)
                            ->orWhere('b.is_reverted_to_ondc', true)
                            ->orWhere('b.is_resend_to_nsic', true); //added by priya
                    });
                $query->whereNotNull('is_sent_ondc');
            } elseif ($action === ClaimReviewStatus::APPROVED->value) {
                $query
                    ->addSelect(
                        DB::raw('(
                            SELECT COUNT(*) 
                            FROM batch_claims AS bc
                            JOIN claims AS c ON bc.claim_id = c.id
                            WHERE bc.batch_id = b.id 
                            AND c.review_status = 3 
                            AND c.status = 3
                        ) as total_number_of_claims'),
                        DB::raw('(
                            SELECT SUM(c.amount) 
                            FROM batch_claims AS bc
                            JOIN claims AS c ON bc.claim_id = c.id
                            WHERE bc.batch_id = b.id 
                            AND c.review_status = 3 
                            AND c.status = 3
                        ) as total_amount')
                    );

                $query->where('b.status_id', ClaimReviewStatus::APPROVED->value);
            } elseif ($action === ClaimReviewStatus::REVERTED->value) {
                $query->addSelect(
                    DB::raw("
                        (
                            CASE 
                                WHEN EXISTS(
                                    SELECT 1 FROM batches WHERE is_reverted_by_ondc = 1 AND id = b.id
                                    AND (is_reverted_to_snp IS NULL OR is_reverted_to_snp = 0)
                                )
                                THEN (
                                    SELECT COUNT(*) 
                                    FROM batch_claims AS bc 
                                    JOIN claims AS c ON bc.claim_id = c.id 
                                    WHERE 
                                        bc.batch_id = b.id AND bc.is_deleted = 1 AND bc.status_id = 5 AND c.status = 5   
                                )
                                WHEN EXISTS (
                                    SELECT 1 FROM batches WHERE is_reverted_to_snp = 1 AND id = b.id
                                )
                                THEN (
                                    SELECT COUNT(*) 
                                    FROM batch_claims AS bc 
                                    JOIN claims AS c ON bc.claim_id = c.id 
                                    WHERE bc.batch_id = b.id
                                    AND  c.status = 5
                                )
                                ELSE (
                                    SELECT COUNT(*) 
                                    FROM batch_claims AS bc 
                                    JOIN claims AS c ON bc.claim_id = c.id 
                                    WHERE bc.batch_id = b.id
                                    AND (
                                        (c.status = 5) /* OR c.status = 6 added by priya */
                                        OR c.ca_review_status = 5
                                        OR c.ondc_review_status = 5
                                        OR c.nsic_review_status = 5
                                        OR c.nsicfinance_review_status = 5
                                    )
                                )
                            END
                        ) AS total_number_of_claims
                    "),
                    DB::raw("
                        (
                            CASE 
                                WHEN EXISTS (
                                    SELECT 1 FROM batches WHERE is_reverted_by_ondc = 1 AND id = b.id
                                    AND (is_reverted_to_snp IS NULL OR is_reverted_to_snp = 0)
                                )
                                THEN (
                                    SELECT SUM(c.amount) FROM claims AS c
                                    WHERE c.id IN (
                                        SELECT bc.claim_id 
                                        FROM batch_claims AS bc 
                                        WHERE bc.batch_id = b.id AND bc.is_deleted = 1 AND bc.status_id = 5 AND c.status = 5
                                    )
                                )
                                WHEN EXISTS (
                                    SELECT 1 FROM batches WHERE is_reverted_to_snp = 1 AND id = b.id
                                )
                                THEN (
                                    SELECT SUM(c.amount) FROM claims AS c
                                    WHERE c.id IN (
                                        SELECT bc.claim_id 
                                        FROM batch_claims AS bc 
                                        WHERE bc.batch_id = b.id
                                        /*AND c.nsic_review_status = 5 commented by priya*/
                                        AND  c.status = 5 /* OR c.status = 5 added by priya */
                                    )
                                )
                                ELSE (
                                    SELECT SUM(c.amount) FROM claims AS c
                                    WHERE c.id IN (
                                        SELECT bc.claim_id 
                                        FROM batch_claims AS bc 
                                        WHERE bc.batch_id = b.id
                                        AND (
                                            (c.status = 5) /* OR c.status = 6 added by priya */
                                            OR c.ca_review_status = 5
                                            OR c.ondc_review_status = 5
                                            OR c.nsic_review_status = 5
                                            OR c.nsicfinance_review_status = 5
                                        )
                                    )
                                )
                            END 
                        ) AS total_amount
                    ")
                );


                /*$query->where(function ($query) {
                    $query
                        ->where('b.status_id', ClaimReviewStatus::REVERTED->value)
                        ->orWhere('b.is_reverted_by_ondc', true)
                        ->orWhere('b.is_reverted_by_ca', true)
                        ->orWhere('b.is_reverted_to_snp', true);
                });
                $query->whereNULL('b.is_moved_to_draft_for_reverted');
                $query->whereNULL('b.is_moved_to_draft_for_ca'); */  //commented for ca reverted case
                $query->where(function ($query) {
                    $query->where(function ($q) {
                        $q->where('b.status_id', ClaimReviewStatus::REVERTED->value)
                          ->orWhere('b.is_reverted_by_ondc', true)
                          ->orWhere('b.is_reverted_by_ca', true)
                          ->orWhere('b.is_reverted_to_snp', true);
                    })
                    ->where(function ($q) {
                        $q->where(function ($inner) {
                            // CA reverted → only show if not moved to draft
                            $inner->where('b.is_reverted_by_ca', true)
                                  ->whereNull('b.is_moved_to_draft_for_ca');
                        })
                        ->orWhere(function ($inner) {
                            // ONDC or REVERTED → only if not moved to draft, but exclude CA–reverted ones
                            $inner->where(function ($sub) {
                                    $sub->where('b.status_id', ClaimReviewStatus::REVERTED->value)
                                        ->orWhere('b.is_reverted_by_ondc', true);
                                })
                                ->whereNull('b.is_moved_to_draft_for_reverted')
                                ->where(function ($excludeCA) {
                                    $excludeCA->whereNull('b.is_reverted_by_ca')
                                              ->orWhere('b.is_reverted_by_ca', false);
                                });
                        })
                        ->orWhere(function ($inner) {
                            // Always show if reverted to SNP
                            $inner->where('b.is_reverted_to_snp', true);
                        });
                    });
                });
                //dd($query->toSql());
            } elseif ($action === ClaimReviewStatus::REJECTED->value) {
                $query->addSelect(
                    DB::raw(
                        '(
                        SELECT COUNT(*) 
                        FROM batch_claims as bc 
                        JOIN claims as c ON bc.claim_id = c.id
                        WHERE bc.batch_id = b.id 
                        AND (bc.is_deleted = 1 OR bc.is_reject_to_snp = 1) 
                        AND (
                                c.status = 4 
                                /*OR c.ca_review_status = 4
                                OR c.ondc_review_status = 4
                                OR c.nsic_review_status = 4
                                OR c.nsicfinance_review_status = 4*/
                            )
                        ) as total_number_of_claims'
                    ),
                    DB::raw(
                        '(
                            SELECT SUM(c.amount) 
                            FROM claims as c 
                            WHERE c.id IN (
                                SELECT claim_id 
                                FROM batch_claims as bc 
                                JOIN claims as c ON bc.claim_id = c.id
                                WHERE bc.batch_id = b.id 
                                AND (bc.is_deleted = 1 OR bc.is_reject_to_snp = 1) 
                                AND (
                                    c.status = 4 
                                   /* OR c.ca_review_status = 4
                                    OR c.ondc_review_status = 4
                                    OR c.nsic_review_status = 4
                                    OR c.nsicfinance_review_status = 4*/
                                )
                        )) as total_amount'
                    ),
                    DB::raw('true AS show_move_to_draft_in_rejected')
                );

                $query->where(function ($query) use ($action) {
                    $query
                        ->where('b.status_id', $action)
                        ->orWhere('b.is_rejected_by_ondc', true)
                        ->orWhere('b.is_rejected_to_snp', true); //added by priya
                });
                $query->whereNULL('b.is_moved_to_draft_for_rejected');
            } elseif ($action === ClaimReviewStatus::PAYMENT_COMPLETED->value) {
                $query
                    ->addSelect(
                        DB::raw('(
                            SELECT COUNT(*) 
                            FROM batch_claims AS bc
                            JOIN claims AS c ON bc.claim_id = c.id
                            WHERE bc.batch_id = b.id 
                            AND c.review_status = 7 
                            AND c.status = 7
                        ) as total_number_of_claims'),
                        DB::raw('(
                            SELECT SUM(c.amount) 
                            FROM batch_claims AS bc
                            JOIN claims AS c ON bc.claim_id = c.id
                            WHERE bc.batch_id = b.id 
                            AND c.review_status = 7 
                            AND c.status = 7
                        ) as total_amount')
                    );

                $query->where('b.status_id', ClaimReviewStatus::PAYMENT_COMPLETED->value);
            }

            $query->where('b.created_by', authId());
        }

        if (hasRole('ca')) {

            $query->join('batch_workflow AS bwl', 'bwl.batch_id', '=', 'b.id');

           

            if ($action === ClaimReviewStatus::PENDING->value) {
                $query
                ->addSelect(
                    DB::raw('(SELECT COUNT(*) FROM batch_claims as bc WHERE bc.batch_id = b.id) as total_number_of_claims'),
                    DB::raw('(SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                        SELECT claim_id FROM batch_claims as bc WHERE bc.batch_id = b.id)) as total_amount')
                );
                $query->where('brs.status_id', ClaimReviewStatus::PENDING->value);
            } elseif ($action === ClaimReviewStatus::APPROVED->value) {
                //$query->where('brs.status_id', ClaimReviewStatus::APPROVED->value);
                $query
                    ->addSelect(
                        DB::raw('(SELECT COUNT(*) FROM claims as c WHERE c.id IN (
                            SELECT claim_id FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.ca_review_status=3)) as total_number_of_claims'),
                        DB::raw('(SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                            SELECT claim_id FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.ca_review_status=3)) as total_amount')
                    );
                $query->where('brs.status_id', ClaimReviewStatus::APPROVED->value);
            } elseif ($action === ClaimReviewStatus::REVERTED->value) {
                //$query->where('brs.status_id', ClaimReviewStatus::REVERTED->value);

                $query
                    ->addSelect(
                        DB::raw('(SELECT COUNT(*) FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.ca_review_status=5 AND bc.is_deleted = 1 ) as total_number_of_claims'),
                        DB::raw('(SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                            SELECT claim_id FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.ca_review_status=5 AND bc.is_deleted = 1)) as total_amount')
                    );
                $query->where('brs.status_id', ClaimReviewStatus::REVERTED->value)
                    ->where('brs.role_id', authRoleId())
                    ->orWhere('b.is_reverted_by_ca', true);
            }

            $query
                ->where('bwl.to_user_id', $user->id)
                ->where('brs.role_id', authRoleId())
                ->where('brs.user_id', AuthId());
        }

        if (hasRole('ondc-admin')) {

            $query->join('batch_workflow AS bwl', 'bwl.batch_id', '=', 'b.id');

            if ($action === ClaimReviewStatus::PENDING->value) {
                $query->addSelect( //added by priya
                    DB::raw("
                        (
                            CASE 
                                WHEN EXISTS(
                                    SELECT 1 FROM batches WHERE is_reverted_to_ondc = 1
                                )
                                THEN (
                                    SELECT COUNT(*) 
                                    FROM batch_claims AS bc
                                    WHERE 
                                        bc.is_revert_to_ondc = 1 
                                        AND bc.status_id = 5  
                                )
                                ELSE (
                                    SELECT COUNT(*) 
                                    FROM batch_claims AS bc 
                                    JOIN claims AS c ON bc.claim_id = c.id 
                                    WHERE 
                                        bc.batch_id = b.id
                                        AND bc.is_deleted IS NULL  
                                )
                            END
                        ) AS total_number_of_claims
                    "),
                    DB::raw("
                        (
                            CASE 
                                WHEN EXISTS(
                                    SELECT 1 FROM batches WHERE is_reverted_to_ondc = 1
                                )
                                THEN (
                                    SELECT SUM(c.amount) FROM claims AS c
                                    WHERE c.id IN (
                                        SELECT bc.claim_id 
                                        FROM batch_claims AS bc 
                                        WHERE bc.batch_id = b.id AND bc.is_revert_to_ondc = 1 
                                        AND bc.status_id = 5 
                                    )
                                )
                                ELSE (
                                    SELECT SUM(c.amount) FROM claims AS c
                                    WHERE c.id IN (
                                        SELECT bc.claim_id 
                                        FROM batch_claims AS bc 
                                        WHERE bc.batch_id = b.id AND bc.is_deleted IS NULL
                                        /*AND (
                                            c.ondc_review_status = 6
                                            OR bc.status_id = 6
                                        )*/
                                    )
                                    
                                )
                            END
                        ) AS total_amount
                    ")
                );
                $query
                    /*->addSelect( //commented by priya
                        DB::raw('(SELECT COUNT(*) FROM batch_claims as bc WHERE batch_id = b.id AND (bc.is_revert_to_ondc = 1 AND bc.status_id = 5)) as total_number_of_claims'),
                        DB::raw('(SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                            SELECT claim_id FROM batch_claims as bc WHERE batch_id = b.id AND (bc.is_revert_to_ondc = 1 AND bc.status_id = 5)
                        )) as total_amount')
                    )*/
                    ->where('b.is_reverted_to_ondc', true)
                    ->orWhere('brs.status_id', ClaimReviewStatus::PENDING->value);
            } elseif ($action === ClaimReviewStatus::APPROVED->value) {

                /*$query
                    ->addSelect(
                        DB::raw('(SELECT COUNT(*) FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.is_deleted IS NULL) as total_number_of_claims'),
                        DB::raw('(SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                            SELECT claim_id FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.is_deleted IS NULL)) as total_amount')
                    );*/ //commented by priya
                //added by priya
                $query
                    ->addSelect(
                        DB::raw('(SELECT COUNT(*) FROM claims as c WHERE c.id IN (
                            SELECT claim_id FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.ondc_review_status=3)) as total_number_of_claims'),
                        DB::raw('(SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                            SELECT claim_id FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.ondc_review_status=3)) as total_amount')
                    );
                $query->where('brs.status_id', ClaimReviewStatus::APPROVED->value);
            } elseif ($action === ClaimReviewStatus::REVERTED->value) {
                $query
                    ->addSelect(
                        DB::raw('(SELECT COUNT(*) FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.ondc_review_status=5 AND bc.is_deleted = 1 ) as total_number_of_claims'),
                        DB::raw('(SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                            SELECT claim_id FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.ondc_review_status=5 AND bc.is_deleted = 1)) as total_amount')
                    );
                $query->where('brs.status_id', ClaimReviewStatus::REVERTED->value)
                    ->where('brs.role_id', authRoleId())
                    ->orWhere('b.is_reverted_by_ondc', true);
            } elseif ($action === ClaimReviewStatus::REJECTED->value) {
                $query
                    ->addSelect(
                        DB::raw('(SELECT COUNT(*) FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.ondc_review_status=4 AND bc.is_deleted = 1) as total_number_of_claims'),
                        DB::raw('(SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                            SELECT claim_id FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.ondc_review_status=4 AND bc.is_deleted = 1)) as total_amount')
                    );
                $query->where('brs.status_id', ClaimReviewStatus::REJECTED->value)
                    ->orWhere('b.is_rejected_by_ondc', true);
            } elseif ($action === ClaimReviewStatus::PAYMENT_COMPLETED->value) {

                $query
                    ->addSelect(
                        DB::raw('(SELECT COUNT(*) FROM claims as c WHERE c.id IN (
                            SELECT claim_id FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.ondc_review_status=7)) as total_number_of_claims'),
                        DB::raw('(SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                            SELECT claim_id FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.ondc_review_status=7)) as total_amount')
                    );
                $query->where('brs.status_id', ClaimReviewStatus::PAYMENT_COMPLETED->value); 
            }

            $query
                ->where('bwl.to_role_id', authRoleId())
                ->where('brs.role_id', authRoleId());
        }

        if (hasRole('nsic')) {
            $query->join('batch_workflow AS bwl', 'bwl.batch_id', '=', 'b.id');
            if ($action === ClaimReviewStatus::PENDING->value) {
                $query
                    ->addSelect(
                        DB::raw('(
                            CASE WHEN (b.is_resend_to_nsic = 1 OR b.is_reverted_to_nsic = 1) AND b.is_sent_nsic_finance=1 
                            THEN (
                                SELECT COUNT(*) 
                                FROM batch_claims as bc 
                                JOIN claims AS c ON c.id = bc.claim_id
                                WHERE (bc.batch_id = b.id AND c.nsic_review_status = 6 AND bc.status_id=6)
                                OR (bc.batch_id = b.id AND bc.is_revert_to_nsic=1 AND bc.status_id=5)
                            ) 
							 WHEN (b.is_resend_to_nsic = 1 OR b.is_reverted_to_nsic = 1) AND b.is_sent_nsic_finance IS NULL 
                            THEN (
                                SELECT COUNT(*) 
                                FROM batch_claims as bc 
                                JOIN claims AS c ON c.id = bc.claim_id
                                WHERE (bc.batch_id = b.id AND c.nsic_review_status IN(6,5,3) AND (bc.status_id=6 OR bc.is_resend_process_by_nsic = 1))
                            ) 
                            ELSE(
                            
                                SELECT COUNT(*) 
                                FROM batch_claims as bc 
                                JOIN claims AS c ON c.id = bc.claim_id
                                WHERE (bc.batch_id = b.id AND c.is_sent_nsic = 1 ) 
                                OR (bc.batch_id = b.id AND bc.is_revert_to_nsic = 1 AND bc.status_id = 5)
                            )
                            END
                        ) as total_number_of_claims'),
                        DB::raw('(
                            CASE 
                                WHEN (b.is_resend_to_nsic = 1 OR b.is_reverted_to_nsic = 1) AND b.is_sent_nsic_finance=1
                                THEN (
                                    SELECT SUM(c.amount) 
                                    FROM claims as c 
                                    WHERE c.id IN (
                                        SELECT claim_id 
                                        FROM batch_claims as bc 
                                        WHERE (bc.batch_id = b.id AND c.nsic_review_status = 6 AND bc.status_id=6)
                                         OR (bc.batch_id = b.id AND bc.is_revert_to_nsic=1 AND bc.status_id=5)
                                    )
                                ) 
								WHEN (b.is_resend_to_nsic = 1 OR b.is_reverted_to_nsic = 1) AND b.is_sent_nsic_finance IS NULL
                                THEN (
                                    SELECT SUM(c.amount) 
                                    FROM claims as c 
                                    WHERE c.id IN (
                                        SELECT claim_id 
                                        FROM batch_claims as bc 
                                        WHERE (bc.batch_id = b.id AND c.nsic_review_status IN (6,5,3) AND (bc.status_id=6 OR bc.is_resend_process_by_nsic = 1))
                                    )
                                ) 								
                                ELSE(
                                    SELECT SUM(c.amount) 
                                    FROM claims as c 
                                    WHERE c.id IN (
                                        SELECT claim_id 
                                        FROM batch_claims as bc 
                                        WHERE (bc.batch_id = b.id AND c.is_sent_nsic=1 AND bc.status_id = 6) OR (bc.batch_id = b.id AND bc.is_revert_to_nsic = 1 AND bc.status_id = 5)
                                    )
                                )
                            END
                        ) as total_amount')
                    );

                $query->where(function ($query) {
                    $query->where('brs.status_id', ClaimReviewStatus::PENDING->value);
                    $query->where('brs.role_id', authRoleId());
                });

                $query->orWhere(function ($query) {
                    $query
                        ->where('b.is_resend_to_nsic', true);
                    //->where('b.status_id', ClaimReviewStatus::APPROVED->value);
                });

                $query->orWhere(function ($query) {
                    $query
                        ->where('b.is_reverted_to_nsic', true);
                });
            } elseif ($action === ClaimReviewStatus::APPROVED->value) {
                $query
                    ->addSelect(
                        DB::raw('(
                            SELECT COUNT(*) FROM 
                            batch_claims as bc 
                            JOIN claims as c ON bc.claim_id = c.id
                            WHERE bc.batch_id = b.id 
                            AND bc.is_deleted IS NULL 
                            AND c.nsic_review_status = 3) as total_number_of_claims'),
                        DB::raw('(
                            SELECT SUM(c.amount) 
                            FROM claims as c 
                            WHERE c.id IN (
                                SELECT claim_id FROM batch_claims as bc 
                                WHERE bc.batch_id = b.id 
                                AND bc.is_deleted IS NULL AND c.nsic_review_status = 3
                                )) as total_amount')
                    );

                $query->where('brs.status_id', ClaimReviewStatus::APPROVED->value);
                $query->where('brs.role_id', authRoleId());
            } elseif ($action === ClaimReviewStatus::REVERTED->value) {
                $query
                    ->addSelect(
                        DB::raw('(
                            SELECT COUNT(*) 
                            FROM batch_claims as bc 
                            JOIN claims as c ON bc.claim_id = c.id
                            WHERE bc.batch_id = b.id 
                            AND c.nsic_review_status = 5
                            ) as total_number_of_claims'),
                        DB::raw('(
                            SELECT SUM(c.amount) 
                            FROM claims as c WHERE c.id IN (
                                SELECT claim_id 
                                FROM batch_claims as bc
                                JOIN claims as c ON bc.claim_id = c.id 
                                WHERE bc.batch_id = b.id 
                                AND bc.is_deleted IS NULL
                                AND c.nsic_review_status = 5
                            )
                            ) as total_amount')
                    );
                $query
                    ->where('brs.status_id', ClaimReviewStatus::REVERTED->value)
                    ->where('brs.role_id', authRoleId())
                    ->orWhere('b.is_reverted_to_snp', true)
                    ->orWhere('b.is_reverted_to_ondc', true);
                //$query->where('brs.role_id', authRoleId());
            } elseif ($action === ClaimReviewStatus::REJECTED->value) {
                $query
                    ->addSelect(
                        DB::raw('(
                            SELECT COUNT(*) 
                            FROM batch_claims as bc
                            JOIN claims as c ON bc.claim_id = c.id  
                            WHERE bc.batch_id = b.id 
                            /*AND bc.is_deleted IS NULL 
                            AND c.nsic_review_status = 4 commented by priya*/
                            AND bc.status_id = 4 
                            AND bc.is_reject_to_snp = 1
                            AND b.is_rejected_to_snp = 1
                            AND bc.is_deleted = 1
                            ) as total_number_of_claims'),
                        DB::raw('(
                            SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                            SELECT claim_id 
                            FROM batch_claims as bc 
                            JOIN claims as c ON bc.claim_id = c.id  
                            WHERE bc.batch_id = b.id 
                            /*AND bc.is_deleted IS NULL 
                            AND c.nsic_review_status = 4 commented by priya*/
                            AND bc.status_id = 4 
                            AND bc.is_reject_to_snp = 1
                            AND b.is_rejected_to_snp = 1
                            AND bc.is_deleted = 1
                            )) as total_amount')
                    );
                $query
                    ->where('brs.status_id', ClaimReviewStatus::REJECTED->value)
                    ->where('brs.role_id', authRoleId())
                    ->orWhere('b.is_rejected_to_snp', true);
            } elseif ($action === ClaimReviewStatus::PAYMENT_COMPLETED->value) {

                $query
                    ->addSelect(
                        DB::raw('(
                            SELECT COUNT(*) FROM 
                            batch_claims as bc 
                            JOIN claims as c ON bc.claim_id = c.id
                            WHERE bc.batch_id = b.id 
                            AND bc.is_deleted IS NULL 
                            AND c.nsic_review_status = 7) as total_number_of_claims'),
                        DB::raw('(
                            SELECT SUM(c.amount) 
                            FROM claims as c 
                            WHERE c.id IN (
                                SELECT claim_id FROM batch_claims as bc 
                                WHERE bc.batch_id = b.id 
                                AND bc.is_deleted IS NULL AND c.nsic_review_status = 7
                                )) as total_amount')
                    );

                $query->where('brs.status_id', ClaimReviewStatus::PAYMENT_COMPLETED->value);
                $query->where('brs.role_id', authRoleId());
            }
        }

        if (hasRole('nsic-finance')) {
            $query->join('batch_workflow AS bwl', 'bwl.batch_id', '=', 'b.id');
            if ($action === ClaimReviewStatus::PENDING->value) {
                $query
                    ->addSelect(
                        DB::raw('(
                            CASE 
                                WHEN b.is_resend_to_nsic_finance = 1 AND b.status_id = 3
                                THEN (
                                    SELECT COUNT(*) 
                                    FROM batch_claims as bc 
                                    JOIN claims as c ON bc.claim_id = c.id  
                                    WHERE bc.batch_id = b.id AND c.nsicfinance_review_status = 6 AND bc.status_id=6
                                )WHEN b.is_resend_to_nsic_finance = 1 AND b.status_id != 3
                                THEN (
                                    SELECT COUNT(*) 
                                    FROM batch_claims as bc 
                                    JOIN claims as c ON bc.claim_id = c.id 
                                    WHERE (bc.batch_id = b.id AND c.nsicfinance_review_status IN(6,5,3) AND bc.status_id=6)
                                )
                                ELSE (
                                    SELECT COUNT(*) 
                                    FROM batch_claims as bc 
                                    JOIN claims as c ON bc.claim_id = c.id  
                                    WHERE bc.batch_id = b.id 
                                    AND bc.is_deleted IS NULL
                                    AND c.is_sent_nsicfinance = 1
                                )
                            END
                            ) as total_number_of_claims'),
                        DB::raw('(
                            CASE 
                                WHEN b.is_resend_to_nsic_finance = 1 AND b.status_id = 3
                                THEN (
                                    SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                                        SELECT claim_id 
                                        FROM batch_claims as bc 
                                        JOIN claims as c ON bc.claim_id = c.id  
                                        WHERE bc.batch_id = b.id AND c.nsicfinance_review_status = 6 AND bc.status_id=6
                                    )
                                )WHEN b.is_resend_to_nsic_finance = 1 AND b.status_id != 3
                                THEN (
                                    SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                                        SELECT claim_id 
                                        FROM batch_claims as bc 
                                        JOIN claims as c ON bc.claim_id = c.id  
                                        WHERE (bc.batch_id = b.id AND c.nsicfinance_review_status IN(6,5,3) AND bc.status_id=6)
                                    )
                                )
                                ELSE (
                                    SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                                        SELECT claim_id 
                                        FROM batch_claims as bc 
                                        JOIN claims as c ON bc.claim_id = c.id  
                                        WHERE bc.batch_id = b.id 
                                        AND bc.is_deleted IS NULL
                                        AND c.is_sent_nsicfinance = 1
                                    )
                                )
                            END
                            ) as total_amount')
                    );


                $query->where(function ($query) {
                    $query->where('brs.status_id', ClaimReviewStatus::PENDING->value);
                    $query
                        ->where('bwl.to_role_id', authRoleId())
                        ->where('brs.role_id', authRoleId());
                });

                $query->orWhere(function ($query) {
                    $query
                        ->where('b.is_resend_to_nsic_finance', true);
                    //->where('b.status_id', ClaimReviewStatus::APPROVED->value);
                });
            } elseif ($action === ClaimReviewStatus::APPROVED->value) {
                $query->addSelect(
                    DB::raw('(
                        SELECT COUNT(*) 
                        FROM batch_claims AS bc 
                        JOIN claims AS c ON bc.claim_id = c.id
                        WHERE bc.batch_id = b.id 
                        AND bc.is_deleted IS NULL
                        AND c.nsicfinance_review_status = 3
                    ) AS total_number_of_claims'),

                    DB::raw('(
                        SELECT SUM(c.amount) 
                        FROM batch_claims AS bc 
                        JOIN claims AS c ON bc.claim_id = c.id
                        WHERE bc.batch_id = b.id 
                        AND bc.is_deleted IS NULL
                        AND c.nsicfinance_review_status = 3
                    ) AS total_amount'),
                    DB::raw('true AS mark_payment_completed')
                );

                $query
                    ->where('brs.status_id', ClaimReviewStatus::APPROVED->value)
                    ->where('brs.role_id', authRoleId()); //added by priya

            } elseif ($action === ClaimReviewStatus::REVERTED->value) {
                $query
                    ->addSelect(
                        DB::raw('(
                            SELECT COUNT(*) 
                            FROM batch_claims as bc
                            JOIN claims as c ON bc.claim_id = c.id   
                            WHERE bc.batch_id = b.id 
                            AND bc.is_deleted IS NULL
                            AND c.nsicfinance_review_status = 5
                        ) as total_number_of_claims'),
                        DB::raw('(SELECT SUM(c.amount) FROM claims as c 
                            WHERE c.id IN (
                            SELECT claim_id 
                            FROM batch_claims as bc 
                            JOIN claims as c ON bc.claim_id = c.id   
                            WHERE bc.batch_id = b.id 
                            AND bc.is_deleted IS NULL 
                            AND c.nsicfinance_review_status = 5)
                            ) as total_amount')
                    );

                $query
                    //->where('bwl.to_role_id', authRoleId()); //commented by priya
                    // ->where('brs.role_id', authRoleId()) //commented by priya
                    ->orWhere('b.is_reverted_to_nsic', true);
            } /*elseif ($action === ClaimReviewStatus::REJECTED->value) {
                $query
                    ->addSelect(
                        DB::raw('(SELECT COUNT(*) FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.is_deleted IS NULL) as total_number_of_claims'),
                        DB::raw('(SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                            SELECT claim_id FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.is_deleted IS NULL)) as total_amount')
                    );
                $query->where('brs.status_id', ClaimReviewStatus::REJECTED->value);
            }*/ elseif ($action === ClaimReviewStatus::PAYMENT_COMPLETED->value) {
                $query
                    ->addSelect(
                        DB::raw('(
                            SELECT COUNT(*) 
                            FROM batch_claims as bc
                            JOIN claims as c ON bc.claim_id = c.id    
                            WHERE bc.batch_id = b.id 
                            AND bc.is_deleted IS NULL
                            AND c.nsicfinance_review_status = 7
                            ) as total_number_of_claims'),
                        DB::raw('(SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                            SELECT claim_id 
                            FROM batch_claims as bc
                            JOIN claims as c ON bc.claim_id = c.id 
                            WHERE bc.batch_id = b.id 
                            AND bc.is_deleted IS NULL
                            AND c.nsicfinance_review_status = 7
                        )) as total_amount')
                    );
                $query->where('brs.status_id', ClaimReviewStatus::PAYMENT_COMPLETED->value);
            }
        }

        $query
            ->where('b.claim_type_id', $claimTypeId)
            ->groupBy('b.id')
            ->orderBy('b.created_at', 'desc');
        // dd($query->toSql());
        if ($page) {
            return $this->getDataTableResult(
                BatchResource::collection($query->paginate($limit))
            );
        }

        return BatchResource::collection($query->get());
    }









    // public function executeOld(?string $claimTypeSlug = null): AnonymousResourceCollection|array
    // {
    //     [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
    //     $search ??= $this->escape_special_characters($search);
    //     $user = auth()->user();

    //     $claimTypeId = DB::table('claim_types')->where('slug', $claimTypeSlug ?? 'claim-for-catalogue-creation')->value('id');
    //     $action = ClaimReviewStatus::getIdByName($filters['review_status']);

    //     $query = DB::table('batches as b')
    //         ->select(
    //             'b.id',
    //             'b.batch_number',
    //             'b.financial_year',
    //             'b.month',
    //             'b.description',
    //             'b.is_ca_certified',
    //             'b.is_sent_ca',
    //             'b.is_sent_ondc',
    //             'b.is_sent_nsic',
    //             'b.is_sent_nsic_finance',
    //             'b.status_id',
    //             'b.created_at',
    //             'snp.snp_name',
    //             'snp.snp_id',
    //             'brs.status_id as batchStatus',
    //             'b.is_reverted_by_ondc',
    //             'b.is_rejected_by_ondc',
    //             'b.is_reverted_to_snp',
    //             'b.is_reverted_to_ondc',
    //         )
    //         ->join('team_snp_scheme as snp','snp.user_id','=','b.created_by')
    //         ->whereNull('b.deleted_at')
    //         ->groupBy('b.id');

    //     if (!hasRole('snp')) {
    //         $userRoles = $this->userService->getUserRoles($user->id);
    //         $query->join('batch_workflow AS bwl', 'bwl.batch_id', '=', 'b.id');
    //         if(hasRole('ca')){
    //             $query->addSelect(DB::raw('(SELECT COUNT(*) FROM batch_claims as bc WHERE batch_id = b.id AND bc.is_deleted IS NULL ) as total_claims'),
    //             DB::raw('(SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
    // 				SELECT claim_id FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.is_deleted IS NULL )) as total_amount'));
    //             $query->join('batch_review_statuses as brs','brs.batch_id','=','b.id');
    //             $query->where('brs.role_id',authRoleId());
    //             $query->where('brs.user_id',AuthId());
    //             $query->where('brs.status_id', $action);
    //             $query->where('bwl.to_user_id',$user->id);
    //         }
    //         $query->whereIn('bwl.to_role_id',$userRoles);
    //     }else{
    //         //$query->where('b.created_by',$user->id);
    //     }
    // }

    public function executes(?string $claimTypeSlug = null): AnonymousResourceCollection|array
    {

        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
        $search ??= $this->escape_special_characters($search);
        $user = auth()->user();

        $claimTypeId = DB::table('claim_types')->where('slug', $claimTypeSlug ?? 'claim-for-catalogue-creation')->value('id');


        $query = DB::table('batches as b')
            ->select(
                'b.id',
                'b.batch_number',
                'b.financial_year',
                'b.month',
                'b.description',
                'b.is_ca_certified',
                'b.is_sent_ca',
                'b.is_sent_ondc',
                'b.is_sent_nsic',
                'b.is_sent_nsic_finance',
                'b.status_id',
                'b.created_at',
                'snp.snp_name',
                'snp.snp_id',
                'brs.status_id as batchStatus',
                'b.is_reverted_by_ondc',
                'b.is_rejected_by_ondc',
                'b.is_reverted_to_snp',
                'b.is_reverted_to_ondc',
                DB::raw('(SELECT COUNT(*) FROM batch_claims as bc WHERE batch_id = b.id AND bc.is_deleted IS NULL ) as total_claims'),
                DB::raw('(SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
					SELECT claim_id FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.is_deleted IS NULL )) as total_amount')
            )
            ->join('team_snp_scheme as snp', 'snp.user_id', '=', 'b.created_by')
            //->leftJoin('batch_claims as bc', 'bc.batch_id', '=', 'b.id')
            //->leftJoin('claims as c', 'c.id', '=', 'bc.claim_id')
            ->whereNull('b.deleted_at')
            //->whereNull('bc.is_deleted')
            ->groupBy('b.id');

        if (!hasRole('snp')) {
            $userRoles = $this->userService->getUserRoles($user->id);
            $query->join('batch_workflow AS bwl', 'bwl.batch_id', '=', 'b.id');
            if (hasRole('ca')) {
                $query->where('bwl.to_user_id', $user->id);
            }
            $query->whereIn('bwl.to_role_id', $userRoles);
        } else {
            //$query->where('b.created_by',$user->id);
        }


        if (isset($filters)) {
            if ($filters['review_status'] == 'Shared With CA') {
                $query->join('batch_review_statuses as brs', 'brs.batch_id', '=', 'b.id');
                $query->where('b.is_sent_ca', 1);
                $query->whereNULL('b.is_ca_certified');
            } elseif ($filters['review_status'] == 'Certified By CA') {
                $query->join('batch_review_statuses as brs', 'brs.batch_id', '=', 'b.id');
                $query->where('b.is_ca_certified', 1);
                $query->whereNULL('b.is_sent_ondc');
            } else {
                $action = ClaimReviewStatus::getIdByName($filters['review_status']);
                //dd($filters['review_status']);
                if (hasRole('ca')) {
                    $query->join('batch_review_statuses as brs', 'brs.batch_id', '=', 'b.id');
                    $query->where('brs.role_id', authRoleId());
                    $query->where('brs.user_id', AuthId());
                    $query->where('brs.status_id', $action);
                } elseif (hasRole('snp')) {
                    $query->join('batch_review_statuses as brs', 'brs.batch_id', '=', 'b.id');

                    if ($action === ClaimReviewStatus::REVERTED->value) {
                        $query->addSelect(
                            DB::raw('(SELECT COUNT(*) FROM batch_claims as bc WHERE batch_id = b.id AND bc.is_deleted = 1 AND bc.status_id = 5) as total_claims_by_ondc'),
                            DB::raw('(SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                            SELECT claim_id FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.is_deleted = 1 AND bc.status_id = 5
                        )) as total_amount_rev_by_ondc'),
                            DB::raw('(SELECT COUNT(*) FROM batch_claims as bc WHERE batch_id = b.id AND bc.is_revert_to_snp = 1 AND (b.is_reverted_to_snp = NULL || b.is_reverted_to_snp = 1) AND bc.status_id = 5) as total_claims_by_nsic'),
                            DB::raw('(SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                            SELECT claim_id FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.is_revert_to_snp = 1 AND (b.is_reverted_to_snp = NULL || b.is_reverted_to_snp = 1) AND bc.status_id = 5
                        )) as total_amount_rev_by_nsic')
                        );
                        $query->where(function ($query) use ($action) {
                            $query
                                ->where('b.status_id', $action)
                                ->orWhere('b.is_reverted_by_ondc', true)
                                ->orWhere('b.is_reverted_to_snp', true);
                        });
                    } else if ($action === ClaimReviewStatus::REJECTED->value) {
                        $query->addSelect(
                            DB::raw('(SELECT COUNT(*) FROM batch_claims as bc WHERE batch_id = b.id AND (bc.is_deleted = 1 || bc.is_reject_to_snp = 1) AND bc.status_id = 4) as total_rej_claims_by_ondc'),
                            DB::raw('(SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                            SELECT claim_id FROM batch_claims as bc WHERE bc.batch_id = b.id AND (bc.is_deleted = 1 || bc.is_reject_to_snp = 1) AND bc.status_id = 4
                        )) as total_amount_rej_by_ondc')
                        );
                        $query->where(function ($query) use ($action) {
                            $query
                                ->where('b.status_id', $action)
                                ->orWhere('b.is_rejected_by_ondc', true);
                        });
                    } else if ($action === ClaimReviewStatus::PENDING->value) {
                        $query->addSelect(
                            DB::raw('(SELECT COUNT(*) FROM batch_claims as bc WHERE batch_id = b.id AND (bc.is_deleted = 1 || bc.is_reject_to_snp = 1) AND bc.status_id = 4) as total_rej_claims_by_ondc'),
                            DB::raw('(SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                            SELECT claim_id FROM batch_claims as bc WHERE bc.batch_id = b.id AND (bc.is_deleted = 1 || bc.is_reject_to_snp = 1) AND bc.status_id = 4
                        )) as total_amount_rej_by_ondc')
                        );
                        $query->where(function ($query) use ($action) {
                            $query
                                ->where('b.status_id', $action)
                                ->orWhere('b.is_rejected_by_ondc', true);
                        });
                    } else {
                        $query->where('b.status_id', $action);
                    }
                } elseif (hasRole('ondc-admin') && $action === ClaimReviewStatus::REVERTED->value) {
                    $query->join('batch_review_statuses as brs', 'brs.batch_id', '=', 'b.id');

                    $query->addSelect(
                        DB::raw('(SELECT COUNT(*) FROM batch_claims as bc WHERE batch_id = b.id AND bc.is_revert_to_ondc = 1 AND bc.status_id = 5) as total_claims_of_ondc'),
                        DB::raw('(SELECT SUM(c.amount) FROM claims as c WHERE c.id IN (
                            SELECT claim_id FROM batch_claims as bc WHERE bc.batch_id = b.id AND bc.is_revert_to_ondc = 1 AND bc.status_id = 5
                        )) as total_amount_rev_of_ondc')
                    );

                    $query->where('b.is_reverted_to_ondc', true);
                } else {
                    $query->join('batch_review_statuses as brs', 'brs.batch_id', '=', 'b.id');
                    $query->where('brs.role_id', authRoleId());
                    $query->where('brs.status_id', $action);
                }
            }
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query
                    ->where('b.batch_number', 'like', "$search%")
                    ->orWhere('b.financial_year', 'like', "$search%")
                    ->orWhere('b.month', 'like', "$search%")
                    ->orWhereRaw("DATE_FORMAT(b.created_at, '%d-%m-%Y') like ?", ["$search%"]);
            });
        }


        $query->where('b.claim_type_id', $claimTypeId);
        $query->orderBy('b.created_at', 'desc');
        // dd($query->toSql());

        if ($page) {
            return $this->getDataTableResult(
                BatchResource::collection($query->paginate($limit))
            );
        }

        return BatchResource::collection($query->get());
    }


    public function getBatchClaims($batchId, $tabAction = null)
    {
        $batchDetails = DB::table('batches')
            ->select('is_resend_to_nsic', 'is_resend_to_nsic_finance', 'is_reverted_by_ca', 'is_reverted_by_ondc', 'is_reverted_to_nsic', 'is_rejected_by_ondc', 'is_reverted_to_ondc', 'is_reverted_to_snp', 'is_rejected_to_snp', 'is_sent_nsic_finance', 'status_id')
            ->where('id', $batchId)
            ->first();
        $action = $tabAction;
        if ($action !== self::SHARED_WITH_CA || $action !== self::CERTIFIED_BY_CA) {
            $action = ClaimReviewStatus::getIdByName($action);
        }
        
        $query = DB::table('batches as b')
            ->join('batch_claims as bc', 'b.id', '=', 'bc.batch_id')
            ->join('claims as c', 'bc.claim_id', '=', 'c.id')
            ->select(
                'c.id',
                'c.application_number',
                'c.msme_name',
                'c.team_registration_id',
                'c.msme_udyam_number',
                'c.msme_classification',
                'c.msme_category',
                'c.onboarding_date',
                'c.amount',
                'c.is_edited',
                'c.gst_charge',
                'c.gst_charge_amount',
                'c.status',
                'bc.batch_id',
                'bc.is_revert_to_ondc',
                'bc.is_revert_to_snp',
                'bc.is_reject_to_snp',
                'bc.is_revert_to_nsic',
                'bc.is_deleted'
            );

        if (hasRole('snp')) {
            if ($tabAction === self::SHARED_WITH_CA) {
                $query->addSelect(DB::raw('c.ca_review_status as status'));
                $query->whereNUll('bc.is_deleted');
            } elseif ($tabAction === self::CERTIFIED_BY_CA) {
               
                $query->addSelect(DB::raw('c.ca_review_status as status'));
                $query->where('bc.ca_review_status', ClaimReviewStatus::APPROVED->value);
            } elseif ($action === ClaimReviewStatus::PENDING->value) {
                $query->addSelect(DB::raw('c.status as status'));
                $query->whereNull('bc.is_deleted');
                //$query->where('bc.is_revert_to_snp', '=', 0); //commented by priya
                //$query->where('bc.is_reject_to_snp', '=', 0); //commented by priya
                $query->where('c.status', '<>', ClaimReviewStatus::APPROVED->value);
                $query->where('c.review_status', '<>', ClaimReviewStatus::APPROVED->value);
                $query->where('c.status', ClaimReviewStatus::PENDING); //added on 4.11
                // Apply revert/reject SNP filters only if batch flags are true //added by priya
                /*if (!empty($batchDetails->is_reverted_to_snp) || !empty($batchDetails->is_rejected_to_snp)) {
                    $query->where('bc.is_revert_to_snp', '=', 0);
                    $query->where('bc.is_reject_to_snp', '=', 0);
                }*/
            } elseif ($action === ClaimReviewStatus::APPROVED->value) {
                $query->addSelect(DB::raw('c.status as status'));
                $query->whereNUll('bc.is_deleted');
                $query->where('c.status', ClaimReviewStatus::APPROVED->value);
                $query->where('c.review_status', ClaimReviewStatus::APPROVED->value);
            } elseif ($action === ClaimReviewStatus::REVERTED->value) {
                $query->addSelect(DB::raw('(CASE WHEN bc.is_revert_to_snp = 1 THEN 5 ELSE c.status END) as status'));
                $query->where(function ($sub) use ($batchDetails) {
                    if ($batchDetails->is_reverted_by_ondc) {
                        $sub->orWhere(function ($q) {
                            $q->where('bc.status_id', ClaimReviewStatus::REVERTED)
                                ->where(function ($inner) {
                                    $inner->whereNull('bc.is_revert_to_ondc')
                                        ->orWhere('bc.is_revert_to_ondc', false);
                                });
                        });
                        $sub->where('bc.is_revert_to_snp', false);
                    }
                    if ($batchDetails->is_reverted_by_ca) {
                        $sub->orWhere(function ($q) {
                            $q->where('bc.status_id', ClaimReviewStatus::REVERTED)
                                ->where(function ($inner) {
                                    $inner->whereNull('bc.is_revert_to_ondc')
                                        ->orWhere('bc.is_revert_by_ca', true);
                                });
                        });
                        $sub->where('bc.is_revert_to_snp', false);
                    }

                    if ($batchDetails->is_reverted_to_snp) {
                        $sub->orWhere(function ($q) {
                            $q->where('bc.status_id', ClaimReviewStatus::REVERTED)
                                ->where('bc.is_revert_to_snp', true)
                                ->where('c.status', ClaimReviewStatus::REVERTED) //added on 4.11
                                ->where(function ($inner) {
                                    $inner->whereNull('bc.is_revert_to_ondc')
                                        ->orWhere('bc.is_revert_to_ondc', false);
                                });
                        });
                    }
                });
                $query->where(function ($inner) {
                    $inner
                        ->where('bc.is_revert_to_nsic', 0)
                        ->orWhereNull('bc.is_revert_to_nsic');
                });
                $query->whereNULL('bc.is_moved_to_drafts');
            } elseif ($action === ClaimReviewStatus::REJECTED->value) {

                $query->addSelect(DB::raw('(CASE WHEN bc.is_reject_to_snp = 1 THEN 4 ELSE c.status END) as status'));
                $query->where('c.status', ClaimReviewStatus::REJECTED->value);
                //$query->orWhere('c.ondc_review_status', ClaimReviewStatus::REJECTED->value); //commented by priya
                //$query->orWhere('c.ca_review_status', ClaimReviewStatus::REJECTED->value); //commented by priya
                //$query->orWhere('c.nsic_review_status', ClaimReviewStatus::REJECTED->value); //commented by priya
                //$query->orWhere('c.nsicfinance_review_status', ClaimReviewStatus::REJECTED->value); //commented by priya
            } elseif ($action === ClaimReviewStatus::PAYMENT_COMPLETED->value) {
                $query->addSelect(DB::raw('c.status as status'));
                $query->whereNUll('bc.is_deleted');
                $query->where('c.status', ClaimReviewStatus::PAYMENT_COMPLETED->value);
                $query->where('c.review_status', ClaimReviewStatus::PAYMENT_COMPLETED->value);
            }

            // if ($batchDetails->is_reverted_to_snp) {
            //     $query->where('bc.is_revert_to_snp', false);
            //     $query->where('bc.is_reject_to_snp', false);
            // }
        }

        if (hasRole('ca')) {

            $query->addSelect(DB::raw('bc.ca_review_status as status'));

            if ($action === ClaimReviewStatus::PENDING->value) {
            } elseif ($action === ClaimReviewStatus::APPROVED->value) {
                $query->addSelect(DB::raw('bc.ca_review_status as status'));
                $query->where('bc.ca_review_status', ClaimReviewStatus::APPROVED->value);
            } elseif ($action === ClaimReviewStatus::REVERTED->value) {
                if ($batchDetails->is_reverted_by_ca) {
                    $query->addSelect(DB::raw('bc.ca_review_status as status'));
                    $query->where('bc.ca_review_status', ClaimReviewStatus::REVERTED->value);
                }
            }
        }

        if (hasRole('ondc-admin')) {

            if ($action === ClaimReviewStatus::PENDING->value) {
                if ($batchDetails->is_reverted_to_ondc) {
                    $query->addSelect(DB::raw('5 as status'));
                    $query->where('bc.is_revert_to_ondc', true);
                } else {
                    $query->where('c.is_sent_ondc', true);
                    $query->addSelect(DB::raw('bc.ondc_review_status as status'));
                }
            } elseif ($action === ClaimReviewStatus::APPROVED->value) {
                $query->addSelect(DB::raw('bc.ondc_review_status as status'));
                $query->where('bc.ondc_review_status', ClaimReviewStatus::APPROVED->value);
            } elseif ($action === ClaimReviewStatus::REVERTED->value) {
                if ($batchDetails->is_reverted_by_ondc) {
                    $query->addSelect(DB::raw('bc.ondc_review_status as status'));
                    $query->where('bc.ondc_review_status', ClaimReviewStatus::REVERTED->value);
                }
            } elseif ($action === ClaimReviewStatus::REJECTED->value) {
                if ($batchDetails->is_rejected_by_ondc) {
                    $query->addSelect(DB::raw('bc.ondc_review_status as status'));
                    $query->where('bc.ondc_review_status', ClaimReviewStatus::REJECTED->value);
                }
            } elseif ($action === ClaimReviewStatus::PAYMENT_COMPLETED->value) {
                $query->addSelect(DB::raw('bc.ondc_review_status as status'));
                $query->where('bc.ondc_review_status', ClaimReviewStatus::PAYMENT_COMPLETED->value);
            }
        }

        if (hasRole('nsic')) {
            /*if ($action === ClaimReviewStatus::PENDING->value) {

                if ($batchDetails->is_resend_to_nsic || $batchDetails->is_reverted_to_nsic) {
                    //$query->addSelect(DB::raw('5 as status'));//commented by priya
                    if ($batchDetails->is_reverted_to_nsic) { //added by priya
                        //$query->addSelect(DB::raw('5 as status'));
                        $query->addSelect(DB::raw('(CASE WHEN bc.status_id = 6 THEN 6 ELSE 5 END) as status'));
                    }
                    $query->where(function ($query) {
                        $query->where('c.is_sent_nsic', true);
                        $query->where('bc.nsic_review_status', ClaimReviewStatus::PENDING->value);
                    });
                    $query->orWhere('bc.is_revert_to_nsic', true);
                } else {
                    $query->addSelect(DB::raw('bc.nsic_review_status as status'));
                    $query->where('c.is_sent_nsic', true);
                }
            }*/

            if ($action === ClaimReviewStatus::PENDING->value) {

                if (($batchDetails->is_resend_to_nsic || $batchDetails->is_reverted_to_nsic) && $batchDetails->is_sent_nsic_finance == 1) {
                    //$query->addSelect(DB::raw('5 as status'));//commented by priya
                    if ($batchDetails->is_reverted_to_nsic) { //added by priya
                        //$query->addSelect(DB::raw('5 as status'));
                        $query->addSelect(DB::raw('(CASE WHEN bc.status_id = 6 THEN 6 ELSE 5 END) as status'));
                    }
                    $query->where(function ($query) {
                        $query->where(function ($query) {
                            $query->where('c.is_sent_nsic', true);
                            $query->where('bc.nsic_review_status', ClaimReviewStatus::PENDING->value);
                        });
                        $query->orWhere('bc.is_revert_to_nsic', true);
                    });
                    $query->where('b.id', $batchId);
                } else if (($batchDetails->is_resend_to_nsic || $batchDetails->is_reverted_to_nsic) && $batchDetails->is_sent_nsic_finance == null) {
                    $query->addSelect(DB::raw('bc.nsic_review_status as status'));

                    $query->where(function ($query) use ($batchDetails) {
                        $query->where('c.is_sent_nsic', true);
                        $query->where(function ($query) use ($batchDetails) {
                            if ($batchDetails->is_resend_to_nsic) {
                                $query->where('bc.is_resend_process_by_nsic', true);
                                $query->orWhere('bc.status_id', ClaimReviewStatus::PENDING->value);
                            }
                        });
                        $query->whereIn('bc.nsic_review_status', [ClaimReviewStatus::PENDING->value, ClaimReviewStatus::APPROVED->value, ClaimReviewStatus::REVERTED->value]);
                    });
                    $query->where('b.id', $batchId);
                } else {
                    $query->addSelect(DB::raw('bc.nsic_review_status as status'));
                    $query->where('c.is_sent_nsic', true);
                    $query->where('b.id', $batchId);
                }
            } elseif ($action === ClaimReviewStatus::APPROVED->value) {
                $query->addSelect(DB::raw('bc.nsic_review_status as status'));
                $query->where('bc.nsic_review_status', ClaimReviewStatus::APPROVED->value);
            } elseif ($action === ClaimReviewStatus::REVERTED->value) {
                $query->addSelect(DB::raw('bc.nsic_review_status as status'));
                $query->where('bc.nsic_review_status', ClaimReviewStatus::REVERTED->value);
            } elseif ($action === ClaimReviewStatus::REJECTED->value) {
                $query->addSelect(DB::raw('bc.status_id as status'));
                //$query->where('c.nsic_review_status', ClaimReviewStatus::REJECTED->value);//commented by priya
                $query->where('bc.status_id', ClaimReviewStatus::REJECTED->value); //added by priya
                $query->where('bc.is_reject_to_snp', 1); //added by priya
                $query->where('bc.is_deleted', 1); //added by priya
            } elseif ($action === ClaimReviewStatus::PAYMENT_COMPLETED->value) {
                $query->addSelect(DB::raw('bc.nsic_review_status as status'));
                $query->where('bc.nsic_review_status', ClaimReviewStatus::PAYMENT_COMPLETED->value);
            }
        }

        if (hasRole('nsic-finance')) {

            if ($action === ClaimReviewStatus::PENDING->value) {
                if ($batchDetails->is_resend_to_nsic_finance &&  $batchDetails->status_id == 3) {
                    $query->addSelect(DB::raw('bc.nsicfinance_review_status as status'));
                    $query->where('c.is_sent_nsicfinance', true);
                    $query->where('bc.nsicfinance_review_status', ClaimReviewStatus::PENDING->value);
                } else if ($batchDetails->is_resend_to_nsic_finance &&  $batchDetails->status_id != 3) {
                    $query->addSelect(DB::raw('bc.nsicfinance_review_status as status'));
                    $query->where('c.is_sent_nsicfinance', true);
                    $query->whereIn('bc.nsicfinance_review_status', [ClaimReviewStatus::PENDING->value, ClaimReviewStatus::APPROVED->value, ClaimReviewStatus::REVERTED->value]);
                    $query->where('bc.status_id', ClaimReviewStatus::PENDING->value);
                } else {
                    $query->addSelect(DB::raw('bc.nsicfinance_review_status as status'));
                    $query->where('c.is_sent_nsicfinance', true);
                }
            } elseif ($action === ClaimReviewStatus::APPROVED->value) {
                $query->addSelect(DB::raw('bc.nsicfinance_review_status as status'));
                $query->where('bc.nsicfinance_review_status', ClaimReviewStatus::APPROVED->value);
            } elseif ($action === ClaimReviewStatus::REVERTED->value) {
                $query->addSelect(DB::raw('bc.nsicfinance_review_status as status'));
                $query->where('bc.nsicfinance_review_status', ClaimReviewStatus::REVERTED->value);
            } elseif ($action === ClaimReviewStatus::REJECTED->value) {
            } elseif ($action === ClaimReviewStatus::PAYMENT_COMPLETED->value) {
                $query->addSelect(DB::raw('bc.nsicfinance_review_status as status'));
                $query->where('bc.nsicfinance_review_status', ClaimReviewStatus::PAYMENT_COMPLETED->value);
            }
        }

        $query->where('bc.batch_id', $batchId);
        $query->where('b.id', $batchId);

        return ClaimResource::collection($query->get());
    }


    public function getBatchClaimsOld($batchId, $status = NULL)
    {
        $batchDetails = DB::table('batches')
            ->select('is_reverted_by_ondc', 'is_rejected_by_ondc', 'is_reverted_to_ondc', 'is_reverted_to_snp', 'is_rejected_to_snp')
            ->where('id', $batchId)
            ->first();
        //dd($batchDetails);
        if ($status != NULL) {
            $reviewStatus = ClaimReviewStatus::getIdByName($status);
        }
        $query = DB::table('batch_claims as bc')
            ->join('claims as c', 'c.id', '=', 'bc.claim_id')
            ->select(
                'c.id',
                'c.application_number',
                'c.msme_name',
                'c.team_registration_id',
                'c.msme_udyam_number',
                'c.msme_classification',
                'c.msme_category',
                'c.onboarding_date',
                'c.amount',
                'c.is_edited',
                'c.gst_charge',
                'c.gst_charge_amount',
                'c.status',
                'bc.batch_id',
                'bc.is_revert_to_ondc',
                'bc.is_revert_to_snp',
                'bc.is_reject_to_snp',
                'bc.is_revert_to_nsic',
            );
        if (hasRole('ca')) {
            $query->addSelect(DB::raw('c.ca_review_status as status'));
        }
        if (hasRole('ondc-admin')) {

            if ($reviewStatus === ClaimReviewStatus::REVERTED->value && 'bc.is_revert_to_ondc' == true) {
                $query->addSelect(DB::raw('5 as status'));
            } else {
                $query->addSelect(DB::raw('c.ondc_review_status as status'));
            }
        }
        if (hasRole('nsic')) {
            $query->addSelect(DB::raw('c.nsic_review_status as status'));
        }
        if (hasRole('nsic-finance')) {
            $query->addSelect(DB::raw('c.nsicfinance_review_status as status'));
        }
        if (hasRole('snp')) {
            //$query->addSelect(DB::raw('c.status as status'));
            if ($reviewStatus === ClaimReviewStatus::REVERTED->value && 'bc.is_revert_to_snp' == true) {
                $query->addSelect(DB::raw('5 as status'));
            } else if ($reviewStatus === ClaimReviewStatus::REJECTED->value && 'bc.is_reject_to_snp' == true) {
                $query->addSelect(DB::raw('4 as status'));
            } else {
                $query->addSelect(DB::raw('c.status as status'));
            }
        }

        $query->where('bc.batch_id', $batchId);
        if ($reviewStatus === ClaimReviewStatus::REVERTED->value) {
            /*if($batchDetails->is_reverted_by_ondc)
                {
                    $query->where('bc.status_id', ClaimReviewStatus::REVERTED);
                }*/
            if (hasRole('snp')) {
                $query->where(function ($sub) use ($batchDetails) {

                    if ($batchDetails->is_reverted_by_ondc) {

                        $sub->orWhere(function ($q) {
                            $q->where('bc.status_id', ClaimReviewStatus::REVERTED)
                                ->where(function ($inner) {
                                    $inner->whereNull('bc.is_revert_to_ondc')
                                        ->orWhere('bc.is_revert_to_ondc', false);
                                });
                        });
                        $sub->where('bc.is_revert_to_snp', false);
                    }

                    if ($batchDetails->is_reverted_to_snp) {

                        $sub->orWhere(function ($q) {
                            $q->where('bc.status_id', ClaimReviewStatus::REVERTED)
                                ->where('bc.is_revert_to_snp', true)
                                ->where(function ($inner) {
                                    $inner->whereNull('bc.is_revert_to_ondc')
                                        ->orWhere('bc.is_revert_to_ondc', false);
                                });
                        });
                    }
                });
            }
            if (hasRole('ondc-admin')) {
                if ($batchDetails->is_reverted_to_ondc) {
                    $query->where('bc.status_id', ClaimReviewStatus::REVERTED);
                    $query->where('bc.is_revert_to_ondc', true);
                }
            }
        } else if ($reviewStatus === ClaimReviewStatus::REJECTED->value) {

            if ($batchDetails->is_rejected_by_ondc && $batchDetails->is_rejected_to_snp == NULL) {
                $query->where('bc.status_id', ClaimReviewStatus::REJECTED);
                $query->where('bc.is_reject_to_snp', false);
            }
            if ($batchDetails->is_rejected_to_snp) {
                $query->where('bc.status_id', ClaimReviewStatus::REJECTED);
                $query->orWhere('bc.is_reject_to_snp', true);
            }
        } else {

            $query->whereNUll('bc.is_deleted');
            if (hasRole('snp') && $batchDetails->is_reverted_to_snp) {
                $query->where('is_revert_to_snp', False);
                $query->where('is_reject_to_snp', False);
            }
        }
        //dd($query->toSql());
        return ClaimResource::collection($query->get());
    }
}
