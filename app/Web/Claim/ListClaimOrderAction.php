<?php

declare(strict_types=1);

namespace App\Web\Claim;

use App\Traits\DataTable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ListClaimOrderAction
{
    use DataTable;

    public function execute(string $claimId): AnonymousResourceCollection|array
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
        $search ??= $this->escape_special_characters($search);

        $query = DB::table('claim_orders as co')
            ->select('co.*')
            ->where('co.claim_id', $claimId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('co.id', 'like', "$search%")
                  ->orWhere('co.team_id', 'like', "$search%")
                  ->orWhere('co.domain', 'like', "$search%")
                  ->orWhere('co.item_consolidated_category', 'like', "$search%")
                  ->orWhere('co.aov_grouping_type', 'like', "$search%")
                  ->orWhere('co.buyer_np_name', 'like', "$search%")
                  ->orWhere('co.seller_np_name', 'like', "$search%")
                  ->orWhere('co.ondc_order_id', 'like', "$search%")
                  ->orWhere('co.invoice_number', 'like', "$search%")
                  ->orWhereRaw("DATE_FORMAT(co.invoice_date, '%d-%m-%Y') like ?", ["$search%"])
                  ->orWhereRaw("DATE_FORMAT(co.invoice_date, '%d-%m-%Y (%H:%i:%s)') like ?", ["$search%"])
                  ->orWhere('co.network_transaction_id', 'like', "$search%")
                  ->orWhereRaw("DATE_FORMAT(co.order_creation_timestamp, '%d-%m-%Y') like ?", ["$search%"])
                  ->orWhereRaw("DATE_FORMAT(co.order_creation_timestamp, '%d-%m-%Y (%H:%i:%s)') like ?", ["$search%"])
                  ->orWhereRaw("DATE_FORMAT(co.order_completed_timestamp, '%d-%m-%Y') like ?", ["$search%"])
                  ->orWhereRaw("DATE_FORMAT(co.order_completed_timestamp, '%d-%m-%Y (%H:%i:%s)') like ?", ["$search%"])
                  ->orWhere('co.cart_level_item_price', 'like', "$search%")
                  ->orWhere('co.delivery_fee', 'like', "$search%")
                  ->orWhere('co.total_fee', 'like', "$search%")
                  ->orWhere('co.order_status', 'like', "$search%");
            });
        }

        $query->orderBy('co.id', 'asc');

        if ($page) {
            return $this->getDataTableResult(
                ClaimOrderResource::collection($query->paginate($limit))
            );
        }

        return ClaimOrderResource::collection($query->get());
    }
}
