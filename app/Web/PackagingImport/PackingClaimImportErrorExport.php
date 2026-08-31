<?php

namespace App\Web\PackagingImport;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PackingClaimImportErrorExport implements FromArray, WithHeadings, WithMapping
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
    public function collection()
    {
        return collect();
    }

    public function headings(): array
    {
        return [
            'Row Number',
            'Errors',
            'Buyer NP Name',
            'Seller NP Name',
            'Team Registration ID',
            'Udyam Number',
            'Provider ID',
            'Domain',
            'Item Consolidated Category',
            'Network Order ID',
            'Network Transaction ID',
            'Order Status',
            'Order Creation Timestamp',
            'Order Completed Timestamp',
            'Invoice Number',
            'Invoice Date',
            'Cart Level Item Price',
            'Delivery Fee',
            'Total Fee',
        ];
    }
    public function map($row): array
    {
        return [
            $row['row_number'],
            collect($row['errors'])->flatten()->implode(' | '),
            $row['data']['buyer_np_name'] ?? '',
            $row['data']['seller_np_name'] ?? '',
            $row['data']['team_id'] ?? '',
            $row['data']['udyam_number'] ?? '',
            $row['data']['provider_id'] ?? '',
            $row['data']['domain'] ?? '',
            $row['data']['item_consolidated_category'] ?? '',
            $row['data']['network_order_id'] ?? '',
            $row['data']['network_transaction_id'] ?? '',
            $row['data']['order_status'] ?? '',
            $row['data']['order_creation_timestamp'] ?? '',
            $row['data']['order_completed_timestamp'] ?? '',
            $row['data']['invoice_number'] ?? '',
            $row['data']['invoice_date'] ?? '',
            $this->formatNumericValue($row['data']['cart_level_item_price'] ?? null),
            $this->formatNumericValue($row['data']['delivery_fee'] ?? null),
            $this->formatNumericValue($row['data']['total_fee'] ?? null),
        ];
    }

    private function formatNumericValue($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        // Convert to string to preserve 0 values
        return (string) $value;
    }
}