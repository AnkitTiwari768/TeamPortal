<?php

declare(strict_types=1);

namespace App\Domain\Batch;

use App\Domain\ClaimType\ClaimTypeRepository;
use App\Traits\DataTable;
use Illuminate\Support\Facades\DB;

final readonly class BatchListQuery
{
    use DataTable;

    public function execute(?string $claimTypeSlug = null)
    {
        $claimTypeSlug = $claimTypeSlug ?? 'claim-for-catalogue-creation';
        $claimTypeId = app(ClaimTypeRepository::class)->getClaimTypeIdBySlug($claimTypeSlug);
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
        $action = $filters['review_status'] ?? null;


        if ($claimTypeSlug === 'claim-for-transportation-and-logistic' || $claimTypeSlug === 'claim-for-demand-generation') {
            $query = DB::table('dy_workflow_instances as wi')
                ->join('dy_workflow_states as ws', 'ws.id', '=', 'wi.current_state_id')
                ->join('dy_batches as b', 'b.id', '=', 'wi.entity_id')
                ->join('network_providers as snp', 'snp.user_id', '=', 'b.created_by')
                ->select('b.*', 'snp.organization_name as snp_name', 'snp.np_team_id as snp_id');
        } else {
            $query = DB::table('dy_workflow_instances as wi')
                ->join('dy_workflow_states as ws', 'ws.id', '=', 'wi.current_state_id')
                ->join('dy_batches as b', 'b.id', '=', 'wi.entity_id')
                ->join('team_snp_scheme as snp', 'snp.user_id', '=', 'b.created_by')
                ->select('b.*', 'snp.snp_name', 'snp.snp_id');
        }


        if (hasRole('snp') || hasRole('lsp') || hasRole('bnp')) {
            if ($action === BatchStatus::PENDING->label()) {
                $query->whereIn('b.status', [
                    BatchStatus::PENDING->value,
                    BatchStatus::SENT_TO_ONDC->value,
                    BatchStatus::SENT_TO_NSIC->value,
                    BatchStatus::SENT_TO_NSIC_BY_SNP->value,
                    BatchStatus::SENT_TO_NSIC_FINANCE->value,
                ]);
            }


            if ($action === BatchStatus::APPROVED->label()) {
                $query->whereIn('b.status', [BatchStatus::APPROVED->value]);
            }


            if ($action === BatchStatus::PAYMENT_COMPLETED->label()) {
                $query->whereIn('b.status', [BatchStatus::PAYMENT_COMPLETED->value]);
            }


            if ($action === BatchStatus::SENT_TO_SNP->label()) {
                $query->whereIn('b.status', [BatchStatus::SENT_TO_SNP->value, BatchStatus::SENT_TO_CA->value]);
            }

            if ($action === BatchStatus::SENT_TO_SNP_FOR_INVOICE->label()) {
                $query->whereIn('b.status', [BatchStatus::SENT_TO_SNP_FOR_INVOICE->value]);
            }
        }


        if (hasRole('ca')) {
            $query->join('team_snpca_mapping as scm', 'scm.snp_user_id', '=', 'b.created_by');
            $query->where('scm.ca_user_id', authId());
            if ($action === BatchStatus::PENDING->label()) {
                $query->whereIn('b.status', [
                    BatchStatus::SENT_TO_CA->value,
                ]);
            }

            if ($action === BatchStatus::APPROVED->label()) {
                $query->whereIn('b.status', [
                    BatchStatus::CA_CERTIFICATE_UPLOADED->value,
                    BatchStatus::SENT_TO_SNP_FOR_INVOICE->value,
                    BatchStatus::SENT_TO_NSIC_BY_SNP->value,
                    BatchStatus::SENT_TO_NSIC_FINANCE->value,
                    BatchStatus::APPROVED->value,
                    BatchStatus::PAYMENT_COMPLETED->value,
                ]);
            }
        }

        if (hasRole('ondc-admin')) {
            if ($action === BatchStatus::PENDING->label()) {
                $query->where('b.status', BatchStatus::SENT_TO_ONDC->value);
            }
            if ($action === BatchStatus::APPROVED->label()) {
                $query->whereIn('b.status', [
                    BatchStatus::SENT_TO_NSIC->value,
                    BatchStatus::SENT_TO_SNP->value,
                    BatchStatus::SENT_TO_CA->value,
                    BatchStatus::SENT_TO_SNP_FOR_INVOICE->value,
                    BatchStatus::SENT_TO_NSIC_BY_SNP->value,
                    BatchStatus::SENT_TO_NSIC_FINANCE->value,
                    BatchStatus::APPROVED->value,

                ]);
            }

            if ($action === BatchStatus::PAYMENT_COMPLETED->label()) {
                $query->whereIn('b.status', [BatchStatus::PAYMENT_COMPLETED->value]);
            }
        }


        if (hasRole('nsic')) {
            dd($action);
            if ($action === BatchStatus::PENDING->label()) {
                $query->whereIn('b.status', [
                    BatchStatus::SENT_TO_NSIC->value,
                ]);
            }

            if ($action === BatchStatus::APPROVED->label()) {
                $query->whereIn('b.status', [
                    BatchStatus::SENT_TO_SNP->value,
                    BatchStatus::SENT_TO_CA->value,
                    BatchStatus::SENT_TO_SNP_FOR_INVOICE->value,
                    BatchStatus::SENT_TO_NSIC_FINANCE->value,
                    BatchStatus::APPROVED->value,
                ]);
            }

            if ($action === BatchStatus::SENT_TO_NSIC_BY_SNP->label()) {
                $query->whereIn('b.status', [BatchStatus::SENT_TO_NSIC_BY_SNP->value]);
            }

            if ($action === BatchStatus::PAYMENT_COMPLETED->label()) {
                $query->whereIn('b.status', [BatchStatus::PAYMENT_COMPLETED->value]);
            }
        }


        if (hasRole('nsic-finance')) {
            if ($action === BatchStatus::PENDING->label()) {
                $query->whereIn('b.status', [
                    BatchStatus::SENT_TO_NSIC_FINANCE->value,
                ]);
            }

            if ($action === BatchStatus::APPROVED->label()) {
                $query->whereIn('b.status', [
                    BatchStatus::APPROVED->value,
                ]);
            }


            if ($action === BatchStatus::PAYMENT_COMPLETED->label()) {
                $query->whereIn('b.status', [BatchStatus::PAYMENT_COMPLETED->value]);
            }
        }




        $query->where('b.claim_type_id', $claimTypeId);
        $query->where('wi.entity_type', EntityType::BATCH->value);

        if ($search) {
            $query->where(function ($q) use ($search, $claimTypeSlug) {
                $q->where('b.batch_number', 'like', "%{$search}%")
                    ->orWhereRaw("MONTHNAME(STR_TO_DATE(b.month, '%c')) LIKE ?", ["%{$search}%"])
                    ->orWhere('b.financial_year', 'like', "%{$search}%")
                    ->orWhereRaw("DATE_FORMAT(b.created_at, '%d-%m-%Y') LIKE ?", ["%{$search}%"]);

                if ($claimTypeSlug === 'claim-for-transportation-and-logistic') {
                    $q->orWhere('snp.organization_name', 'like', "%{$search}%");
                } else {
                    $q->orWhere('snp.snp_name', 'like', "%{$search}%");
                }
            });
        }

        $query->orderBy('b.created_at', 'desc');

        if ($page) {
            return $this->getDataTableResult(
                BatchResource::collection($query->paginate($limit))
            );
        }
        // dd($query->get());
        return BatchResource::collection($query->get());
    }
}
