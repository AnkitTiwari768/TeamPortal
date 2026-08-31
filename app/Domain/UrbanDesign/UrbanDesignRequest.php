<?php

declare(strict_types=1);

namespace App\Domain\UrbanDesign;

use App\Core\BaseRequest;

class UrbanDesignRequest extends BaseRequest
{
    public static function getRules(?string $id = null): array
    {
        return [
            'service_slug' => 'required',
            'applicant_type' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],
            'subject' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],
            // 'address' => [
            //     'bail',
            //     'required',
            //     'string',
            //     'max:500',
            // ],

            'application_date' => [
                'bail',
                'required',
                'date',
            ],
            'property_format' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],
            'property_location' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],
            'location_plan_document' => [
                'bail',
                'required',
                'exists:file_uploads,file_system_name'
            ],
            'detailed_drawing_document' => [
                'bail',
                'required',
                'exists:file_uploads,file_system_name'
            ],
            'design_proposal_document' => [
                'bail',
                'required',
                'exists:file_uploads,file_system_name'
            ],
            'site_photograph_document' => [
                'bail',
                'required',
                'exists:file_uploads,file_system_name'
            ],
            'other_supporting_document' => [
                'bail',
                'required',
                'exists:file_uploads,file_system_name'
            ],
        ];
    }
}
