<?php

declare(strict_types=1);

namespace App\Web\MsmeAllList;

use Illuminate\Http\Resources\Json\JsonResource;

class SnpCategoryListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'np_team_id' => $this->resource['np_team_id'] ?? null,
            'organization_name' => $this->resource['organization_name'] ?? null,
            'role_code' => $this->resource['role_code'] ?? null,
            'role_name' => $this->resource['role_name'] ?? null,
            'category' => $this->resource['category'] ?? null,
            'open_msme_count' => (int) ($this->resource['open_msme_count'] ?? 0),
            'transaction_type' => $this->resource['transaction_type'] ?? null,
            'ondc_domain_mapping' => $this->resource['ondc_domain_mapping'] ?? null,
            'serviceability' => $this->resource['serviceability'] ?? null,
            'status_name' => $this->resource['status_name'] ?? null,
        ];
    }
}
