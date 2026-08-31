<?php

declare(strict_types=1);

namespace App\Web\Workshop;

use Illuminate\Http\UploadedFile;

readonly class UpdateExecutedWorkshopDTO
{
    public function __construct(
        public int $noOfParticipants,
        public float $expenseAmount,
        public float $nsicFee,
        public string $tdsApplicable,
        public ?float $tdsPercentage,
        public ?float $netAmount,
        public string $sanctionOrderNumber,
        public string $sanctionOrderDate,
        public ?string $supportingDocument,
        public ?string $remarks
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            noOfParticipants: (int) $data['no_of_participants'],
            expenseAmount: (float) $data['expense_amount'],
            nsicFee: (float) $data['nsic_fee'],
            tdsApplicable: $data['tds_applicable'],
            tdsPercentage: isset($data['tds_percentage']) ? (float) $data['tds_percentage'] : null,
            netAmount: isset($data['net_amount']) ? (float) $data['net_amount'] : null,
            sanctionOrderNumber: $data['sanction_order_number'],
            sanctionOrderDate: $data['sanction_order_date'],
            supportingDocument: $data['supporting_document'] ?? null,
            remarks: $data['remarks'] ?? null
        );
    }
}
