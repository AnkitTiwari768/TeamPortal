<?php

declare(strict_types=1);

namespace App\Web\Claim;

use App\Utils\UuidGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Facades\Excel;

class CatalogueCreationClaimDetails
{
    public function __construct(
        private ClaimService $claimService,
        private string $claimTypeId,
		public array $pdfUploads,
    ) {}

    public function attributes(): array
    {
        return [
            '0' => 'Msme Team Registration Id',
            '1' => 'Date of Onboarding of MSME on ONDC',
            '2' => 'Number of SKUs',
            '3' => 'ONDC Order Id',
            '4' => 'ONDC Invoice Number',
            '5' => 'ONDC Invoice Date'
        ];
    }

    public function rules($rows = null): array
    {
        return [
            '0' => [
                'bail',
                'required',
                'max:100',
                'exists:team_msme_schemes,team_id',
                'unique:claims,team_registration_id'
            ],
            '1' => [
                'bail',
                'required',
                function (string $attribute, mixed $value, \Closure $fail) {
                    try {
                        // Try converting Excel serial number to Carbon
                        $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                        if (!$date instanceof \DateTime) {
                            $fail("The $attribute is not a valid Excel date.");
                        }
                    } catch (\Exception $e) {
                        // If conversion fails, also try native PHP date parse
                        if (!strtotime($value)) {
                            $fail("The $attribute must be a valid date or Excel serial.");
                        }
                    }
                }
            ],
            '2' => [
                'bail',
                'required',
                'integer',
                'min:1'
            ],
            '3' => [
                'bail',
                'required',
                'distinct',
                'unique:claim_orders,ondc_order_id'
            ],
            '4' => [
                'bail',
                'required',
                'distinct',
                'unique:claim_orders,invoice_number'
            ],
            '5' => [
                'bail',
                'nullable',
                function (string $attribute, mixed $value, \Closure $fail) use ($rows) {
                    $dates = explode(',', $value);
                    foreach ($dates as $date) {
                        if (!strtotime($date)) {
                            $fail("Invalid date format at row {$rows} column 10.");
                        }
                    }
                }
            ],
        ];
    }


