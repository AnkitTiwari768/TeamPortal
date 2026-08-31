<?php

declare(strict_types=1);

namespace App\Domain\QMS;

use App\Traits\DataTable;
use Illuminate\Support\Facades\DB;
use App\Models\User;

final readonly class QueryListQuery
{
    use DataTable;

    public function execute(string $userId, ?string $claimSlug = null)
    {
        $userRoles = DB::table('user_roles')
            ->where('user_id', $userId)
            ->pluck('role_id')
            ->toArray();

        // Columns for sorting mapping
        $columns = [
            0 => 'subject',
            3 => 'status',
            4 => 'updated_at'
        ];

        [$limit, $order, $dir, $search, $page, $filters, $start] = $this->getDataTableParams(columns: $columns);

        $query = Query::query()
            ->with(['sender', 'senderRole', 'receiver', 'receiverRole'])
            ->select('qms_queries.*');

        // Filter for user involvement
        $query->where(function($q) use ($userId, $userRoles) {
            $q->where('sender_id', $userId)
              ->orWhereIn('sender_role_id', $userRoles)
              ->orWhere('receiver_id', $userId)
              ->orWhereIn('receiver_role_id', $userRoles);
        });

        // Restrict to queries raised against claims/batches of the given claim type.
        // Queries with no dy_queries link (generic, non-claim queries) are excluded
        // once a claim type scope is requested.
        if (!empty($claimSlug)) {
            $query->whereExists(function ($sub) use ($claimSlug) {
                $sub->select(DB::raw(1))
                    ->from('dy_queries')
                    ->leftJoin('claims', 'dy_queries.claim_id', '=', 'claims.id')
                    ->leftJoin('dy_batches', 'dy_queries.batch_id', '=', 'dy_batches.id')
                    ->leftJoin('claim_types as claim_claim_types', 'claims.claim_type_id', '=', 'claim_claim_types.id')
                    ->leftJoin('claim_types as batch_claim_types', 'dy_batches.claim_type_id', '=', 'batch_claim_types.id')
                    ->whereColumn('dy_queries.query_id', 'qms_queries.id')
                    ->where(function ($w) use ($claimSlug) {
                        $w->where('claim_claim_types.slug', $claimSlug)
                          ->orWhere('batch_claim_types.slug', $claimSlug);
                    });
            });
        }

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhereHas('sender', function($sq) use ($search) {
                      $sq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('senderRole', function($rq) use ($search) {
                      $rq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('receiver', function($rq) use ($search) {
                      $rq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('receiverRole', function($rq) use ($search) {
                      $rq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $query->orderBy($order, $dir);

        if ($page) {
            return $this->getDataTableResult(QueryListResource::collection(
                $query->paginate($limit)
            ));
        }

        return QueryListResource::collection($query->get());
    }
}
