<?php

declare(strict_types=1);

namespace App\Web\RoleType;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleTypeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => (int) $this->status,
            'permission_count' => (int) ($this->permission_count ?? 0),
            'created_at' => $this->created_at
        ];
    }
}
