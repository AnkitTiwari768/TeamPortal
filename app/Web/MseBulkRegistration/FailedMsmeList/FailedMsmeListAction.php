<?php

declare(strict_types=1);

namespace App\Web\MseBulkRegistration\FailedMsmeList;

use App\Traits\DataTable;
use App\Web\MseBulkRegistration\MseDraftBulkUploadService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Server-side DataTable source for the Failed MSME list.
 *
 * Reads the terminal-failure rows out of team_msme_scheme_drafts — the rows the
 * pending cron has given up on after MseDraftBulkUploadService::MAX_API_ATTEMPTS.
 */
class FailedMsmeListAction
{
    use DataTable;

    /** DataTable column index => orderable SQL column. Index 0 is the SN counter. */
    protected array $columns = [
        1 => 'd.udyam_no',
        2 => 'd.mobile',
        3 => 'd.product_category_id',
        4 => 'd.current_state_business_id',
        5 => 'd.ondc_transaction_type_id',
        6 => 'd.status',
        7 => 'd.created_at',
    ];

    public function execute(): array
    {
        // The 'd' prefix matters: the trait falls back to a bare "id" for the
        // order column, which is ambiguous once the joins are applied.
        [$limit, $order, $dir, $search, $page, $filters, $start] = $this->getDataTableParams('d');

        $limit = $limit > 0 ? $limit : 10;
        $dir   = strtolower((string) $dir) === 'asc' ? 'asc' : 'desc';

        $query = $this->baseQuery();

        $this->applyFilters($query, $filters);
        $this->applySearch($query, $search);

        $query->orderBy($order, $dir);

        // DataTables sends start/length rather than a page number.
        $currentPage = (int) floor(((int) $start) / $limit) + 1;

        return $this->getDataTableResult(
            FailedMsmeListResource::collection(
                $query->paginate($limit, ['*'], 'page', $currentPage)
            )
        );
    }

    private function baseQuery()
    {
        $query = DB::table('team_msme_scheme_drafts as d')
            /*
             * SNP: team_msme_scheme_drafts.primary_snp_id is stamped at upload
             * time from team_snp_scheme.id.
             *
             * primary_snp_id is utf8mb4_unicode_ci while team_snp_scheme.id is
             * utf8mb4_general_ci; joining them without an explicit COLLATE raises
             * MySQL error 1267 (illegal mix of collations).
             */
            ->leftJoin('team_snp_scheme as s', function ($join) {
                $join->on(
                    DB::raw('s.id COLLATE utf8mb4_general_ci'),
                    '=',
                    DB::raw('d.primary_snp_id COLLATE utf8mb4_general_ci')
                );
            })
            // IA: same relationship the rest of the project uses to resolve the
            // creating association (see MisReportService / SNPMSMEService / MsmeService).
            ->leftJoin('industrial_associations as ia', 'ia.user_id', '=', 'd.created_by')
            ->select([
                'd.id',
                'd.udyam_no',
                'd.mobile',
                'd.product_category_id',
                'd.current_state_business_id',
                'd.ondc_transaction_type_id',
                'd.status',
                'd.role_type',
                'd.api_attempts',
                'd.created_at',
                's.snp_name',
                'ia.organization_name as ia_name',
            ])
            ->where('d.status', MseDraftBulkUploadService::STATUS_FAILED);

        $this->applyRoleScope($query);

        return $query;
    }

    /**
     * SNP and IA users only see their own uploads (and their parent account's),
     * matching the behaviour of the existing draft list.
     */
    private function applyRoleScope($query): void
    {
        if (!hasRole('snp') && !hasRole('ia-registration')) {
            return;
        }

        $query->where(function ($q) {
            $q->where('d.created_by', Auth::id());

            $parentUserId = Auth::user()->parent_user_id ?? null;

            if ($parentUserId) {
                $q->orWhere('d.created_by', $parentUserId);
            }
        });
    }

    private function applyFilters($query, ?array $filters): void
    {
        $filters ??= [];

        // Fall back to top-level request params so the endpoint works whether the
        // blade nests values under filters[] or sends them flat.
        $get = function (string $key) use ($filters) {
            $value = $filters[$key] ?? request()->input($key);

            return is_string($value) ? trim($value) : $value;
        };

        $fromDate = $this->parseDate($get('from_date'));
        $toDate   = $this->parseDate($get('to_date'));

        if ($fromDate && $toDate) {
            $query->whereBetween('d.created_at', [$fromDate->startOfDay(), $toDate->endOfDay()]);
        } elseif ($fromDate) {
            $query->where('d.created_at', '>=', $fromDate->startOfDay());
        } elseif ($toDate) {
            $query->where('d.created_at', '<=', $toDate->endOfDay());
        }

        // SNP filter — team_snp_scheme.id as stamped on the draft.
        $snpId = $get('snp_id');
        if (!empty($snpId)) {
            $query->where('d.primary_snp_id', $snpId);
        }

        // IA filter — industrial_associations.id, resolved through created_by.
        $iaId = $get('ia_id');
        if (!empty($iaId)) {
            $query->where('ia.id', $iaId);
        }
    }

    private function applySearch($query, ?string $search): void
    {
        if (empty($search)) {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('d.udyam_no', 'like', "%{$search}%")
                ->orWhere('d.mobile', 'like', "%{$search}%")
                ->orWhere('d.product_category_id', 'like', "%{$search}%")
                ->orWhere('d.current_state_business_id', 'like', "%{$search}%")
                ->orWhere('d.ondc_transaction_type_id', 'like', "%{$search}%")
                ->orWhere('d.status', 'like', "%{$search}%")
                ->orWhere('s.snp_name', 'like', "%{$search}%")
                ->orWhere('ia.organization_name', 'like', "%{$search}%");
        });
    }

    /**
     * The date pickers submit dd-mm-yyyy; be tolerant of other formats too.
     */
    private function parseDate($value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('d-m-Y', (string) $value)->startOfDay();
        } catch (\Throwable $e) {
            try {
                return Carbon::parse((string) $value);
            } catch (\Throwable $e) {
                return null;
            }
        }
    }
}
