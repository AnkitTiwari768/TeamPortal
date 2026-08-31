<?php

declare(strict_types=1);

namespace App\Web\ApplicationStatus;

use App\Domain\NetworkProvider\NetworkProviderStatus;
use Illuminate\Support\Facades\DB;

class SnpTracker
{
    private array $statusMap;

    public function __construct()
    {
        $this->statusMap = [
            NetworkProviderStatus::PENDING->value => [
                'key'     => 'pending',
                'badge'   => 'bg-warning text-dark',
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

    public function check(array $criteria, string $moduleKey): array
    {
        $query = DB::table('network_providers as np')
            ->leftJoin('users as u', 'u.id', '=', 'np.user_id');

        $query->where(function ($q) use ($criteria) {
            foreach ($criteria as $key => $value) {
                if ($key === 'email') $q->orWhere('np.email', $value);
                elseif ($key === 'mobile') $q->orWhere('u.mobile', $value);
            }
        });

        $record = $query->select('np.*')->orderByDesc('np.created_at')->first();

        if (!$record) return ['found' => false, 'status_key' => 'not_found', 'message' => 'No record found.'];

        $statusDef = $this->statusMap[(int) $record->status] ?? $this->statusMap[NetworkProviderStatus::PENDING->value];

        return [
            'status_key'   => $statusDef['key'],
            'message'      => 'NP / SNP Status Found.',
            'badge_class'  => $statusDef['badge'],
            'badge_icon'   => $statusDef['icon'],
            'found'        => true,
            'record'       => $record,
            'record_id'    => $record->np_team_id,
            'display_data' => [
                ['label' => 'Organization Name', 'value' => $record->organization_name],
                ['label' => 'Status', 'value' => ucfirst($statusDef['key'])],
            ],
        ];
    }
}
