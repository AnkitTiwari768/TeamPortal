<?php

declare(strict_types=1);

namespace App\Domain\FundFlow;

readonly class ComponentUtilizationMappingDTO
{
    public function __construct(
        public string $financialYear,
        public ?string $durationId,
        public ?string $subDurationId,
        public string $targetMajorComponentId,
        public ?string $targetSubComponentId,
        public ?string $remarks,
        public ?float $totalMaxUtilizationAmount,
        public array $eligibleLines,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            financialYear: $data['financial_year'],
            durationId: $data['duration_id'] ?? null,
            subDurationId: $data['sub_duration_id'] ?? null,
            targetMajorComponentId: $data['target_major_component_id'],
            targetSubComponentId: $data['target_sub_component_id'] ?? null,
            remarks: $data['remarks'] ?? null,
            totalMaxUtilizationAmount: isset($data['total_max_utilization_amount']) ? (float)$data['total_max_utilization_amount'] : null,
            eligibleLines: $data['eligible_lines'] ?? [],
        );
    }
}
