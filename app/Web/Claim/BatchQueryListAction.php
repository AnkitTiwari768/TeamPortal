<?php

declare(strict_types=1);

namespace App\Web\Claim;

use App\Domain\ClaimType\ClaimTypeRepository;
use App\Traits\DataTable;
use App\Domain\Claim\ClaimStatus;
use App\Domain\Batch\BatchStatus;
use App\Domain\Batch\EntityType;
use Illuminate\Support\Facades\DB;
use App\Web\Claim\BatchQueryListResource;

class BatchQueryListAction
{
    use DataTable;

    public function execute(?string $claimTypeSlug = null)
    {
        $claimTypeSlug = $claimTypeSlug ?? 'claim-for-catalogue-creation';
        $claimTypeId = app(ClaimTypeRepository::class)->getClaimTypeIdBySlug($claimTypeSlug);
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $query = DB::table('dy_workflow_instances as wi')
            ->join('dy_workflow_states as ws', 'ws.id', '=', 'wi.current_state_id')
            ->join('dy_batches as b', 'b.id', '=', 'wi.entity_id')
            ->join('team_snp_scheme as snp', 'snp.user_id', '=', 'b.created_by')
            ->select('b.*','b.id as batch_id', 'snp.snp_name', 'snp.snp_id');
			
			$query->where('b.claim_type_id', $claimTypeId);
			$query->whereIn('b.is_query', [1,2]);
			$query->where('wi.entity_type', EntityType::BATCH->value);
			if(hasRole('snp')){
				$query->where('b.created_by', authId());
			}
			$query->orderBy('b.created_at', 'desc');

			if ($page) {
				return $this->getDataTableResult(
					BatchQueryListResource::collection($query->paginate($limit))
				);
			}
	   
			return BatchQueryListResource::collection($query->get());
    }
}

