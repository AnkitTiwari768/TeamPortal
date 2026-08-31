<?php

namespace App\Domain\DemandGeneration;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;

class CsvTemplateExport implements FromArray, WithHeadings
{
    // Predefined headers for your template
    protected array $headers = [
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

    public function array(): array
    {
        // Empty row array; CSV will have just headers and no data
        return [];
    }

    public function headings(): array
    {
        return $this->headers;
    }
}
