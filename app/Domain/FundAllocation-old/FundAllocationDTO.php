<?php

declare(strict_types=1);

namespace App\Domain\FundAllocation;

readonly class FundAllocationDTO
{
    public function __construct(
        public string $financialYear,
        public string $durationId,
        public ?string $subDurationId,
        public string $sanctionOrderNumber,
        public string $sanctionOrderDate,
        public ?string $documentPath,
        public ?string $remarks,
        public bool $applyCarryForward,
        public array $allocationLines,
        public ?float $totalAmount = null,
        public ?float $totalAvailableAmount = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            financialYear: $data['financial_year'],
            durationId: $data['duration_id'],
            subDurationId: $data['sub_duration_id'] ?? null,
            sanctionOrderNumber: $data['sanction_order_number'],
            sanctionOrderDate: $data['sanction_order_date'],
            documentPath: $data['document_path'] ?? null,
            remarks: $data['remarks'] ?? null,
            applyCarryForward: isset($data['apply_carry_forward']) ? (bool) $data['apply_carry_forward'] : false,
            allocationLines: $data['allocation_lines'] ?? [],
            totalAmount: isset($data['total_amount']) ? (float) $data['total_amount'] : null,
            totalAvailableAmount: isset($data['total_available_amount']) ? (float) $data['total_available_amount'] : null,
        );
    }
}
