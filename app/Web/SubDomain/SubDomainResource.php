<?php 

declare(strict_types=1);

namespace App\Web\SubDomain;

use Illuminate\Http\Resources\Json\JsonResource;

class SubDomainResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'domain_id' => $this->domain_id,
            'domain' => $this->domain,
            'name' => $this->name,
            'code' => $this->code,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}