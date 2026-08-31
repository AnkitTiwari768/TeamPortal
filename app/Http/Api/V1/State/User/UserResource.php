<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
			'full_name' => ucwords($this->full_name),
			'email' => $this->email,
			'mobile' => $this->mobile,
            'username' => $this->username,
            'status' => $this->status,
            'state_name' => $this->state_name,
            'district_name' => $this->district_name,
            'country_name' => $this->country_name,
            'roles' => $this->roles,
        ];
    }
}