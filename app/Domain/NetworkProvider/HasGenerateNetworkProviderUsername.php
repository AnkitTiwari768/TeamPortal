<?php

declare(strict_types=1);

namespace App\Domain\NetworkProvider;

trait HasGenerateNetworkProviderUsername
{
    public function generateUsername(): string
    {
        $lastNumber = NetworkProvider::selectRaw('MAX(CAST(SUBSTRING(np_team_id, 7) AS UNSIGNED)) as max_num')
            ->value('max_num');

        $nextNumber = (int) ($lastNumber + 1);

        return static::formatUsername($nextNumber);
    }

    public function generateSnpUsername(): string
    {
        $lastNumber = DB::table('team_snp_scheme')->selectRaw('MAX(CAST(SUBSTRING(snp_id, 7) AS UNSIGNED)) as max_num')
            ->value('max_num');

        $nextNumber = (int) ($lastNumber + 1);

        return 'NP-' . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
    }

    protected static function formatUsername(int $number): string
    {
        return 'NP-' . str_pad((string) $number, 5, '0', STR_PAD_LEFT);
    }
}
