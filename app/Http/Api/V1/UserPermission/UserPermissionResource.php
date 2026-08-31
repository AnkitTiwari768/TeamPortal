<?php 
namespace App\Http\Api\V1\UserPermission;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPermissionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'user_id' => $this->user_id,
            'permission_id' => $this->permission_id,
            'is_default' => $this->is_default,
            'group_permission_id' => $this->group_permission_id,
        ];
    }
}