<?php

namespace App\Web\Claim;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClaimOrdersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $claimId;
    private $index = 0;

    public function __construct(string $claimId)
    {
        $this->claimId = $claimId;
    }

    public function collection()
    {
        return DB::table('claim_orders')
            ->where('claim_id', $this->claimId)
            ->orderBy('id', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'S.No.',
            'MSE TEAM ID',
            'Catalogue Score ID',
            'Credential Score ID',
            'Provider ID',
            'Domain',
            'Item Consolidated Category',
            'AOV Grouping Type',
            'Buyer NP Name',
            'Seller NP Name',
            'Transaction Network Order ID',
            'Invoice Number',
            'Invoice Date',
            'Network Transaction ID',
            'Order Created',
            'Order Completed',
            'Cart Level Item Price',
            'Delivery Fee',
            'Total Fee',
            'Status'
        ];
    }

    public function map($row): array
    {
        $this->index++;

        return [
            $this->index,
            $row->team_id,
            $row->catalogue_score_id ?? '',
            $row->credential_score_id ?? '',
            $row->provider_id ?? '',
            $row->domain,
            $row->item_consolidated_category,
            $row->aov_grouping_type,
            $row->buyer_np_name,
            $row->seller_np_name,
            $row->ondc_order_id,
            $row->invoice_number,
            $this->formatDateWithTime($row->invoice_date),
            $row->network_transaction_id,
            $this->formatDateWithTime($row->order_creation_timestamp),
            $this->formatDateWithTime($row->order_completed_timestamp),
            $row->cart_level_item_price,
            $row->delivery_fee,
            $row->total_fee,
            $row->order_status,
        ];
    }

    private function formatDateWithTime($value)
    {
        if (empty($value)) return null;
        try {
            $dateTime = Carbon::parse($value);
            $formattedDate = $dateTime->format('d-m-Y');
            if ($dateTime->format('H:i:s') !== '00:00:00') {
                return $formattedDate . ' (' . $dateTime->format('H:i:s') . ')';
            }
            return $formattedDate;
        } catch (\Exception $e) {
            return $value;
        }
    }
}
