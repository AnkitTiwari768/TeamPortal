<?php

declare(strict_types=1);

namespace App\Web\ClaimForm;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;


class SelectedRowsExport implements FromCollection, WithHeadings
{
    protected $ids;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return DB::table('team_msme_schemes')->whereIn('id', $this->ids)
            ->select([
                DB::raw('"" as seller_np_name'),
                'team_id',
                // 'udyam_no',
                // 'mobile',
                // 'email'
            ])
            ->get();
    }

    public function headings(): array
    {
        return [
            'seller_np_name',
            'unique_team_registration_id',
            'udyam_number',
            'provider_id',
            'catalogue_score_id',
            'catalogue_score_timestamp',
            'catalogue_score_url',
            'credential_score_id',
            'credential_score_timestamp',
            'credential_score_url',

            'txn_1_domain',
            'txn_1_item_consolidated_category',
            'txn_1_network_order_id',
            'txn_1_network_transaction_id',
            'txn_1_buyer_np_name',
            'txn_1_order_status',
            'txn_1_order_creation_timestamp',
            'txn_1_order_completed_timestamp',
            'txn_1_invoice_number',
            'txn_1_invoice_date',
            'txn_1_cart_level_item_price',
            'txn_1_delivery_fee',
            'txn_1_total_fee',

            'txn_2_domain',
            'txn_2_item_consolidated_category',
            'txn_2_network_order_id',
            'txn_2_network_transaction_id',
            'txn_2_buyer_np_name',
            'txn_2_order_status',
            'txn_2_order_creation_timestamp',
            'txn_2_order_completed_timestamp',
            'txn_2_invoice_number',
            'txn_2_invoice_date',
            'txn_2_cart_level_item_price',
            'txn_2_delivery_fee',
            'txn_2_total_fee',
        ];
    }
}
