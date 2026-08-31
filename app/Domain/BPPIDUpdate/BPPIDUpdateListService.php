<?php

declare(strict_types=1);

namespace App\Domain\BPPIDUpdate;

use App\Core\BaseService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class BPPIDUpdateListService extends BaseService
{
    protected array $columns = [
        2 => 'ms.bpp_id',
        3 => 'ms.bpp_updated_at',
        4 => 'ms.udyam_no',
    ];

    public function getList()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $reportKey = $filters['bppid_report_key_filter'] ?? null;

        if (!empty($reportKey)) {
            return $this->getUploadReportList($reportKey, (int) $limit, $search, (int) request('page', 1));
        }

        $query = $this->baseListQuery();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ms.udyam_no', 'like', "%$search%")
                    ->orWhere('ms.bpp_id', 'like', "%$search%");
            });
        }

        $query->orderBy($order, $dir);

        if ($page) {
            return $this->getDataTableResult(
                BppIdListResource::collection($query->paginate($limit))
            );
        }

        return BppIdListResource::collection($query->get());
    }

    /**
     * Paginates the cached rows of a single upload (see BppIdUploadReport)
     * instead of querying team_msme_schemes, so the DataTable can show the
     * complete uploaded batch - including unmapped rows that were never
     * saved to the database - right after an upload finishes.
     */
    private function getUploadReportList(string $reportKey, int $limit, ?string $search, int $page)
    {
        $report = BppIdUploadReport::find($reportKey);
        $rows = $report ? BppIdUploadReport::rows($report) : [];

        if ($search) {
            $rows = array_values(array_filter($rows, function ($row) use ($search) {
                return stripos((string) ($row['udyam_no'] ?? ''), $search) !== false
                    || stripos((string) ($row['new_bpp_id'] ?? ''), $search) !== false;
            }));
        }

        $total = count($rows);
        $limit = $limit ?: 10;
        $slice = array_slice($rows, ($page - 1) * $limit, $limit);

        return [
            'draw'            => (int) request('draw'),
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $slice,
            'current_page'    => $page,
            'next'            => null,
            'previous'        => null,
            'per_page'        => $limit,
        ];
    }

    /**
     * Full, unpaginated dataset for the Excel/PDF downloads - same base
     * scope as the on-screen list, so a download always matches the table.
     * $limit is used only by the PDF export: dompdf's table renderer grows
     * memory non-linearly (verified: 2,000 rows already exhausts 1GB) and
     * cannot render the complete list, unlike the Excel export below.
     */
    public function getExportRows(?int $limit = null)
    {
        $query = $this->baseListQuery()
            ->orderBy('ms.bpp_updated_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function getExportRowCount(): int
    {
        return $this->baseCountQuery()->count();
    }

    public function getSummaryCards(?string $reportKey = null): array
    {
        if (!empty($reportKey)) {
            $report = BppIdUploadReport::find($reportKey);

            return $report
                ? BppIdUploadReport::summary($report)
                : ['total' => 0, 'mapped' => 0, 'unmapped' => 0, 'duplicate' => 0, 'already_updated' => 0];
        }

        $total = $this->baseCountQuery()->count();
        $mapped = $this->baseCountQuery()
            ->whereNotNull('ms.bpp_id')
            ->where('ms.bpp_id', '!=', '')
            ->count();

        return [
            'total' => (int) $total,
            'mapped' => (int) $mapped,
            'unmapped' => (int) ($total - $mapped),
            'duplicate' => (int) $this->duplicateCountQuery()->count(),
            'already_updated' => 0,
        ];
    }

    /**
     * Scope: MSME records that selected an SNP (select_snp = 1), i.e. records
     * that actually went through the BPP ID mapping flow. There is no
     * persisted "old" BPP ID anywhere in the schema (bpp_id is overwritten in
     * place on every bulk update), so Old BPPID has no data source and is
     * always blank in the list/exports.
     */
    private function baseListQuery(): Builder
    {
        return DB::table('team_msme_schemes as ms')
            ->select(
                'ms.id',
                'ms.udyam_no',
                'ms.bpp_id',
                'ms.bpp_updated_at'
            )
            ->where('ms.select_snp', 1);
    }

    private function baseCountQuery(): Builder
    {
        return DB::table('team_msme_schemes as ms')
            ->where('ms.select_snp', 1);
    }

    /**
     * "Duplicate" = the record's BPP ID is currently shared with at least one
     * other select_snp=1 record. There is no per-upload history to detect a
     * duplicate at the moment it happened, so this is computed as a
     * data-quality flag against the current state.
     */
    private function duplicateCountQuery(): Builder
    {
        return DB::table('team_msme_schemes as ms')
            ->where('ms.select_snp', 1)
            ->whereNotNull('ms.bpp_id')
            ->where('ms.bpp_id', '!=', '')
            ->whereIn('ms.bpp_id', function ($q) {
                $q->select('bpp_id')
                    ->from('team_msme_schemes')
                    ->where('select_snp', 1)
                    ->whereNotNull('bpp_id')
                    ->where('bpp_id', '!=', '')
                    ->groupBy('bpp_id')
                    ->havingRaw('count(*) > 1');
            });
    }
}
