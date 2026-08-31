<?php

declare(strict_types=1);

namespace App\Web\ClaimForm;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;


class AccountSelectedRowsExport implements FromCollection, WithHeadings
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
                'team_id as unique_team_registration_id',  // First heading
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
            'credential_score_id',
            'domain',
            'item_consolidated_category',
            'network_order_id',
            'network_transaction_id',
            'buyer_np_name',
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
}
