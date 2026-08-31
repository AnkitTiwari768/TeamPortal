<?php

declare(strict_types=1);

namespace App\Domain\Batch;

use App\Domain\ClaimType\ClaimTypeRepository;
use App\Traits\DataTable;
use Illuminate\Support\Facades\DB;
use App\Domain\Workflow\WorkflowService;

final readonly class BatchListQuery
{
    use DataTable;

    public function __construct(
        private WorkflowService $workflowService
    ) {}

    private function currentUserRole(): string
    {
        $role = session('active_role');

        if (!$role) {
            $role = DB::table('user_roles as ur')
                ->join('roles as r', 'r.id', '=', 'ur.role_id')
                ->where('ur.user_id', authId())
                ->value('r.slug');
        }

        return match (strtolower($role)) {
            'nsic' => 'NSIC',
            'nsic-maker' => 'NSIC Maker',
            'nsic-checker' => 'NSIC Checker',
            'nsic-finance' => 'NSIC Finance',
            'ondc-admin' => 'ONDC Admin',
            'snp' => 'SNP',
            'bnp' => 'BNP',
            'lsp' => 'LSP',
            'ca' => 'CA',
            default => $role,
        };
    }

    public function execute(?string $claimTypeSlug = null)
    {


        $claimTypeSlug = $claimTypeSlug ?? 'claim-for-catalogue-creation';

        $claimTypeId = app(ClaimTypeRepository::class)
            ->getClaimTypeIdBySlug($claimTypeSlug);

        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $action = strtolower($filters['review_status'] ?? ''); // pending, approved, etc.
        // dd($action);
        $role   = $this->currentUserRole();

        // ✅ Get workflow_type_id dynamically
        $workflowTypeId = $this->workflowService
            ->getWorkflowTypeId($claimTypeSlug); // use correct slug

        // ✅ Base Query
        $query = DB::table('dy_workflow_instances as wi')
            ->join('dy_workflow_states as ws', 'ws.id', '=', 'wi.current_state_id')
            ->join('dy_batches as b', 'b.id', '=', 'wi.entity_id');

        // ✅ Dynamic SNP Join
        if (in_array($claimTypeSlug, [
            'claim-for-transportation-and-logistic',
            'claim-for-demand-generation',
        ])) {
            $query->join('network_providers as snp', 'snp.user_id', '=', 'b.created_by')
                ->addSelect('snp.organization_name as snp_name', 'snp.np_team_id as snp_id');
        } elseif ($claimTypeSlug === 'claim-for-ai-cataloguing') {
            $query->addSelect([
                DB::raw("'N/A' AS snp_name"),
                DB::raw("'N/A' AS snp_id"),
            ]);
        } else {
            $query->join('team_snp_scheme as snp', 'snp.user_id', '=', 'b.created_by')
                ->addSelect('snp.organization_name as snp_name', 'snp.snp_id');
        }

        $query->addSelect('b.*');

        // ✅ Dynamic Role & Tab Filtering
        if (!empty($role)) {
            $visibleStateIds = $this->workflowService->getVisibleStateIds(
                $role,
                $action !== '' ? $action : null,
                $workflowTypeId
            );
            // dd($visibleStateIds, in_array('78824875-2da8-11f1-922a-00155d022d06', $visibleStateIds));
            // Directly pass array to whereIn. If empty, Laravel securely generates '0 = 1'
            if ((hasRole('snp') || hasRole('bnp') || hasRole('lsp')) && $filters['review_status'] === 'Upload Invoice') {
                $query
                    ->where('b.is_invoice_reupload_requested', 1)
                    ->orWhereIn('wi.current_state_id', $visibleStateIds);
            } else {
                $query->whereIn('wi.current_state_id', $visibleStateIds);
            }
        }

        // ✅ Special Case: CA Mapping
        if ($role === 'ca') {
            $query->join('team_snpca_mapping as scm', 'scm.snp_user_id', '=', 'b.created_by')
                ->where('scm.ca_user_id', authId());
        }

        // ✅ Mandatory Filters
        $query->where('b.claim_type_id', $claimTypeId)
            ->where('wi.entity_type', EntityType::BATCH->value);

        // ✅ Search
        if ($search) {
            $query->where(function ($q) use ($search, $claimTypeSlug) {
                $q->where('b.batch_number', 'like', "%{$search}%")
                    ->orWhereRaw("MONTHNAME(STR_TO_DATE(b.month, '%c')) LIKE ?", ["%{$search}%"])
                    ->orWhere('b.financial_year', 'like', "%{$search}%")
                    ->orWhereRaw("DATE_FORMAT(b.created_at, '%d-%m-%Y') LIKE ?", ["%{$search}%"]);

                if ($claimTypeSlug === 'claim-for-transportation-and-logistic') {
                    $q->orWhere('snp.organization_name', 'like', "%{$search}%");
                } else {
                    $q->orWhere('snp.organization_name', 'like', "%{$search}%");
                }
            });
        }

        // Skip rows where total_claims is 0
        $query->whereExists(function ($q) {
            $q->select(DB::raw(1))
                ->from('dy_batch_claims')
                ->whereColumn('dy_batch_claims.batch_id', 'b.id')
                ->whereNull('dy_batch_claims.is_deleted');
        });

        if (hasRole('snp') || hasRole('bnp') || hasRole('lsp') || hasRole('nsoc-maker')) {
            $query->where(function ($query) {
                $query
                    ->where('b.created_by', authId())
                    ->orWhere('b.created_by', auth()->user()->parent_user_id);
            });
        }

        $query->orderBy('b.created_at', 'desc');

        // ✅ Pagination
        if ($page) {
            $result = BatchResource::collection($query->paginate($limit));

            if ($result === [[]]) {
                $result = array_merge(...$result);
            }
            return $this->getDataTableResult(
                $result
            );
        }

        return BatchResource::collection($query->get());
    }
}
