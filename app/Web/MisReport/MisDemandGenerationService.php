<?php
declare(strict_types=1);
namespace App\Web\MisReport;
use App\Traits\DataTable;
use App\Core\BaseService;
use App\Http\Services\CommonService;
use App\Web\MisReport\MisDemandGenerationResource;
use App\Web\User\UserService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

use App\Traits\HasAttribute;
use DB;
use Carbon\Carbon;

use App\Domain\Claim\ClaimStatus;
use App\Domain\Batch\BatchStatus;
use App\Web\MisReport\FinalStatus;

class MisDemandGenerationService extends BaseService
{
    use DataTable, HasAttribute;

    // public function getDemandGenerationList(
    //     ?string $claimTypeSlug = null
    //  ): array {
    //     [
    //         $limit,
    //         $order,
    //         $dir,
    //         $search,
    //         $page,
    //         $filters,
    //     ] = $this->getDataTableParams();
    //     $search ??= $this->escape_special_characters($search);

    //     $claimTypeId = DB::table("claim_types")
    //         ->where("slug", $claimTypeSlug ?? "claim-for-demand-generation")
    //         ->value("id");

    //     $query = DB::table("claims as c")
    //         ->select(
    //             "c.id",
    //             "c.bpp_id",
    //             "c.snp_id",
    //             'u.first_name as snp_name',
    //             'c.application_number',
    //             "c.total_valid_records",
    //             "c.total_unique_mse_count",
    //             "c.onboarding_date",
    //             "c.status",
    //             "c.claim_status",
    //             "c.amount",
    //             "c.date_of_sku_update",
    //             "c.submitted_at",
    //             "c.created_at",
    //             "c.updated_at",
    //         )
    //          ->join('network_providers as np', 'np.np_team_id', '=', 'c.snp_id')
    //          ->join('users as u', 'u.id', '=', 'np.user_id')
    //          ->where("c.claim_type_id", $claimTypeId)
    //           ->where("c.claim_status", '!=', ClaimStatus::DRAFT->value)
    //           ->where("c.claim_status", '!=', ClaimStatus::PENDING->value);

    //     if (
    //         isset($search) &&
    //         !empty($search) &&
    //         $this->escape_special_characters($search)
    //     ) {
    //         $query->where(function ($query) use ($search) {
    //             $query
    //                 ->where("c.snp_id", "like", "$search%")
    //                 ->orWhere('c.application_number', 'like', "$search%")
    //                  ->orWhere('u.first_name', 'like', "$search%")
	// 				->orWhere('c.total_valid_records', 'like', "$search%")
    //                 ->orWhere('c.total_unique_mse_count', 'like', "$search%")
    //                 ->orWhere("c.amount", "like", "$search%")
    //                 ->orWhereRaw(
    //                     "DATE_FORMAT(c.onboarding_date, '%d-%m-%Y') like ?",
    //                     ["$search%"]
    //                 );
    //         });
    //     }

    //     if (!empty($filters["from_date"])) {
    //         $from = Carbon::createFromFormat(
    //             "d-m-Y",
    //             $filters["from_date"]
    //         )->format("Y-m-d");
    //     }

    //     if (!empty($filters["to_date"])) {
    //         $to = Carbon::createFromFormat(
    //             "d-m-Y",
    //             $filters["to_date"]
    //         )->format("Y-m-d");
    //     }

    //     if (!empty($from) && !empty($to)) {
    //         $query->whereBetween("c.created_at", [
    //             $from . " 00:00:00",
    //             $to . " 23:59:59",
    //         ]);
    //     } elseif (!empty($from)) {
    //         $query->whereDate("c.created_at", ">=", $from);
    //     } elseif (!empty($to)) {
    //         $query->whereDate("c.created_at", "<=", $to);
    //     }
	
    //     if (isset($filters['review_status']) && $filters['review_status'] !== '') {

    //         $finalStatus = FinalStatus::tryFrom((int)$filters['review_status']);

    //         if ($finalStatus) {
    //             $query->whereIn('c.claim_status', $finalStatus->statuses());
    //         }
    //     }

    //     $query->orderBy("c.created_at", "desc");

    //     if ($page) {
    //         return $this->getDataTableResult(
    //             MisDemandGenerationResource::collection(
    //                 $query->paginate($limit)
    //             )
    //         );
    //     }
    //     return MisDemandGenerationResource::collection($query->get());
    // }
    public function getDemandGenerationList(
    ?string $claimTypeSlug = null
): array {

    [
        $limit,
        $order,
        $dir,
        $search,
        $page,
        $filters,
    ] = $this->getDataTableParams();

    // ❌ DO NOT escape application number type strings
    $search = trim($search ?? '');

    $claimTypeId = DB::table("claim_types")
        ->where("slug", $claimTypeSlug ?? "claim-for-demand-generation")
        ->value("id");

    $query = DB::table("claims as c")
        ->select(
            "c.id",
            "c.bpp_id",
            "c.snp_id",
            'u.first_name as snp_name',
            'c.application_number',
            "c.total_valid_records",
            "c.total_unique_mse_count",
            "c.onboarding_date",
            "c.status",
            "c.claim_status",
            "c.amount",
            "c.date_of_sku_update",
            "c.submitted_at",
            "c.created_at",
            "c.updated_at",
        )
        ->join('network_providers as np', 'np.np_team_id', '=', 'c.snp_id')
        ->join('users as u', 'u.id', '=', 'np.user_id')
        ->where("c.claim_type_id", $claimTypeId)
        ->where("c.claim_status", '!=', ClaimStatus::DRAFT->value)
        ->where("c.claim_status", '!=', ClaimStatus::PENDING->value);

    /* =========================
       SEARCH FIX (IMPORTANT)
    ========================= */
    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('c.snp_id', 'like', "%$search%")
              ->orWhere('c.application_number', 'like', "%$search%") // ✅ FIXED HERE
              ->orWhere('u.first_name', 'like', "%$search%")
              ->orWhere('c.total_valid_records', 'like', "%$search%")
              ->orWhere('c.total_unique_mse_count', 'like', "%$search%")
              ->orWhere('c.amount', 'like', "%$search%")
              ->orWhereRaw("DATE_FORMAT(c.onboarding_date, '%d-%m-%Y') like ?", ["%$search%"]);
        });
    }

    /* =========================
       DATE FILTER
    ========================= */
    if (!empty($filters["from_date"])) {
        $from = Carbon::createFromFormat("d-m-Y", $filters["from_date"])
            ->format("Y-m-d");
    }

    if (!empty($filters["to_date"])) {
        $to = Carbon::createFromFormat("d-m-Y", $filters["to_date"])
            ->format("Y-m-d");
    }

    if (!empty($from) && !empty($to)) {
        $query->whereBetween("c.created_at", [
            $from . " 00:00:00",
            $to . " 23:59:59",
        ]);
    } elseif (!empty($from)) {
        $query->whereDate("c.created_at", ">=", $from);
    } elseif (!empty($to)) {
        $query->whereDate("c.created_at", "<=", $to);
    }

    /* =========================
       STATUS FILTER
    ========================= */
    if (!empty($filters['review_status'])) {

        $finalStatus = FinalStatus::tryFrom((int)$filters['review_status']);

        if ($finalStatus) {
            $query->whereIn('c.claim_status', $finalStatus->statuses());
        }
    }

    $query->orderBy("c.created_at", "desc");

    if ($page) {
        return $this->getDataTableResult(
            MisDemandGenerationResource::collection(
                $query->paginate($limit)
            )
        );
    }

    return MisDemandGenerationResource::collection($query->get());
}

}
