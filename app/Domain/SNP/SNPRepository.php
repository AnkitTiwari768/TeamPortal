<?php

declare(strict_types=1);

namespace App\Domain\SNP;

use Illuminate\Support\Facades\DB;

class SNPRepository
{
    public function getSnpIdByUserId(string $userId): string
    {
        $userIds = getSubUserAndParentIds($userId);

        if (hasRole('lsp') || hasRole('bnp')) {
            return (string) DB::table('users')
            ->select('np.np_team_id')
            ->join('network_providers as np', 'np.user_id', '=', 'users.id')
            ->whereIn('users.id', $userIds)
            ->value('np_team_id');
        }
        
        return (string) DB::table('users')
            ->select('team_snp_scheme.snp_id')
            ->join('team_snp_scheme', 'team_snp_scheme.user_id', '=', 'users.id')
            ->whereIn('users.id', $userIds)
            ->value('snp_id');
    }
}
