<?php

declare(strict_types=1);

namespace App\Domain\ClaimType;

class ClaimTypeRepository
{
    public function getClaimTypeIdBySlug(string $slug): string
    {
        // return '0378d757-7b90-4105-beb4-5a8af2d06eb4';
        return ClaimType::where('slug', $slug)->value('id');
    }

    public function getClaimTypeShortNameById(string $id): string
    {
        return ClaimType::where('id', $id)->value('short_name');
    }
}
