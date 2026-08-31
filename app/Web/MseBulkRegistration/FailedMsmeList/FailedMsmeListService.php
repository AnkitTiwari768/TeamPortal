<?php

declare(strict_types=1);

namespace App\Web\MseBulkRegistration\FailedMsmeList;

use App\Web\MseBulkRegistration\MseDraftBulkUploadService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FailedMsmeListService
{
    /**
     * SNPs that own at least one failed draft, loaded live from team_snp_scheme.
     *
     * Scoping to SNPs actually present in the data keeps every option meaningful
     * rather than listing every registered SNP. Nothing is hard-coded.
     */
    public function getSnpOptions(): array
    {
        $query = DB::table('team_msme_scheme_drafts as d')
            ->join('team_snp_scheme as s', function ($join) {
                // Collation differs between the two columns — see FailedMsmeListAction.
                $join->on(
                    DB::raw('s.id COLLATE utf8mb4_general_ci'),
                    '=',
                    DB::raw('d.primary_snp_id COLLATE utf8mb4_general_ci')
                );
            })
            ->select('s.id', 's.snp_name', 's.snp_id')
            ->distinct()
            ->where('d.status', MseDraftBulkUploadService::STATUS_FAILED)
            ->whereNotNull('d.primary_snp_id');

        $this->applyRoleScope($query);

        return $query
            ->orderBy('s.snp_name')
            ->get()
            ->map(fn ($row) => [
                'id'   => $row->id,
                'text' => trim(($row->snp_name ?: '-') . ($row->snp_id ? " ({$row->snp_id})" : '')),
            ])
            ->all();
    }

    /**
     * Industrial Associations that own at least one failed draft.
     *
     * Uses the same ia.user_id = created_by relationship the rest of the project
     * uses to resolve the creating association.
     */
    public function getIaOptions(): array
    {
        $query = DB::table('team_msme_scheme_drafts as d')
            ->join('industrial_associations as ia', 'ia.user_id', '=', 'd.created_by')
            ->select('ia.id', 'ia.organization_name', 'ia.registration_number')
            ->distinct()
            ->where('d.status', MseDraftBulkUploadService::STATUS_FAILED);

        $this->applyRoleScope($query);

        return $query
            ->orderBy('ia.organization_name')
            ->get()
            ->map(fn ($row) => [
                'id'   => $row->id,
                'text' => trim(($row->organization_name ?: '-')
                    . ($row->registration_number ? " ({$row->registration_number})" : '')),
            ])
            ->all();
    }

    /**
     * Small header summary for the list screen.
     */
    public function getSummary(): array
    {
        $query = DB::table('team_msme_scheme_drafts as d')
            ->where('d.status', MseDraftBulkUploadService::STATUS_FAILED);

        $this->applyRoleScope($query);

        $rows = (clone $query)
            ->select('d.role_type', DB::raw('COUNT(*) as total'))
            ->groupBy('d.role_type')
            ->pluck('total', 'role_type');

        return [
            'total' => (int) $rows->sum(),
            'snp'   => (int) ($rows[MseDraftBulkUploadService::ROLE_TYPE_SNP] ?? 0),
            'ia'    => (int) ($rows[MseDraftBulkUploadService::ROLE_TYPE_IA] ?? 0),
        ];
    }

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
}
