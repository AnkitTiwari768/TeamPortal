<?php

declare(strict_types=1);

namespace App\Web\RoleType;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleTypePermissionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'module_name' => $this->module_name ?? null
        ];
    }
}
