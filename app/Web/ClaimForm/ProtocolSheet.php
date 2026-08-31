<?php

declare(strict_types=1);

namespace App\Web\ClaimForm;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ProtocolSheet implements FromArray, WithTitle, WithEvents
{
    // array() and title() here

    public function title(): string
    {
        return 'Key Protocols';
    }

    public function array(): array
    {
        return [

            // ===== TITLE =====
            ['CLAIM BULK UPLOAD – FIELD DEFINITIONS'],
            [],

            // ===== TABLE HEADER =====
            ['Column Header', 'Sample Value', 'Description'],

            // ===== MSE DETAILS =====
            ['MSE DETAILS', '', ''],

            ['seller_np_name', '', 'Seller Network Participant Name'],
            ['unique_team_registration_id', '1234', 'TEAM Scheme Portal Unique Registration ID (Do not modify)'],
            ['udyam_number', 'UDYAM-XX-00-0000000', 'Udyam Registration Number of MSE'],
            ['provider_id', '50000000', 'ONDC Seller Network Participant ID'],
            ['catalogue_score_id', '544567721', 'Unique Catalog Score ID'],
            ['catalogue_score_timestamp', '2026-01-27T14:01:59', 'Catalog Score creation timestamp (ISO format)'],
            ['catalogue_score_url', 'https://dummy/catalog.score', 'URL of Catalog Score Report'],
            ['credential_score_id', '9848931', 'Unique Credential Score ID'],
            ['credential_score_timestamp', '2026-01-27T14:01:59', 'Credential Score creation timestamp (ISO format)'],
            ['credential_score_url', 'https://dummy/credential.score', 'URL of Credential Score Report'],

            // ===== DOMAIN DETAILS =====
            [],
            ['TRANSACTION DOMAIN DETAILS', '', ''],

            ['txn_1_domain', 'ONDC:RET11', 'ONDC Domain'],
            ['txn_1_item_consolidated_category', 'F&B', 'Item Category as per selected domain'],

            // ===== TRANSACTION DETAILS =====
            [],
            ['TRANSACTION DETAILS', '', ''],

            ['txn_1_provider_id', '50000000', 'ONDC Seller Network Participant ID'],
            ['txn_1_network_order_id', '304961693', 'Unique Order Identifier'],
            ['txn_1_network_transaction_id', '304961693_txn', 'Unique Transaction Identifier'],
            ['txn_1_buyer_np_name', '', 'Buyer Network Participant Name'],
            ['txn_1_order_status', 'Completed', 'Terminal order status'],
            ['txn_1_order_creation_timestamp', '2026-01-27T14:01:59', 'Order creation timestamp (ISO format)'],
            ['txn_1_order_completed_timestamp', '2026-01-27T14:54:54', 'Order completion timestamp (ISO format)'],
            ['txn_1_invoice_number', '', 'Invoice Number'],
            ['txn_1_invoice_date', '27-01-2026', 'Invoice Date (DD-MM-YYYY)'],
            ['txn_1_cart_level_item_price', '330.00', 'Cart price inclusive of taxes'],
            ['txn_1_delivery_fee', '80.00', 'Delivery fee inclusive of taxes'],
            ['txn_1_total_fee', '410.00', 'Total = Item Price + Delivery Fee'],

            // ===== IMPORTANT NOTES =====
            [],
            ['IMPORTANT NOTES', '', ''],

            ['Do not change column headers', '', 'File will be rejected if headers are modified'],
            ['Timestamp format', 'YYYY-MM-DDTHH:MM:SS', 'Follow strict ISO format'],
            ['Excel Warning', '', 'Avoid pressing Enter in timestamp fields (may corrupt format)'],
            ['Numeric fields', '', 'Do not use text or special characters'],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {

                $sheet = $event->sheet->getDelegate();

                // 🔹 Title Styling
                $sheet->mergeCells('A1:C1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

                // 🔹 Header Row Bold
                $sheet->getStyle('A3:C3')->getFont()->setBold(true);

                // 🔹 Section Headers Bold
                $sheet->getStyle('A5:A5')->getFont()->setBold(true);   // MSE DETAILS
                $sheet->getStyle('A17:A17')->getFont()->setBold(true); // DOMAIN
                $sheet->getStyle('A20:A20')->getFont()->setBold(true); // TXN
                $sheet->getStyle('A35:A35')->getFont()->setBold(true); // NOTES

                // 🔹 Auto Width
                foreach (range('A', 'C') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // 🔹 Wrap Text for Description Column
                $sheet->getStyle('C')->getAlignment()->setWrapText(true);

                // 🔹 Freeze Header
                $sheet->freezePane('A4');
            },
        ];
    }
}
