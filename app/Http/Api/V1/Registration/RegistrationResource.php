<?php 

declare(strict_types=1);

namespace  App\Http\Api\V1\Registration;

use Illuminate\Http\Resources\Json\JsonResource;

class RegistrationResource extends JsonResource
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}