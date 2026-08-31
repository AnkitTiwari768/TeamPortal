<?php

declare(strict_types=1);

namespace App\Web\Claim;

enum ClaimReviewStatus: int
{
    case SUBMITTED = 1;
    case FORWARDED = 2;
    case APPROVED = 3;
    case REJECTED = 4;
    case REVERTED = 5;
    case PENDING = 6;
    case PAYMENT_COMPLETED = 7;
    case DRAFT = 0;
    case TEMPORARY = -1;

    public function label(): string
    {
        return match ($this) {
            self::SUBMITTED => 'Submitted',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::PENDING => 'Pending',
            self::REVERTED => 'Reverted',
            self::FORWARDED => 'Forwarded',
            self::DRAFT => 'Drafts',
            self::PAYMENT_COMPLETED => 'Payment Completed',
            self::TEMPORARY => 'Temporary'
        };
    }

    public static function getName(int $status): string
    {
        return match ($status) {
            1 => 'Submitted',
            2 => 'Forwared',
            3 => 'Approved',
            4 => 'Rejected',
            5 => 'Reverted',
            6 => 'Pending',
            7 => 'Payment Completed',
            0 => 'Draft',
            default => -1
        };
    }

    public static function getIdByName(string $name): int
    {
        return match ($name) {
            'Approved' => 3,
            'Rejected' => 4,
            'Reverted' => 5,
            'Payment Completed' => 7,
            'Drafts' => 0,
            'Pending' => 6,
            default => -1
        };
    }
}