    public function data($row): array
    {
		
        $msmeDetails = $this->claimService->getMsmeDetails((string) $row[0]);
		$pdfUploads=$this->pdfUploads;
		//dd($pdfUploads);
        $claimDetails = [
            'id' => UuidGenerator::uuid7(),
            'claim_type_id' => $this->claimTypeId,
            'snp_id' => auth()->user()->username,
            'application_number' => $this->claimService->generateApplicationNumber(),
            'team_registration_id' => (string) $row[0],
            'catalogue_type' => 'Manual',
            'onboarding_date' =>  $this->transformDate($row[1]),
            'seller_provider_id' => $msmeDetails?->seller_provider_id,
            'number_of_skus' => $row[2],
            'msme_transaction_type' => $msmeDetails->ondc_transaction_type_id ?? null,
            'created_at' => now(),
            'is_bulk' => true,
            'status' => ClaimReviewStatus::DRAFT->value,
            'msme_name' => $msmeDetails->enterprise_name ?? null,
            'msme_udyam_number' => $msmeDetails->udyam_no ?? null,
            'msme_classification' => $msmeDetails->msme_classification ?? null,
            'msme_category' => $msmeDetails->major_activity ?? null,
            'created_by' => auth()->user()->id,
        ];

        $ondcOrderIdArray = explode(',', $row[3]);
        $ondcInvoiceNumberArray = explode(',', $row[4]);
        $ondcInvoiceDateArray = explode(',', $row[5]);
		

        // Build rules
        $rules = [];
        $messages = [];
		
		
        foreach ($ondcOrderIdArray as $index => $value) {
            $rowNum = $index + 2; // +2 because Excel rows usually have a header
            $rules["ondc_order_id.$index"] = 'unique:claim_orders,ondc_order_id';
            $messages["ondc_order_id.$index.unique"] = "Row $rowNum - The ONDC Order Id \"$value\" already exists in the database.";
        }

        foreach ($ondcInvoiceNumberArray as $index => $value) {
            $rowNum = $index + 2;
            $rules["invoice_number.$index"] = 'unique:claim_orders,invoice_number';
            $messages["invoice_number.$index.unique"] = "Row $rowNum - The ONDC Invoice Number \"$value\" already exists in the database.";
        }

        // Create validator
        $validator = Validator::make(
            [
                'ondc_order_id'  => $ondcOrderIdArray,
                'invoice_number' => $ondcInvoiceNumberArray,
            ],
            $rules,
            $messages
        );


        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        if (count($ondcOrderIdArray) !== count(array_unique($ondcOrderIdArray))) {
            throw new \Exception("Duplicate ONDC Order IDs found.");
        }

        if (count($ondcInvoiceNumberArray) !== count(array_unique($ondcInvoiceNumberArray))) {
            throw new \Exception("Duplicate Invoice Numbers found.");
        }

        $claimOrders = [];
        foreach ($ondcOrderIdArray as $index => $order) {
            $claimOrders[] = [
                'id' => UuidGenerator::uuid7(),
                'claim_id' => $claimDetails['id'],
                'ondc_order_id' => $ondcOrderIdArray[$index] ?? null,
                'invoice_number' => $ondcInvoiceNumberArray[$index] ?? null,
                'invoice_date' => isset($ondcInvoiceDateArray[$index]) ? date('Y-m-d', strtotime($ondcInvoiceDateArray[$index])) : null,
            ];
        }
		
		/*$claimDocuments = [];
		if(!empty($pdfUploads)){
				foreach($pdfUploads as $pdfName=>$pdfUpload){
					$originalFileName=pathinfo($pdfName, PATHINFO_FILENAME);
					$originalFileNameExploade = explode("_", $originalFileName);
					$teamRegistrationId=$originalFileNameExploade[0];
					$slug=strtolower(str_replace(' ', '_', $originalFileNameExploade[1]));
					
					
					$claimDocuments[] = [
					'id' => UuidGenerator::uuid7(),
					'claim_id' =>$claimDetails['id'],
					'document_category_id' => $this->getDocumentCategoryId($slug),
					'file_upload_id' => $pdfUpload,
				];
			}
		}*/
		
		
		
		$teamWiseFiles = [];

		// Step 1: Group files by team_registration_id
		foreach ($pdfUploads as $pdfName => $pdfUploadId) {
			$originalFileName = pathinfo($pdfName, PATHINFO_FILENAME);
			$originalFileNameExploade = explode("_", $originalFileName);

			$teamRegistrationId = $originalFileNameExploade[0];
			$slug = strtolower(str_replace(' ', '_', $originalFileNameExploade[1]));

			$teamWiseFiles[$teamRegistrationId][] = [
				'slug'       => $slug,
				'pdfUploadId'  => $pdfUploadId,
			];
		}
		
		if (!isset($teamWiseFiles[$claimDetails['team_registration_id']])) {
			throw new \Exception("Validation failed: No PDF files found for Team Registration ID: " . $claimDetails['team_registration_id']);
		}
		
		//dd($teamWiseFiles);

		$claimDocuments = [];
		

		$teamWiseFiles = $teamWiseFiles[$claimDetails['team_registration_id']];
		foreach ($teamWiseFiles as $file) {
			$claimDocuments[] = [
				'id'                   => UuidGenerator::uuid7(),
				'claim_id'             => $claimDetails['id'],
				'document_category_id' => $this->getDocumentCategoryId($file['slug']),
				'file_upload_id'       => $file['pdfUploadId'],
			];
		}
			

        return [
            $claimDetails,
            $claimOrders,
			$claimDocuments,
        ];
    }

    private function transformDate($value)
    {
        try {
            return Carbon::instance(Date::excelToDateTimeObject($value));
        } catch (\Exception $e) {
            return null;
        }
    }
	
	
	public function getDocumentCategoryId($slug){
		return \DB::table('document_categories')->where('slug',$slug)->where('status',1)->value('id');
	}
}
