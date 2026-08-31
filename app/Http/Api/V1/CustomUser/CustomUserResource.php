<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\CustomUser;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomUserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'roles' => $this->roles,
            'status' => $this->status,
            'created_at' => $this->created_at
        ];
    }
}