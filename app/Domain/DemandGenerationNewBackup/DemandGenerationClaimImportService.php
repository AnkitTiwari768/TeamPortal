<?php

declare(strict_types=1);

namespace App\Domain\DemandGeneration;

use App\Domain\Declaration\DeclarationService;
use App\Domain\NetworkProvider\NetworkProvider;
use Illuminate\Support\Facades\DB;

class DemandGenerationClaimImportService
{
    private static string $declarationKey = 'claim-for-demand-generation';

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

    public function getMsmeDetailsBulk(array $ids)
    {
        return DB::table('team_msme_schemes')
            ->select(
                'team_msme_schemes.*',
                'av.attribute_value as transaction_type'
            )
            ->leftJoin('attribute_values as av', 'av.id', '=', 'team_msme_schemes.ondc_transaction_type_id')
            ->where(function ($query) use ($ids) {
                $query->whereIn('team_msme_schemes.team_id', $ids)
                    ->orWhereIn('team_msme_schemes.udyam_no', $ids);
            })
            ->get();
    }

    public function getDeclarationContent(): ?string
    {
        return app(DeclarationService::class)->getDeclarationContent(self::$declarationKey);
    }
}
