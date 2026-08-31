<?php

declare(strict_types=1);

namespace App\Web\Claim;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ClaimOrderResource extends JsonResource
{
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

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'team_id' => $this->team_id,
            'domain' => $this->domain ?? '',
            'item_consolidated_category' => $this->item_consolidated_category ?? '',
            'aov_grouping_type' => $this->aov_grouping_type ?? '',
            'buyer_np_name' => $this->buyer_np_name ?? '',
            'seller_np_name' => $this->seller_np_name ?? '',
            'ondc_order_id' => $this->ondc_order_id ?? '',
            'invoice_number' => $this->invoice_number ?? '',
            'invoice_date' => $this->formatDateWithTime($this->invoice_date ?? ''),
            'network_transaction_id' => $this->network_transaction_id ?? '',
            'order_creation_timestamp' => $this->formatDateWithTime($this->order_creation_timestamp ?? ''),
            'order_completed_timestamp' => $this->formatDateWithTime($this->order_completed_timestamp ?? ''),
            'cart_level_item_price' => $this->cart_level_item_price ?? '',
            'delivery_fee' => $this->delivery_fee ?? '',
            'total_fee' => $this->total_fee ?? '',
            'order_status' => $this->order_status ?? '',
            'catalogue_score_id' => $this->catalogue_score_id ?? '',
            'credential_score_id' => $this->credential_score_id ?? '',
            'provider_id' => $this->provider_id ?? '',
        ];
    }
}
