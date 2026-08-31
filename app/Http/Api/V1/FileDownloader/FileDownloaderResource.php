<?php 

declare(strict_types=1);

namespace  App\Http\Api\V1\LegalOfficer;

use Illuminate\Http\Resources\Json\JsonResource;

class LegalOfficerResource extends JsonResource
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}