<?php

namespace App\Web\Import;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AccountImportErrorExport implements FromArray, WithHeadings, WithMapping
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
            'unique_team_registration_id',
            'seller_np_name',
            'udyam_number',
            'provider_id',
            'domain',
            'item_consolidated_category',
            'network_order_id',
            'network_transaction_id',
            'buyer_np_name',
            'order_creation_timestamp',
            'order_completed_timestamp',
            'order_status',
            'invoice_number',
            'invoice_date',
            'cart_level_item_price',
            'delivery_fee',
            'total_fee'
        ];
    }

    public function map($row): array
    {
        return [
            collect($row['errors'])->flatten()->implode(' | '),
            $row['row_number'],

            $row['data']['team_id'] ?? '',
            $row['data']['seller_np_name'] ?? '',
            $row['data']['udyam_number'] ?? '',
            $row['data']['provider_id'] ?? '',

            $row['data']['domain'] ?? '',
            $row['data']['item_consolidated_category'] ?? '',
            $row['data']['network_order_id'] ?? '',
            $row['data']['network_transaction_id'] ?? '',
            $row['data']['buyer_np_name'] ?? '',
            $row['data']['order_creation_timestamp'] ?? '',
            $row['data']['order_completed_timestamp'] ?? '',
            $row['data']['order_status'] ?? '',
            $row['data']['invoice_number'] ?? '',
            $row['data']['invoice_date'] ?? '',
            $row['data']['cart_level_item_price'] ?? '',
            $row['data']['delivery_fee'] ?? '',
            $row['data']['total_fee'] ?? '',
        ];
    }
}
