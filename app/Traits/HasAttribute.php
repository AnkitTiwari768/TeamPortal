<?php

declare(strict_types=1);

namespace App\Traits;

use App\Web\Attributes\AttributeService;

trait HasAttribute
{
    protected function listOf(string $code, ?bool $skipParent = false, ?bool $dependency = false): array
    {
        return app(AttributeService::class)->getAttributeValuesByCode($code, $skipParent, $dependency);
    }
}
