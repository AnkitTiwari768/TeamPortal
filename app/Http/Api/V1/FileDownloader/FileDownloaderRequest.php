<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\LegalOfficer;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Rules\AlphaSpace;
use App\Core\BaseRequest;

class LegalOfficerRequest extends BaseRequest
{
    public static function getRules(?string $id = null): array 
    {
        return [
			'national_permission_application_id' => [
                'required',
                'string'                
            ],
            'application_type' => [
                'required'
            ],
			'title_name' => [
                'required'
            ],
			'legal_officer_name' => [
                'required'
            ],
			'legal_officer_email' => [
                'required',
				'unique:users,email'
            ],
			'legal_officer_contact_number' => [
                'required'
            ]
			/*,
			'designation_id' => [
                'required'
            ]*/
		];
		
    }
}