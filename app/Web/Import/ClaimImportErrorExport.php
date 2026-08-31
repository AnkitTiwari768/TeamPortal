<?php

namespace App\Web\Import;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClaimImportErrorExport implements FromArray, WithHeadings, WithMapping
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
            'seller np name',
            'TEAM Registration Id of MSE',
            'Udyam Number',
            'ONDC Seller Network ID of MSE',
            'Catalogue Score Report ID',
            'Catalogue Score Timestamp',
            'Catalogue Score URL',
            'Seller Credential Report ID',
            'Seller Credential Score Timestamp',
            'Seller Credential Score URL',
            // Transaction 1 Details
            'Txn 1 Domain',
            'Txn 1 Item Consolidated Category',
            'Txn 1 Network Order ID',
            'Txn 1 Network Transaction ID',
            'Txn 1 Buyer NP Name',
            'Txn 1 Order Status',
            'Txn 1 Order Creation Timestamp',
            'Txn 1 Order Completed Timestamp',
            'Txn 1 Invoice Number',
            'Txn 1 Invoice Date',
            'Txn 1 Cart Level Item Price',
            'Txn 1 Delivery Fee',
            'Txn 1 Total Fee',
            // Transaction 2 Details
            'Txn 2 Domain',
            'Txn 2 Item Consolidated Category',
            'Txn 2 Network Order ID',
            'Txn 2 Network Transaction ID',
            'Txn 2 Buyer NP Name',
            'Txn 2 Order Status',
            'Txn 2 Order Creation Timestamp',
            'Txn 2 Order Completed Timestamp',
            'Txn 2 Invoice Number',
            'Txn 2 Ivoice Date',
            'Txn 2 Cart Level Item Price',
            'Txn 2 Delivery Fee',
            'Txn 2 Total Fee',
        ];
    }

    public function map($row): array
    {
        $errors = $row['errors'];
        $errorMessages = [];

        // Collect all errors with their field names
        foreach ($errors->messages() as $field => $messages) {
            foreach ($messages as $message) {
                // Add field name to make it clear which transaction the error belongs to
                $errorMessages[] = $field . ': ' . $message;
            }
        }

        return [
            implode(' | ', $errorMessages),
            $row['row_number'],
            $row['data']['seller_np_name'] ?? '',
            $row['data']['team_id'] ?? '',

            $row['data']['udyam_number'] ?? '',
            $row['data']['provider_id'] ?? '',
            $row['data']['catalogue_score_report_id'] ?? '',
            $row['data']['catalogue_score_timestamp'] ?? '',
            $row['data']['catalogue_score_url'] ?? '',
            $row['data']['credential_report_id'] ?? '',
            $row['data']['credential_score_timestamp'] ?? '',
            $row['data']['credential_score_url'] ?? '',

            $row['data']['txn_1_domain'] ?? '',
            $row['data']['txn_1_item_consolidated_category'] ?? '',
            $row['data']['txn_1_network_order_id'] ?? '',
            $row['data']['txn_1_network_transaction_id'] ?? '',
            $row['data']['txn_1_buyer_np_name'] ?? '',
            $row['data']['txn_1_order_status'] ?? '',
            $row['data']['txn_1_order_creation_timestamp'] ?? '',
            $row['data']['txn_1_order_completed_timestamp'] ?? '',
            $row['data']['txn_1_invoice_number'] ?? '',
            $row['data']['txn_1_invoice_date'] ?? '',
            $row['data']['txn_1_cart_level_item_price'] ?? '',
            $row['data']['txn_1_delivery_fee'] ?? '',
            $row['data']['txn_1_total_fee'] ?? '',

            $row['data']['txn_2_domain'] ?? '',
            $row['data']['txn_2_item_consolidated_category'] ?? '',
            $row['data']['txn_2_network_order_id'] ?? '',
            $row['data']['txn_2_network_transaction_id'] ?? '',
            $row['data']['txn_2_buyer_np_name'] ?? '',
            $row['data']['txn_2_order_status'] ?? '',
            $row['data']['txn_2_order_creation_timestamp'] ?? '',
            $row['data']['txn_2_order_completed_timestamp'] ?? '',
            $row['data']['txn_2_invoice_number'] ?? '',
            $row['data']['txn_2_invoice_date'] ?? '',
            $row['data']['txn_2_cart_level_item_price'] ?? '',
            $row['data']['txn_2_delivery_fee'] ?? '',
            $row['data']['txn_2_total_fee'] ?? '',
        ];
    }
}
