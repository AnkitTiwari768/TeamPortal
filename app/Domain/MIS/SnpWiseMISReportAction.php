<?php

declare(strict_types=1);

namespace App\Domain\MIS;

use Illuminate\Support\Facades\DB;

final class SnpWiseMISReportAction
{
    public static function getNationalStateId(): ?string
    {
        return DB::table('states')
            ->where('slug', 'national')
            ->value('id');
    }

    /**
     * Canonical "Open MSME" role-selection matching used by the SNP Wise MIS Report,
     * the SNP dashboard "Open" card, and (when an SNP filter is applied) the
     * mis-reports-msme list, so all three stay consistent.
     */
    public static function applyRoleSelectionOpenFilters($query, array $roleSelections, ?string $nationalId): void
    {
        $query->where(function ($subQuery) use ($roleSelections, $nationalId) {
            foreach ($roleSelections as $role) {
                if (($role['role_name'] ?? '') !== 'Seller Network Participant (SNP)') {
                    continue;
                }
                $subQuery->orWhere(function ($roleQuery) use ($role, $nationalId) {
                    $roleQuery->whereRaw(
                        'JSON_CONTAINS(ms.product_category_id, ?)',
                        ['"' . trim($role['domain']) . '"']
                    );

                    if (($role['transaction_type_name'] ?? '') !== 'Both') {
                        $roleQuery->where(function ($subQuery) use ($role) {
                            $subQuery->where('ms.ondc_transaction_type_id', $role['transaction_type'])
                                ->orWhere('ms.ondc_transaction_type_id', 'b44fb78b-d49e-11f0-922a-00155d022d06'); // Both
                        });
                    } else {
                        $roleQuery->whereIn(
                            'ms.ondc_transaction_type_id',
                            ['9e7e1e8b-5578-11f0-81dc-00155d022d06', // B2B
                            '36523ead-533d-11f0-81dc-00155d022d06', // B2C
                            'b44fb78b-d49e-11f0-922a-00155d022d06' ] //Both
                        );
                    }

                    if (
                        !empty($role['serviceability']) &&
                        $role['serviceability'] != $nationalId
                    ) {
                        $roleQuery->whereIn(
                            'ms.state_id',
                            (array) $role['serviceability']
                        );
                    }
                });
            }
        });
    }

    public function execute(): array
    {
        // Get all SNPs first
        $snps = DB::table('team_snp_scheme')
            ->whereNotNull('snp_id')
            ->select('id', 'snp_id', 'organization_name', 'state_id', 'transaction_type', 'sub_domain', 'user_id')
            ->orderBy('organization_name', 'asc')
            ->where('status', 2)
            ->get();

        $result = [];
        $totalOpenMsme = 0;
        $totalOnboardedMsme = 0;
        foreach ($snps as $snp) {
            
            // Get SNP details from network_providers to get role selections
            $snpDetail = DB::table('network_providers')
                ->where('user_id', $snp->user_id)
                ->first();

            $roleSelections = $snpDetail ? json_decode($snpDetail->role_selection_details, true) : [];

        
            if (empty($roleSelections)) {
                $openCount = 0;
            } else {
                $nationalId = self::getNationalStateId();

                $openQuery = DB::table('team_msme_schemes as ms')
                    ->where('ms.select_snp', 0)
                    ->whereNull('ms.bpp_id')
                    ->whereNotNull('ms.major_activity')
                    ->where('ms.major_activity', '!=', '');

                self::applyRoleSelectionOpenFilters($openQuery, $roleSelections, $nationalId);

                $openCount = $openQuery->count();
            }
            
            // Get Selected MSMEs count
            $selectedCount = DB::table('team_snpmsme_mapping as tsm')
                ->join('team_msme_schemes as ms', 'ms.id', '=', 'tsm.msme_id')
                ->where('tsm.snp_id', $snp->id)
                ->where('ms.select_snp', 1)
                ->where('tsm.status', 0)
                ->whereNotNull('ms.major_activity')
                ->where('ms.major_activity', '!=', '')
                ->count();
            
            // Get Onboarded MSMEs count
            $onboardedCount = DB::table('team_snpmsme_mapping as tsm')
                ->join('team_msme_schemes as ms', 'ms.id', '=', 'tsm.msme_id')
                ->where('tsm.snp_id', $snp->id)
                ->where('tsm.status', 1)
                ->whereNotNull('ms.major_activity')
                ->where('ms.major_activity', '!=', '')
                ->count();
            
            $totalOpenMsme += $openCount;
            $totalOnboardedMsme += $onboardedCount;

            $result[] = [
                'id' => $snp->id,
                'snp_id' => $snp->snp_id,
                'organization_name' => $snp->organization_name,
                'open_msme' => $openCount,
                'selected_msme' => $selectedCount,
                'onboarded_msme' => $onboardedCount,
            ];
        }

        // Same source/logic as the Dashboard "selectedCount" and Direct Selection By MSE list,
        // so the total stays consistent with those screens (not scoped to status=2 SNPs only).
        $totalSelectedMsme = DB::table('team_msme_schemes as ms')
            ->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
            ->where('ms.select_snp', 1)
            ->where('tsm.status', 0)
            ->whereNotNull('ms.major_activity')
            ->where('ms.major_activity', '!=', '')
            ->count();

        return [
            'rows' => $result,
            'totals' => [
                'total_open_msme' => $totalOpenMsme,
                'total_selected_msme' => $totalSelectedMsme,
                'total_onboarded_msme' => $totalOnboardedMsme,
            ],
        ];
    }
}