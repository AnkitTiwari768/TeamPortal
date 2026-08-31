<?php

declare(strict_types=1);

namespace App\Web\ClaimForm;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class PackagingSelectedRowsExport implements FromCollection, WithHeadings
{
    protected $ids;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        if (empty($this->ids)) {
            return collect([]);
        }

        return DB::table('team_msme_schemes')->whereIn('id', $this->ids)
            ->select([
                DB::raw('"" as buyer_np_name'),      // 0
                DB::raw('"" as seller_np_name'),     // 1
                'team_id',                           // 2 - This will be prefilled
                DB::raw('"" as udyam_no'),
                DB::raw('"" as provider_id'),        // 4
                DB::raw('"" as catalogue_score_id'), // 5
                DB::raw('"" as credential_score_id'), // 6
                DB::raw('"" as domain'),             // 7
                DB::raw('"" as item_consolidated_category'), // 8
                DB::raw('"" as network_order_id'),       // 9
                DB::raw('"" as network_transaction_id'), // 10
                DB::raw('"" as order_status'),           // 11
                DB::raw('"" as order_creation_timestamp'), // 12
                DB::raw('"" as order_completed_timestamp'), // 13
                DB::raw('"" as invoice_number'),         // 14
                DB::raw('"" as invoice_date'),           // 15
                DB::raw('"" as cart_level_item_price'),  // 16
                DB::raw('"" as delivery_fee'),           // 17
                DB::raw('"" as total_fee'),              // 18
            ])
            ->get();
    }

    public function headings(): array
    {
        return [
            'Buyer NP Name',
            'Seller NP Name',
            'Team Registration ID',
            'Udyam Number',
            'Provider ID',
            'Catalogue Score ID',
            'Credential Score ID',
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
}
