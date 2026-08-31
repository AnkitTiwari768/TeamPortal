<?php

namespace App\Web\MisReport;

use App\Domain\Batch\BatchStatus;
use App\Domain\Claim\ClaimStatus;

enum FinalStatus: int
{
    case DRAFT = 0;
    case PENDING = 1;
    case APPROVED = 2;
    case REJECTED = 3;
    case PAYMENT_COMPLETED = 4;

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::PAYMENT_COMPLETED => 'Payment Completed',
        };
    }

    public function statuses(): array
    {
        return match ($this) {

            self::DRAFT => [
                ClaimStatus::DRAFT->value
            ],

            self::PENDING => [
                BatchStatus::PENDING->value,
                BatchStatus::BATCH_CREATED->value,
                BatchStatus::SENT_TO_ONDC->value,
                BatchStatus::SENT_TO_NSIC->value,
                BatchStatus::SENT_TO_SNP->value,
                BatchStatus::SENT_TO_CA->value,
                BatchStatus::SENT_TO_SNP_FOR_INVOICE->value,
                BatchStatus::SENT_TO_NSIC_BY_SNP->value,
                BatchStatus::SENT_TO_NSIC_FINANCE->value,
            ],

            self::APPROVED => [
                BatchStatus::APPROVED_BY_ONDC->value,
                BatchStatus::APPROVED_BY_NSIC->value,
                BatchStatus::APPROVED->value,
                BatchStatus::CA_CERTIFICATE_UPLOADED->value,
                BatchStatus::INVOICE_UPLOADED->value,
            ],

            self::REJECTED => [
                BatchStatus::REJECTED_BY_ONDC->value,
                BatchStatus::REJECTED_BY_NSIC->value,
                BatchStatus::REJECTED_NSIC_FINANCE->value,
            ],

            self::PAYMENT_COMPLETED => [
                BatchStatus::PAYMENT_COMPLETED->value
            ],
        };
    }

    public static function labelFromStatus(int $status): string
    {
        if ($status === ClaimStatus::DRAFT->value) {
            return 'Draft';
        }

        if (in_array($status, [
            BatchStatus::PENDING->value,
            BatchStatus::BATCH_CREATED->value,
            BatchStatus::SENT_TO_ONDC->value,
            BatchStatus::SENT_TO_NSIC->value,
            BatchStatus::SENT_TO_SNP->value,
            BatchStatus::SENT_TO_CA->value,
            BatchStatus::SENT_TO_SNP_FOR_INVOICE->value,
            BatchStatus::SENT_TO_NSIC_BY_SNP->value,
            BatchStatus::SENT_TO_NSIC_FINANCE->value,
        ])) {
            return 'Pending';
        }

        if (in_array($status, [
            BatchStatus::APPROVED_BY_ONDC->value,
            BatchStatus::APPROVED_BY_NSIC->value,
            BatchStatus::APPROVED->value,
            BatchStatus::CA_CERTIFICATE_UPLOADED->value,
            BatchStatus::INVOICE_UPLOADED->value,
        ])) {
            return 'Approved';
        }

        if (in_array($status, [
            BatchStatus::REJECTED_BY_ONDC->value,
            BatchStatus::REJECTED_BY_NSIC->value,
            BatchStatus::REJECTED_NSIC_FINANCE->value,
        ])) {
            return 'Rejected';
        }

        if ($status === BatchStatus::PAYMENT_COMPLETED->value) {
            return 'Payment Completed';
        }

        return 'N/A';
    }
}
