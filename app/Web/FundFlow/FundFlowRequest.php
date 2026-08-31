<?php 

declare(strict_types=1);

namespace App\Web\FundFlow;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\AlphaSpace;
use App\Rules\UniqueFundFlow;

class FundFlowRequest
{
    public static function getRules(?string $id = null): array 
    {
        return [
			
			'financial_year' => [
                'bail',
                'required',
                'string',
                'regex:/^[0-9]{4}-[0-9]{4}$/'
                
            ],
            'duration' => [
                'bail',
                'required',
                'string',
                'regex:/^[a-zA-Z\s]+$/'                
            ],
            'duration_limit' => [
                'bail',
                'nullable',
                'string',    
                'regex:/^[a-zA-Z0-9\s]+$/'            
            ],
            'total_amount_allocated' => [
                'bail',
                'required',
                'string',     
                'regex:/^\d+(\.\d{1,5})?$/'           
            ],
            'personalities_details.*.major_component_id' => [
                'bail',
                'required',
                'string',
                'exists:major-components,id',
                /*new UniqueFundFlow(
                    request('financial_year'),
                    request('duration'),
                    request('duration_limit'),
                    $id, // allocation id
                    //request()->input('personalities_details.*.id') // map row id
                ),*/
            ],
            'personalities_details.*.component_id' => [
                'bail',
                'nullable',
                'string',
                'exists:components,id'
            ],
			'personalities_details.*.sub_component_id' => [
                'bail',
                'nullable',
                'string',
                'exists:sub-components,id',
                
            ],
			'personalities_details.*.amount' => [
                'bail',
                'required',
                'string',
                'regex:/^\d+(\.\d{1,2})?$/'    
                
            ],
            'sanction_order_no' => [
                'bail',
                'required',
                'string',
                'regex:/^[a-zA-Z0-9\s]*$/',
            ],
            'sanction_order_date' => [
                'bail',
                'required',
                'date',            
                // 'date_format:d-m-Y',
            ],
            'upload_document' => [
                'bail',
                'nullable',
                'string'
            ],
            'remarks' => [
                'bail',
                'nullable',
                'string',
                'regex:/^[a-zA-Z0-9\s.,!?()\-]*$/',
            ]
		];
    }
	
	public static function messages():array
	{
		return [
            'personalities_details.*.major_component_id.required' => 'The Major Component field is required.',
            'personalities_details.*.major_component_id.exists' => 'The Major Component field is invalid.',
            'personalities_details.*.component_id.exists' => 'The Component field is invalid.',
            'personalities_details.*.sub_component_id.exists' => 'The Sub Component field is invalid.',
            'personalities_details.*.amount.required' => 'The Amount field is required.',

			// 'personalities_details.*.sub_component_id.required' => 'The Sub Component field is required.',
			// 'personalities_details.*.component_id.required' => 'The Major Component field is required.',
		];
	}
}