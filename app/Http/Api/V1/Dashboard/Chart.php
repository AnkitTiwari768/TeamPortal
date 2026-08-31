<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Dashboard;

class Chart 
{
    protected static function collection(array $items, string $key): array
    {
        return array_map(function($item) use ($key) {
            return $item->{$key};
        }, $items);
    }
}