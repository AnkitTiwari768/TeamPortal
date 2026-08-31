<?php 

namespace App\Http\Api\V1\Permission;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Api\V1\Permission\PermissionRoleResource;

class PermissionResource extends JsonResource
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}