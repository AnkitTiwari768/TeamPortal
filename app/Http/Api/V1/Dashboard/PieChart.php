<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Dashboard;

class PieChart extends Chart 
{
    public function __construct(
        private readonly string $_name,
        private readonly float $_value,
    )
    {
    }

    public static function create(string $name, float $value): PieChart 
    {
        return new self($name, $value);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->_name,
            'y' => $this->_value,
        ];
    }
}