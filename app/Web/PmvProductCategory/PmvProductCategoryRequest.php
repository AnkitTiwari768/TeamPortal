<?php 
declare(strict_types=1);

namespace App\Web\PmvProductCategory;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; 

class PmvProductCategoryRequest
{
    public static function getRules(?string $id = null): array 
    {
        return [
            'name' => [
                'bail',
                'required',
                'string',
                'min:1',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-\&\(\)\/]*$/',
                'unique:pm_vishwakarma_categories,name,' . $id,
            ],

            'subdomain_id' => [
                'required',
            ],

            'status' => [
                'required',
                'integer',
            ],
        ];
    }

    public static function messages(): array
    {
        return [
            // Name messages
            'name.required' => 'Category name is required.',
            'name.unique' => 'This category name already exists.',

            // Subdomain messages
            'subdomain_id.required' => 'Subdomain is required.',

            // Status messages
            'status.required' => 'Status is required.',
            'status.integer' => 'Status must be a valid number.',
        ];
    }
}