<?php

namespace App\Domain\DemandGeneration;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DemandGenerationClaimImportErrorExport implements FromArray, WithHeadings, WithMapping
{
    protected array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Errors',
            'Row Number',
            'buyer_np_name',
            'unique_team_registration_id_of_the_mse',
            'udyam_number',
            'provider_id',
            'domain',
            'item_consolidated_category',
            'network_order_id',
            'network_transaction_id',
            'seller_np_name',
            'order_status',
            'order_creation_timestamp',
            'order_completed_timestamp',
            'invoice_number',
            'invoice_date',
            'cart_level_item_price',
            'delivery_fee',
            'total_fee'
        ];
    }

    public function map($row): array
    {
        $headings = $this->headings();

        // remove first 2 columns (Errors, Row Number)
        $columns = array_slice($headings, 2);

        $data = [
            collect($row['errors'])->flatten()->implode(' | '),
            $row['row_number'],
        ];

        foreach ($columns as $column) {
            $data[] = $row['data'][$column] ?? '';
        }

        return $data;
    }
}
