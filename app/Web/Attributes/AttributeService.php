<?php

declare(strict_types=1);

namespace App\Web\Attributes;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttributeService
{
    public function getAttributeValuesByCode(string $code, ?bool $skipParent = false, ?bool $dependency = false): array
    {
        $query = DB::table('attributes as a')
            ->join('attribute_values as av', 'a.id', '=', 'av.attribute_id');

        if (Str::isUuid($code) && $dependency) {
            $query->where('av.parent_id', $code);
        } else {
            $query->where('a.code', $code);
        }

        $query->where('av.status', true);

        if ($skipParent) {
            $query->whereNull('av.parent_id');
        }

        return $query
            ->orderBy('av.sort_order', 'asc')
            ->pluck('av.attribute_value', 'av.id')
            ->toArray();
    }
}
