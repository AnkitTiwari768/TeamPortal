<?php 

declare(strict_types=1);

namespace App\Web\FundFlow;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\AlphaSpace;
use App\Web\FundFlow\FundFlowController;

class FundDistributionRequest
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
            'amount_allocated' => [
                'bail',
                'required',
                'string',  
                'regex:/^\d+(\.\d{1,5})?$/',
                'gt:0',
                function ($attribute, $value, $fail) {
                    self::validateDistributionAmount(request(), $value, $fail);
                }                         
            ],
            'major_component_id' => [
                'bail',
                'required',
                'string',
                'exists:major-components,id'
               
            ],
            'component_id' => [
                'bail',
                'nullable',
                'string',
                'exists:components,id'
            ],
			'sub_component_id' => [
                'bail',
                'nullable',
                'string',
                'exists:sub-components,id',
                
            ],
			
            'sanction_order_no' => [
                'bail',
                'required',
                'string'
            ],
            'sanction_order_date' => [
                'bail',
                'required',
                'string',
                //'regex:/^[0-9\s]*$/',
            ],
            'tds' => [
                'bail',
                'required',
                'string',
                'gt:0',
                'regex:/^\d+(\.\d{1,2})?$/'
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
           // 'personalities_details.*.amount.required' => 'The Amount field is required.',
           'amount_allocated.gt' => 'Amount must be greater than 0.',
            'tds.gt' => 'TDS % must be greater than 0.',		
		];
	}

    public static function validateDistributionAmount($attribute, $value, $fail) {
    
        $data = app(FundFlowController::class)->getTotalAllocation(request())->getData(true);
        //$data = $controller->getTotalAllocation(request())->getData(true);

        if ((float)$value > (float)$data['remaining']) {
            $fail('Entered amount exceeds the available balance of ₹' . $data['remaining'] . '.');
        }

        if ($data['remaining'] < 0) {
            $fail('Remaining balance is invalid. Please contact support.');
        }
    }
}