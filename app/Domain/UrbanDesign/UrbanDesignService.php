<?php

declare(strict_types=1);

namespace App\Domain\UrbanDesign;

use App\Traits\HasFileUpload;
use DB;

class UrbanDesignService
{
    use HasFileUpload;

    public function apply(array $validated)
    {
        $common = getDepartmentAndServiceId($validated['service_slug']);

        $vendorApplicationData = [
            'id' => uuid(),
            'user_id' => AuthId(),
            'application_number' => rand(1000000000, 9999999999),
            'department_id' => $common?->department_id,
            'service_id' => $common?->id,
            'review_status' => null,
            'created_by' => AuthId(),
            'created_at' => currentDateTime()
        ];

        $serviceData = [
            'id' => uuid(),
            'vendor_application_id' => $vendorApplicationData['id'],
            'applicant_type_id' => $validated['applicant_type'],
            'application_date' => date('Y-m-d', strtotime($validated['application_date'])),
            'property_location' => $validated['property_location'],
            'subject' => $validated['subject'],
            'property_format' => $validated['property_format'],

            'location_plan_document' => $validated['location_plan_document'],
            'location_plan_document_original_name' => $this->getUploadedFileOriginalName($validated['location_plan_document']),

            'design_proposal_document' => $validated['design_proposal_document'],
            'design_proposal_document_original_name' => $this->getUploadedFileOriginalName($validated['design_proposal_document']),

            'detailed_drawing_document' => $validated['detailed_drawing_document'],
            'detailed_drawing_document_original_name' => $this->getUploadedFileOriginalName($validated['detailed_drawing_document']),

            'site_photograph_document' => $validated['site_photograph_document'],
            'site_photograph_document_original_name' => $this->getUploadedFileOriginalName($validated['site_photograph_document']),

            'other_supporting_document' => $validated['other_supporting_document'],
            'other_supporting_document_original_name' => $this->getUploadedFileOriginalName($validated['other_supporting_document']),

            'created_at' => currentDateTime(),
            'created_by' => AuthId(),
        ];

        DB::transaction(function () use ($vendorApplicationData, $serviceData) {
            DB::table('vendor_applications')->insert($vendorApplicationData);
            DB::table('service_urban_design')->insert($serviceData);
        });
    }


    public function getDocumentUploadConfigurations(string $document)
    {
        $uploadPath = null;
        $uplaodRules = [];
        $uploadMessages = [];
        $uploadHandler = null;

        if ($document === 'upload-location-plan-documents') {
            $uploadPath = config('upload.location_plan_document_path');
            $uplaodRules = UrbanDesignRequest::getZipOrPdfRules();
            $uploadMessages = UrbanDesignRequest::getZipOrPdfRuleMessages();
            $uploadHandler = 'uploadFileWithValidation';
        } elseif ($document === 'upload-design-proposal-documents') {
            $uploadPath = config('upload.design_proposal_document_path');
            $uplaodRules = UrbanDesignRequest::getZipOrPdfRules();
            $uploadMessages = UrbanDesignRequest::getZipOrPdfRuleMessages();
            $uploadHandler = 'uploadFileWithValidation';
        } elseif ($document === 'upload-detailed-drawing-documents') {
            $uploadPath = config('upload.detailed_drawing_document_path');
            $uplaodRules = UrbanDesignRequest::getZipOrPdfRules();
            $uploadMessages = UrbanDesignRequest::getZipOrPdfRuleMessages();
            $uploadHandler = 'uploadFileWithValidation';
        } elseif ($document === 'upload-site-photograph-documents') {
            $uploadPath = config('upload.site_photograph_document_path');
            $uplaodRules = UrbanDesignRequest::getZipOrPdfRules();
            $uploadMessages = UrbanDesignRequest::getZipOrPdfRuleMessages();
            $uploadHandler = 'uploadFileWithValidation';
        } elseif ($document === 'upload-other-supporting-documents') {
            $uploadPath = config('upload.other_supporting_document_path');
            $uplaodRules = UrbanDesignRequest::getZipOrPdfRules();
            $uploadMessages = UrbanDesignRequest::getZipOrPdfRuleMessages();
            $uploadHandler = 'uploadFileWithValidation';
        }

        return [
            $uploadPath,
            $uplaodRules,
            $uploadMessages,
            $uploadHandler
        ];
    }

    public function getDetail(string $vendor_application_id)
    {
        $serviceDetail = \DB::table('vendor_applications AS va')
            ->selectRaw('
                va.*,
                smp.*             
            ')
            ->join('service_urban_design AS smp', 'smp.vendor_application_id', '=', 'va.id')
            ->where('va.id', $vendor_application_id)
            ->first();

        return $serviceDetail;
    }
}
