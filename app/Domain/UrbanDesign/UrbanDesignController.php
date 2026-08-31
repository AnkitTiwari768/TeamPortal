<?php

declare(strict_types=1);

namespace App\Domain\UrbanDesign;

use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Traits\HasFileUpload;
use App\Traits\HasUserAddress;

final class UrbanDesignController extends ClientController
{
    use HasFileUpload, HasUserAddress;

    public function __construct(
        private UrbanDesignService $service
    ) {}

    public function index()
    {
        return view('urban_design.index');
    }

    public function apply(?string $serviceSlug = null)
    {
        return view('urban_design.form', [
            'module_url' => 'urban_design.apply',
            'title' => 'Urban Design Division',
            'userAddress' => $this->fullUserAddress(auth()->user()->id),
            'serviceSlug' => $serviceSlug
        ]);
    }

    public function uploadDocuments(Request $request, string $document)
    {
        [$uploadPath, $uploadRules, $uploadMessages, $uploadHandler] = $this->service->getDocumentUploadConfigurations($document);
        return $this->{$uploadHandler}($request, 'file', $uploadPath, $uploadRules, $uploadMessages);
    }


    public function store(Request $request)
    {
        $validator = Validator::make(
            data: $request->only([
                'applicant_name',
                'applicant_type',
                'subject',
                'application_date',
                'property_format',
                'property_location',
                'location_plan_document',
                'design_proposal_document',
                'detailed_drawing_document',
                'site_photograph_document',
                'other_supporting_document',
                'service_slug'
            ]),
            rules: UrbanDesignRequest::getRules()
        );

        if ($validator->fails()) {
            return $this->error($validator->errors());
        }

        return $this->created($this->service->apply($validator->validated()));
    }

    public function viewDetail($vendor_application_id)
    {
        $module_url = 'vendor-applications.index';
        $row = (array) $this->service->getDetail($vendor_application_id);
        //dd($row);
        $userAddress = $this->fullUserAddress(auth()->user()->id);
        return view('urban_design.view', compact('module_url', 'row', 'userAddress'))->with('title', 'Urban Design Service');
    }
}
