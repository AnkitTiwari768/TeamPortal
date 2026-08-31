<?php

namespace App\Web\MseBulkRegistration;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MseDownload implements FromArray, WithHeadings, WithStyles
{
    protected $bulks;

    public function __construct($bulks)
    {
        $this->bulks = $bulks;
    }

    public function array(): array
    {
        $data = [];

        foreach ($this->bulks as $bulk) {
            $data[] = [
                $bulk->udyam_no,
                $bulk->mobile,
				$bulk->current_state_business_id,
				$bulk->attending_ondc_awareness_workshop,
				$bulk->turnover,
				$bulk->pan_no,
				$bulk->gstin_no,
				$bulk->product_category_id,
				$bulk->ondc_transaction_type_id,
				$bulk->error_message,
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'udyam_no',
            'mobile',
			'current_state_business_id',
			'attending_ondc_awareness_workshop',
            'turnover',
			'pan_no',
            'gstin_no',
			'product_category_id',
            'ondc_transaction_type_id',
			'error_message',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
            ],
        ];
    }
}
