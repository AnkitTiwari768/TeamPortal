<?php

declare(strict_types=1);

namespace App\Domain\Claim;

enum ClaimStatus: int
{
    case APPROVED = 1;
    case REJECTED = 2;
    case PENDING  = 3;
    case DRAFT = 0;

    public static function getIdByLabel(string $label): int
    {
        return match (strtolower($label)) {
            'drafts' => ClaimStatus::DRAFT->value,
            'pending' => ClaimStatus::PENDING->value
        };
    }

    public static function getLabelById(int $status): string
    {
        return match ($status) {
            ClaimStatus::DRAFT->value => 'drafts',
            ClaimStatus::PENDING->value => 'pending'
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }
}
