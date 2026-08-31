<?php 
namespace App\Http\Api\V1\Permission;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionRoleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'permission_id' => $this->permission_id,
            'role_id' => $this->role_id,
            'role_name' => $this->role->name
        ];
    }
}