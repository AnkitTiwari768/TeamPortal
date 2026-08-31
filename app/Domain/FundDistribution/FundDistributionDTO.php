<?php

declare(strict_types=1);

namespace App\Domain\FundDistribution;

readonly class FundDistributionDTO
{
    public function __construct(
        public string $financialYear,
        public string $durationId,
        public ?string $subDurationId,
        public string $majorComponentId,
        public ?string $subComponentId,
        public float $distributionAmount,
        public float $tdsPercentage,
        public ?string $sanctionOrderNumber,
        public ?string $sanctionOrderDate,
        public ?string $uploadDocument,
        public ?string $remarks,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            financialYear: $data['financial_year'],
            durationId: $data['duration_id'],
            subDurationId: $data['sub_duration_id'] ?? null,
            majorComponentId: $data['major_component_id'],
            subComponentId: $data['sub_component_id'] ?? null,
            distributionAmount: (float) $data['distribution_amount'],
            tdsPercentage: (float) $data['tds_percentage'],
            sanctionOrderNumber: $data['sanction_order_number'] ?? null,
            sanctionOrderDate: $data['sanction_order_date'] ?? null,
            uploadDocument: $data['upload_document'] ?? null,
            remarks: $data['remarks'] ?? null,
        );
    }
}
