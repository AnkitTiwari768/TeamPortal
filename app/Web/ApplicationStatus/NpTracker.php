<?php

declare(strict_types=1);

namespace App\Web\ApplicationStatus;

use App\Domain\NetworkProvider\NetworkProviderStatus;
use Illuminate\Support\Facades\DB;

class NpTracker
{
    private array $statusMap;

    public function __construct()
    {
        $this->statusMap = [
            NetworkProviderStatus::PENDING->value => [
                'key'     => 'pending',
                'badge'   => 'bg-primary text-dark',
                'icon'    => 'fa-hourglass-half',
            ],
            NetworkProviderStatus::APPROVE->value => [
                'key'     => 'approve',
                'badge'   => 'bg-success',
                'icon'    => 'fa-check-circle',
            ],
            NetworkProviderStatus::REJECT->value => [
                'key'     => 'reject',
                'badge'   => 'bg-danger',
                'icon'    => 'fa-times-circle',
            ],
            NetworkProviderStatus::REVERT->value => [
                'key'     => 'revert',
                'badge'   => 'bg-secondary',
                'icon'    => 'fa-undo',
            ],
        ];
    }

    /**
     * Search in team_snp_scheme joined with users and network_providers.
     */
    public function check(array $criteria, string $tabKey): array
    {
        $searchValue = reset($criteria);
        if (empty($searchValue)) {
            return [
                'found' => false,
                'status_key' => 'not_found',
                'message' => 'Please enter a search value.'
            ];
        }

        // Fetch all searchable fields for this module dynamically
        $searchableFields = DB::table('status_tab_fields as f')
            ->join('status_tabs as t', 'f.status_tab_id', '=', 't.id')
            ->where('t.tab_key', $tabKey)
            ->where('f.is_searchable', 1)
            ->pluck('f.field_key')
            ->toArray();

        // Ensure email and mobile are always searchable as per requirements
        $searchableFields = array_unique(array_merge($searchableFields, ['email', 'mobile']));

        $query = DB::table('team_snp_scheme as tss')
            ->leftJoin('users as u', 'tss.user_id', '=', 'u.id')
            ->leftJoin('network_providers as np', 'tss.network_provider_id', '=', 'np.id')
            ->select('tss.*', 'u.first_name', 'u.email', 'u.mobile', 'np.organization_name', 'np.bppid_providerid');

        $query->where(function ($q) use ($searchValue, $searchableFields) {
            foreach ($searchableFields as $field) {
                if (in_array($field, ['email', 'mobile', 'first_name'])) {
                    $q->orWhere('u.' . $field, 'like', "%$searchValue%");
                } elseif (in_array($field, ['organization_name', 'bppid_providerid', 'np_team_id'])) {
                    $q->orWhere('np.' . $field, 'like', "%$searchValue%");
                } else {
                    $q->orWhere('tss.' . $field, 'like', "%$searchValue%");
                }
            }
        });

        $record = $query->orderByDesc('tss.created_at')->first();

        if (!$record) {
            return [
                'found' => false,
                'status_key' => 'not_found',
                'message' => 'No SNP application found for the provided details.'
            ];
        }

        // ── Calculate MSME Counts from mapping table ──
        $record->total_mapped = DB::table('team_snpmsme_mapping')
            ->where('snp_id', $record->id)
            ->count();

        $record->onboarded = DB::table('team_snpmsme_mapping')
            ->where('snp_id', $record->id)
            ->where('status', 1)
            ->count();

        $statusDef = $this->statusMap[(int) $record->status] ?? $this->statusMap[NetworkProviderStatus::PENDING->value];

        return [
            'found'        => true,
            'status_key'   => $statusDef['key'],
            'message'      => 'SNP Registered on Team Portal.',
            'badge_class'  => $statusDef['badge'],
            'badge_icon'   => $statusDef['icon'],
            'record'       => $record,
        ];
    }
}
