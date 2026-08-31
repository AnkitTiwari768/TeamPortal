<?php

declare(strict_types=1);

namespace App\Web\MsmeAllList;

use App\Core\BaseService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Active sub_domains (product categories), each mapped to a count of
 * team_msme_schemes rows whose product_category_id JSON array contains
 * that category's id. Mirrors the "open_msme" filter combination used by
 * App\Domain\MIS\ProductCategoryMISReportAction (bpp_id IS NULL,
 * select_snp = 0, major_activity present).
 */
class CategoryWiseCountService extends BaseService
{
    protected array $columns = [
        1 => 'sd.name',
        2 => 'mapped_msme_count',
    ];

    public function getDropdownList(): array
    {
        return [
            'categories' => DB::table('sub_domains')
                ->where('status', config('constant.ACTIVE'))
                ->orderBy('name', 'ASC')
                ->get(['id', 'name']),
        ];
    }

    public function getCategoryWiseCountList()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
        $search = $search ? $this->escape_special_characters($search) : null;

        $query = $this->baseCategoryCountQuery($filters ?? []);

        if ($search) {
            $query->where('sd.name', 'like', "%$search%");
        }

        $query->orderBy($order, $dir);

        if ($page) {
            return $this->getDataTableResult(
                CategoryWiseCountResource::collection($query->paginate($limit))
            );
        }

        return CategoryWiseCountResource::collection($query->get());
    }

    /**
     * Applies the Category filter (and the fixed team_msme_schemes
     * conditions) shared by the on-screen list.
     */
    private function baseCategoryCountQuery(array $filters): Builder
    {
        $query = DB::table('sub_domains as sd')
            ->where('sd.status', config('constant.ACTIVE'))
            ->leftJoin('team_msme_schemes as ms', function ($join) {
                $join->whereRaw('JSON_CONTAINS(ms.product_category_id, JSON_QUOTE(CAST(sd.id AS CHAR)))');
            })
            ->selectRaw('
                sd.id,
                sd.name AS category,
                COUNT(DISTINCT CASE
                    WHEN ms.bpp_id IS NULL
                     AND ms.select_snp = 0
                     AND ms.major_activity IS NOT NULL
                     AND ms.major_activity <> \'\'
                    THEN ms.id
                END) AS mapped_msme_count
            ')
            ->groupBy('sd.id', 'sd.name');

        if (!empty($filters['category_id']) && is_array($filters['category_id'])) {
            $query->whereIn('sd.id', $filters['category_id']);
        }

        return $query;
    }
}
