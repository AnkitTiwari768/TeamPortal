<?php 

declare(strict_types=1);

namespace  App\Http\Api\V1\Role;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}