<?php

declare(strict_types=1);

namespace App\Domain\DemandGeneration;

use App\Domain\NetworkProvider\NetworkProvider;
use Illuminate\Support\Facades\DB;

class DemandGenerationClaimService
{
    public function getNetworkProviderDetailsByUserID(string $userId)
    {
        return NetworkProvider::query()
            ->from('network_providers as np')
            ->select('np.organization_id', 'np.bppid_providerid')
            ->join('users as u', 'np.user_id', '=', 'u.id')

            ->where('np.user_id', $userId)
            ->first();
    }

    public function getProductCategories()
    {
        $categories = DB::table('sub_domains')
            ->select('id', 'name', 'aov_grouping_type')
            ->where('status', true)
            ->whereIn('aov_grouping_type', ['High AOV', 'Low AOV'])
            ->get()
            ->groupBy('aov_grouping_type');

        return [
            'highAov' => $categories['High AOV'] ?? collect(),
            'lowAov'  => $categories['Low AOV'] ?? collect(),
        ];
    }

    public function getDeclarationContent(string $key): ?string
    {
        return DB::table('declaration_types')
            ->where('status', true)
            ->where('declaration_key', $key)
            ->value('declaration_content');
    }
}
