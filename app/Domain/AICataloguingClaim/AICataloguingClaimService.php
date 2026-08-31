<?php

declare(strict_types=1);

namespace App\Domain\AICataloguingClaim;

use App\Domain\NetworkProvider\NetworkProvider;
use Illuminate\Support\Facades\DB;

class AICataloguingClaimService
{
    public function getNetworkProviderDetailsByUserID(string $userId)
    {
        return NetworkProvider::query()
            ->from('network_providers as np')
            ->select('np.organization_id', 'np.bppid_providerid', 'np.np_team_id')
            ->join('users as u', 'np.user_id', '=', 'u.id')
            ->where('np.user_id', $userId)
            ->first();
    }

    public function getDeclarationContent(string $key): ?string
    {
        return DB::table('declaration_types')
            ->where('status', true)
            ->where('declaration_key', $key)
            ->value('declaration_content');
    }
}
