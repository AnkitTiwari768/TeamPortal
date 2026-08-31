<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Dashboard;

class BarChart extends Chart 
{
    private readonly array $result;
    private readonly string $categoryKey;
    private readonly string $valueKey;

    public function setResult(array $result) 
    {
        $this->_result = $result;
        
        return $this;
    }

    public function setCategoryKey(string $categoryKey)
    {
        $this->_categoryKey = $categoryKey;

        return $this;
    }

    public function setValueKey(string $valueKey)
    {
        $this->_valueKey = $valueKey;

        return $this;
    }

    public function create(): array
    {
        return [
            'categories' => parent::collection($this->_result, $this->_categoryKey),
            'data'       => parent::collection($this->_result, $this->_valueKey),
        ];
    }
}