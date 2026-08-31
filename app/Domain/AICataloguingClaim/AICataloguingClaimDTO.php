<?php

declare(strict_types=1);

namespace App\Domain\AICataloguingClaim;

readonly class AICataloguingClaimDTO
{
    public function __construct(
        public string $gstType,
        public ?float $gstPercentage,
        public ?float $cgstPercentage,
        public ?float $sgstPercentage,
        public bool $declaration,
        public mixed $excelFile = null
    ) {}

    public static function fromArray(array $data, $file = null): self
    {
        return new self(
            gstType: (string) ($data['gst_type'] ?? ''),
            gstPercentage: isset($data['gst_percentage']) ? (float) $data['gst_percentage'] : null,
            cgstPercentage: isset($data['cgst_percentage']) ? (float) $data['cgst_percentage'] : null,
            sgstPercentage: isset($data['sgst_percentage']) ? (float) $data['sgst_percentage'] : null,
            declaration: (bool) ($data['declaration'] ?? false),
            excelFile: $file
        );
    }
}
