<?php 

declare(strict_types=1);

namespace App\Web\Msme;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class SnpListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'snp_id' => $this->snp_id,
            'organization_name' => $this->organization_name ?? null,
            'snp_name' => $this->snp_name,
            'state_name' => $this->state_name ?? '',
            'domain_names' => $this->domain_names ?? '',
            
        ];
    }
}