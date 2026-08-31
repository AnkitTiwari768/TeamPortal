<?php 
declare(strict_types=1);
namespace App\Web\AovCategory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; 

class AovCategoryRequest
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
                'unique:sub_domains,name,' . $id,
            ],

            // 'ondc_domain_id' => [
            //     'required',
            //     'string',
            //     'regex:/^(ONDC:[A-Z]{3}[0-9]+[A-Z]?|FIS[0-9]+|nic[0-9]+:[0-9]+)$/',
            //     'max:255',
            //     'unique:sub_domains,ondc_domain_id,' . $id,
            // ],
            'ondc_domain_id' => [
                    'required',
                    'string',
                    'max:255',
                ],

            'aov_category_id' => [
                'nullable'
            ],

            'aov_grouping_type' => [
                'required',
            ],

            'minimum_order_value' => [
                'nullable',
                'numeric',
                'min:0'
            ],

            'status' => [
                'required',
                'integer',
            ],
        ];
    }
}