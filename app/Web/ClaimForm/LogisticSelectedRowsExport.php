<?php

declare(strict_types=1);

namespace App\Web\ClaimForm;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class LogisticSelectedRowsExport implements FromCollection, WithHeadings
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
                'team_id',                           // 2
                'udyam_no',                          // 3
                DB::raw('"" as provider_id'),        // 4 (Could be bpp_id?)
                DB::raw('"" as domain'),             // 5
                DB::raw('"" as item_consolidated_category'), // 6
                // DB::raw('"" as provider_id_duplicate'), // 7
                DB::raw('"" as network_order_id'),       // 8
                DB::raw('"" as network_transaction_id'), // 9
                DB::raw('"" as order_status'),           // 10
                DB::raw('"" as order_creation_timestamp'), // 11
                DB::raw('"" as order_completed_timestamp'), // 12
                DB::raw('"" as invoice_number'),         // 13
                DB::raw('"" as invoice_date'),           // 14
                DB::raw('"" as cart_level_item_price'),  // 15
                DB::raw('"" as delivery_fee'),           // 16
                DB::raw('"" as total_fee'),              // 17
            ])
            ->get();
    }

    public function headings(): array
    {
        return [
            'buyer np name',
            'seller np name',
            'team registration id',
            'udyam number',
            'provider id',
            'domain',
            'item consolidated category',
            'network order id',
            'network transaction id',
            'order status',
            'order creation timestamp',
            'order completed timestamp',
            'invoice number',
            'invoice date',
            'cart level item price',
            'delivery fee',
            'total fee',
        ];
    }
}
