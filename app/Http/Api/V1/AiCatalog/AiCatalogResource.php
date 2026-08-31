<?php

declare(strict_types=1);

namespace App\Http\Api\V1\AiCatalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class AiCatalogResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return (array) $this->resource;
    }
}
