<?php

declare(strict_types=1);

namespace App\Domain\FundCarryForward;

readonly class FundCarryForwardDTO
{
    public function __construct(
        public string $financialYear,
        public string $fromDurationId,
        public ?string $fromSubDurationId,
        public string $toDurationId,
        public ?string $toSubDurationId,
        public ?string $carryForwardDate,
        public ?string $remarks,
        public ?float $totalAmount,
        public array $carryForwardLines,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            financialYear: $data['financial_year'],
            fromDurationId: $data['from_duration_id'],
            fromSubDurationId: $data['from_sub_duration_id'] ?? null,
            toDurationId: $data['to_duration_id'],
            toSubDurationId: $data['to_sub_duration_id'] ?? null,
            carryForwardDate: $data['carry_forward_date'] ?? null,
            remarks: $data['remarks'] ?? null,
            totalAmount: isset($data['total_amount']) ? (float)$data['total_amount'] : null,
            carryForwardLines: $data['carry_forward_lines'] ?? [],
        );
    }
}
