<?php

declare(strict_types=1);

namespace App\Web\Claim;

use App\Utils\UuidGenerator;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LogisticClaimDetails
{
    public function __construct(
        private ClaimService $claimService,
        private string $claimTypeId,
    ) {}


    public function rules(): array
    {
        return [
            '0' => [
                'bail',
                'required',
                'max:100',
            ],
            '1' => [
                'bail',
                'required',
                'max:100',
            ],
            '2' => [
                'bail',
                'required',

            ],
            '3' => [
                'bail',
                'required',
                // 'in:ai,manual,AI,Manual',
                // function (string $attribute, mixed $value, \Closure $fail) {
                //     if ($value === 'ai' || $value === 'AI') {
                //         $fail('You are not eligible to file this claim');
                //     }
                // }
            ],
            '4' => [
                'bail',
                'required',
            ],
            '5' => [
                'bail',
                'nullable',
            ],
            '6' => [
                'bail',
                'nullable',
            ],
            '7' => [
                'bail',
                'required',
            ],
            '8' => [
                'bail',
                'required',
            ]
        ];
    }

    public function data($row): array
    {
        $msmeDetails = $this->claimService->getMsmeDetails((string) $row[0]);

        $claimDetails = [
            'id' => UuidGenerator::uuid7(),
            'claim_type_id' => $this->claimTypeId,
            'application_number' => $this->claimService->generateApplicationNumber(),
            'team_registration_id' => (string) $row[0],
            'seller_provider_id' => $row[1],
            'number_of_orders' => $row[2],
            'msme_transaction_type' => $msmeDetails->ondc_transaction_type_id ?? null,
            'created_at' => now(),
            'is_bulk' => true,
            'total_gmv' => $row[3],
            'net_sales' => $row[4],
            'total_commission' => $row[5],

            'msme_name' => $msmeDetails->enterprise_name ?? null,
            'msme_udyam_number' => $msmeDetails->udyam_no ?? null,
            'msme_classification' => $msmeDetails->msme_classification ?? null,
            'msme_category' => $msmeDetails->major_activity ?? null,
        ];

        $ondcOrderIdArray = explode(',', $row[6]);
        $ondcInvoiceNumberArray = explode(',', $row[7]);
        $ondcInvoiceDateArray = explode(',', $row[8]);

        $claimOrders = [
            [
                'id' => UuidGenerator::uuid7(),
                'claim_id' => $claimDetails['id'],
                'ondc_order_id' => $ondcOrderIdArray[0] ?? null,
                'invoice_number' => $ondcInvoiceNumberArray[0] ?? null,
                'invoice_date' => $ondcInvoiceDateArray[0] ? date('Y-m-d', strtotime($ondcInvoiceDateArray[0])) : null,
            ],
            [
                'id' => UuidGenerator::uuid7(),
                'claim_id' => $claimDetails['id'],
                'ondc_order_id' => $ondcOrderIdArray[1] ?? null,
                'invoice_number' => $ondcInvoiceNumberArray[1] ?? null,
                'invoice_date' => $ondcInvoiceDateArray[1] ? date('Y-m-d', strtotime($ondcInvoiceDateArray[1])) : null,
            ]
        ];

        return [
            $claimDetails,
            $claimOrders,
        ];
    }

    private function transformDate($value)
    {
        try {
            return Carbon::instance(Date::excelToDateTimeObject($value));
        } catch (\Exception $e) {
            return null;
        }
    }
}
