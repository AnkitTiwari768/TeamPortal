<?php

declare(strict_types=1);

namespace App\Web\MsmeAllList;

use App\Core\BaseService;
use App\Domain\NetworkProvider\NetworkProviderStatus;
use App\Http\Services\CommonService;
use App\Traits\HasAttribute;
use DB;
use Illuminate\Database\Query\Builder;

class MsmeAllListService extends BaseService
{
    use HasAttribute;

    protected array $columns = [
        1 => 'ms.team_id',
        2 => 'ms.udyam_no',
        3 => 'ms.mobile',
        4 => 'ms.email',
        5 => 'ms.entrepreneur_name',
        6 => 'ms.enterprise_name',
        7 => 'ms.organisation_type',
        8 => 'st.name',
        9 => 'ms.created_at',
    ];

    public function getMsmeAllList()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
        $search ??= $this->escape_special_characters($search);

        $query = $this->baseListQuery();

        $this->applyFilters($query, $filters ?? []);

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('ms.udyam_no', 'like', "%$search%")
                    ->orWhere('ms.team_id', 'like', "%$search%")
                    ->orWhere('ms.mobile', 'like', "%$search%")
                    ->orWhere('ms.email', 'like', "%$search%")
                    ->orWhere('ms.entrepreneur_name', 'like', "%$search%")
                    ->orWhere('ms.enterprise_name', 'like', "%$search%")
                    ->orWhere('ms.organisation_type', 'like', "%$search%")
                    ->orWhere('st.name', 'like', "%$search%")
                    ->orWhere('av.attribute_value', 'like', "%$search%")
                    ->orWhereRaw("
                        EXISTS (
                            SELECT 1
                            FROM sub_domains sud
                            WHERE JSON_CONTAINS(
                                ms.product_category_id,
                                JSON_QUOTE(sud.id)
                            )
                            AND sud.name LIKE ?
                        )
                    ", ["$search%"])
                    ->orWhereRaw("DATE_FORMAT(ms.created_at, '%d-%m-%Y') like ?", ["$search%"]);
            });
        }

        $query->orderBy($order, $dir);

        if ($page) {
            return $this->getDataTableResult(
                MsmeAllListResource::collection($query->paginate($limit))
            );
        }

        return MsmeAllListResource::collection($query->get());
    }

    /**
     * Filtered dataset (no pagination, no search) for the Excel/PDF exports, so a
     * download always reflects the same six filters as the on-screen list. $limit
     * caps how many rows are fetched - used by the PDF export, since dompdf's
     * table renderer runs out of memory on datasets of a few thousand+ rows.
     */
    public function getExportRows(?int $limit = null)
    {
        [, , , , , $filters] = $this->getDataTableParams();

        $query = $this->baseListQuery();

        $this->applyFilters($query, $filters ?? []);

        $query->orderBy('ms.created_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function getExportRowCount(): int
    {
        [, , , , , $filters] = $this->getDataTableParams();

        return $this->summaryBaseQuery($filters ?? [])->count();
    }

    private function baseListQuery(): Builder
    {
        return DB::table('team_msme_schemes as ms')
            ->select(
                'ms.id',
                'ms.team_id',
                'ms.udyam_no',
                'ms.mobile',
                'ms.email',
                'ms.entrepreneur_name',
                'ms.enterprise_name',
                'ms.organisation_type',
                'ms.major_activity',
                'ms.product_category_id',
                'ms.incorporation_date',
                'ms.created_at',
                'ms.ondc_transaction_type_id',
                'st.name as state_name',
                'av.attribute_value as transaction_type'
            )
            ->leftJoin('states as st', 'st.id', '=', 'ms.state_id')
            ->leftJoin('attribute_values as av', 'av.id', '=', 'ms.ondc_transaction_type_id')
            ->whereNotNull('ms.major_activity')
            ->where('ms.major_activity', '!=', '');
    }

    /**
     * Open/Direct/Onboarded counts are computed by forcing msme_status to
     * each value in turn (reusing the exact same conditions applyFilters()
     * already uses for the Status dropdown/table), rather than respecting
     * whatever msme_status the user currently has selected. Coupling the
     * cards to that filter would zero out two of the three cards any time a
     * single status is selected, which defeats the purpose of a breakdown.
     * Every other active filter (dates, transaction type, product category,
     * state, SNP Approved) still narrows all three counts.
     */
    public function getSummaryCards()
    {
        [, , , , , $filters] = $this->getDataTableParams();
        $filters ??= [];

        $totalCount = $this->summaryBaseQuery($filters)->count();

        $openCount = $this->summaryBaseQuery(array_merge($filters, ['msme_status' => 'open']))->count();
        $directCount = $this->summaryBaseQuery(array_merge($filters, ['msme_status' => 'direct']))->count();
        $onboardedCount = $this->summaryBaseQuery(array_merge($filters, ['msme_status' => 'onboarded']))->count();

        return [
            'total_msme' => (int) $totalCount,
            'open_msme' => (int) $openCount,
            'direct_selection_msme' => (int) $directCount,
            'onboarded_msme' => (int) $onboardedCount,
        ];
    }

    private function summaryBaseQuery(array $filters): Builder
    {
        $query = DB::table('team_msme_schemes as ms')
            ->leftJoin('states as st', 'st.id', '=', 'ms.state_id')
            ->whereNotNull('ms.major_activity')
            ->where('ms.major_activity', '!=', '');

        $this->applyFilters($query, $filters);

        return $query;
    }

    /**
     * Applies the msme-all-list filters (From Date, To Date, Transaction Type,
     * Product Category, State, Status, SNP Approved) shared by both the list
     * query and the summary card counts, so filtering always stays in sync
     * between them.
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['from_dates'])) {
            $from = \Carbon\Carbon::createFromFormat('d-m-Y', $filters['from_dates'])->format('Y-m-d');
        }

        if (!empty($filters['to_dates'])) {
            $to = \Carbon\Carbon::createFromFormat('d-m-Y', $filters['to_dates'])->format('Y-m-d');
        }

        if (!empty($from) && !empty($to)) {
            $query->whereBetween('ms.created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
        }

        if (!empty($filters['ondc_transaction_type_id'])) {
            $query->where('ms.ondc_transaction_type_id', $filters['ondc_transaction_type_id']);
        }

        if (!empty($filters['product_category_id']) && is_array($filters['product_category_id'])) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters['product_category_id'] as $categoryId) {
                    $q->orWhereJsonContains('ms.product_category_id', $categoryId);
                }
            });
        }

        if (!empty($filters['state_id']) && is_array($filters['state_id'])) {
            $query->whereIn('ms.state_id', $filters['state_id']);
        }

        $status = $filters['msme_status'] ?? null;
        $snpApprovedId = $filters['snp_approved_id'] ?? null;

        // team_snpmsme_mapping is only ever needed for 'direct'/'onboarded'
        // status or the SNP Approved filter - joined once here (whichever
        // of those triggered it) so both can safely add their own
        // conditions below without joining the "tsm" alias twice.
        if (in_array($status, ['direct', 'onboarded'], true) || !empty($snpApprovedId)) {
            $query->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id');
        }

        if (!empty($status)) {
            if ($status === 'open') {
                $query->where('ms.select_snp', 0)
                    ->whereNull('ms.bpp_id');
            }

            if ($status === 'direct') {
                $query->where('ms.select_snp', 1)
                    ->where('tsm.status', 0);
            }

            if ($status === 'bulk') {
                $query->whereNotNull('ms.is_bulk_import');
            }

            if ($status === 'onboarded') {
                $query->where('tsm.status', 1);
            }
        }

        if (!empty($snpApprovedId)) {
            $query->where('tsm.snp_id', $snpApprovedId);
        }
    }

    public function getDropdownList(): array
    {
        $commonService = new CommonService();

        return [
            'ondc_types' => $this->listOf('types-of-transactions-preferred-on-ondc'),
            'state_id' => $commonService->getStates(countryId: ''),
            'sub_domains' => $commonService->getDropdownNewList('sub_domains', 'status', 'ASC', 'name', ['id', 'name']),
            'snp_approved' => $this->getApprovedSnpOptions(),
        ];
    }

    /**
     * SNP Approved dropdown options - approved SNPs (team_snp_scheme.status
     * = NetworkProviderStatus::APPROVE), matched by id against
     * team_snpmsme_mapping.snp_id.
     */
    private function getApprovedSnpOptions(): array
    {
        return DB::table('team_snp_scheme')
            ->whereNotNull('snp_id')
            ->where('status', NetworkProviderStatus::APPROVE->value)
            ->orderBy('organization_name', 'ASC')
            ->get(['id', 'snp_id', 'organization_name'])
            ->mapWithKeys(fn ($snp) => [$snp->id => ($snp->organization_name ?: $snp->snp_id)])
            ->toArray();
    }
}
