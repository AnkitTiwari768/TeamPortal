<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Module;

use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}