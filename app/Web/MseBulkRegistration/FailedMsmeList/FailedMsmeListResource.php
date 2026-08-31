<?php

declare(strict_types=1);

namespace App\Web\MseBulkRegistration\FailedMsmeList;

use App\Web\MseBulkRegistration\MseDraftBulkUploadService;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class FailedMsmeListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                        => $this->id,
            'udyam_no'                  => $this->udyam_no ?: '-',
            'mobile'                    => $this->mobile ?: '-',
            'product_category_id'       => $this->product_category_id ?: '-',
            'current_state_business_id' => $this->current_state_business_id ?: '-',
            'ondc_transaction_type_id'  => $this->ondc_transaction_type_id ?: '-',
            'status'                    => $this->status ?: '-',
            'snp_name'                  => $this->snp_name ?: '-',
            'ia_name'                   => $this->ia_name ?: '-',
            'source_type'               => (int) $this->role_type === MseDraftBulkUploadService::ROLE_TYPE_SNP
                ? 'SNP'
                : 'IA',
            'api_attempts'              => (int) $this->api_attempts,
            'created_at'                => $this->formatDate($this->created_at),
        ];
    }

    private function formatDate($value): string
    {
        if (empty($value) || str_starts_with((string) $value, '0000-00-00')) {
            return '-';
        }

        return Carbon::parse($value)->format('d-m-Y h:i A');
    }
}
