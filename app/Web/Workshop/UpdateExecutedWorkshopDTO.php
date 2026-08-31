<?php

declare(strict_types=1);

namespace App\Web\Workshop;

readonly class UpdateExecutedWorkshopDTO
{
    public function __construct(
        public ?string $status,
        public ?string $remark,
        public ?int $noOfParticipants,
        public ?float $expenseAmount,
        public ?float $nsicFee,
        public ?string $tdsApplicable,
        public ?float $tdsPercentage,
        public ?float $netAmount,
        public ?string $sanctionOrderNumber,
        public ?string $sanctionOrderDate,
        public ?string $supportingDocument,
        public ?string $remarks
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'] ?? null,
            remark: $data['remark'] ?? null,
            noOfParticipants: isset($data['no_of_participants']) ? (int) $data['no_of_participants'] : null,
            expenseAmount: isset($data['expense_amount']) ? (float) $data['expense_amount'] : null,
            nsicFee: isset($data['nsic_fee']) ? (float) $data['nsic_fee'] : null,
            tdsApplicable: $data['tds_applicable'] ?? null,
            tdsPercentage: isset($data['tds_percentage']) ? (float) $data['tds_percentage'] : null,
            netAmount: isset($data['net_amount']) ? (float) $data['net_amount'] : null,
            sanctionOrderNumber: $data['sanction_order_number'] ?? null,
            sanctionOrderDate: $data['sanction_order_date'] ?? null,
            supportingDocument: $data['supporting_document'] ?? null,
            remarks: $data['remarks'] ?? null
        );
    }
}