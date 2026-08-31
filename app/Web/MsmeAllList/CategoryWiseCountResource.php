<?php

declare(strict_types=1);

namespace App\Web\MsmeAllList;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryWiseCountResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'mapped_msme_count' => (int) $this->mapped_msme_count,
        ];
    }
}
