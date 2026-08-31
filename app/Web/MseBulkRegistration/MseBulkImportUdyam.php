<?php

namespace App\Web\MseBulkRegistration;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use DateTime;
use App\Contracts\GrantType;
use Maatwebsite\Excel\Concerns\{
    ToCollection,
    WithHeadingRow,
    // WithValidation,
    SkipsEmptyRows,
    SkipsOnFailure
};
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\SkipsFailures;


class MseBulkImportUdyam implements ToCollection, WithHeadingRow,
    //WithValidation,
    SkipsEmptyRows, SkipsOnFailure
{
    use SkipsFailures;

    private array $mobiles = [];
    private array $udyams = [];
    private bool $apiTimeout = false;

    public $importErrors = [];
    public $validDataRows = [];
    public int $totalRows = 0;

    public function collection(Collection $rows)
    {
	   $this->totalRows = $rows->count();
		if ($this->totalRows > 500) {
			$this->importErrors = [
				"status" => 'api_not_working',
				"message" => "Bulk upload allows a maximum of 500 MSE entries per upload. Please ensure the file does not exceed this limit",
				"success" => 0,
				"failed" => 0,
				"errors" => []
			];
			return;
		}
    
            $errors = [];
            $successCount = 0;
            $this->validDataRows = [];
            $excelDuplicateTracker = [];

            foreach ($rows as $index => $payload) {
                $rowNumber = $index + 2;
                $data = $payload->toArray();

                $mobile = trim($data["mobile"] ?? "");
                $udyam_no = trim($data["udyam_no"] ?? "");

                if ($mobile === "" && $udyam_no === "") {
                    continue;
                }
                // ✅ Excel Duplicate Check
                $uniqueKey = $mobile . "_" . $udyam_no;

                if (isset($excelDuplicateTracker[$uniqueKey]))
				{
                    $errorMsg = "Duplicate record in Excel file";
                    $errors[] = [
                        "row" => $rowNumber,
                        "message" => $errorMsg,
                    ];
                    $this->saveTempImportError($data, $errorMsg);
                    continue;
                }

                $excelDuplicateTracker[$uniqueKey] = true;
                // ✅ Get IDs for validation
                $productCategoryId = $this->getSubdomainTypes("sub_domains",$data["product_category_id"] ?? null);
                $currentBusinessId = $this->getCurrentBuisinessTypes("attribute_values",slugify($data["current_state_business_id"] ?? null));
                $transactionTypeId = $this->getTransactionTypes("attribute_values",slugify($data["ondc_transaction_type_id"] ?? null));
                // ✅ Validate Row
                $validator = $this->validateRow($data,$productCategoryId,$currentBusinessId,$transactionTypeId);

                if ($validator->fails()) {
                    $errorMsg = implode(", ", $validator->errors()->all());
                    $errors[] = [
                        "row" => $rowNumber,
                        "message" => $errorMsg,
                    ];
                    $this->saveTempImportError($data, $errorMsg);
                    continue;
                }
                // ✅ Store valid row with row number
                $this->validDataRows[] = ["row_number" => $rowNumber,"data" => $data,];
            }
            foreach ($this->validDataRows as $validRow) {
                if ($this->apiTimeout) {
                    break;
                }
                $rowNumber = $validRow["row_number"];
                $row = $validRow["data"];
                $mobile = trim($row["mobile"]);
                $udyam_no = trim($row["udyam_no"]);

                try {
                    $apiDetails = $this->getUdyamDetailsOrDummy($udyam_no,$mobile);
                    if (
                        isset($apiDetails["error"]) &&
                        $apiDetails["error"] === true
                    ) {
                        $errorMsg = $apiDetails["message"];
                        if (
                            $errorMsg ==="Udyam API timeout — server not responding" ||
                            $errorMsg ==="Udyam API is currently not responding. Please try again later" ||
                            $errorMsg === "Udyam API stopped responding"
                        ) {
                            $this->apiTimeout = true;
                            DB::table("team_msme_scheme_temps")->where("created_by", AuthId())->delete();

                            $this->importErrors = [
                                "status" => "api_not_working",
                                "message" => $errorMsg,
                                "success" => 0,
                                "failed" => 0,
                                "errors" => [],
                            ];
                            return;
                        }
                        $errors[] = [
                            "row" => $rowNumber,
                            "message" => $errorMsg,
                        ];

                        $this->saveTempImportError($row, $errorMsg);
                        continue;
                    }

                    $udetails = $apiDetails["data"];
                    if (
                        isset($udetails["BasicDetail"]["Error"]) && $udetails["BasicDetail"]["Error"] === "Wrong Detail") {
                        $errorMsg = $udetails["BasicDetail"]["message"] ?? "Invalid Udyam details";

                        $errors[] = [
                            "row" => $rowNumber,
                            "message" => $errorMsg,
                        ];
                        $this->saveTempImportError($row, $errorMsg);
                        continue;
                    }

                    $majorActivity = strtolower(trim($udetails["BasicDetail"]["MajorActivity"] ?? ""));
                    $enterpriseType = strtolower(trim($udetails["BasicDetail"]["EnterpriseType"] ?? ""));

                    if ($majorActivity === "trading") {
                        $errorMsg ="Registration on the TEAMS Portal is currently restricted for MSMEs engaged in Trading activities.";
                        $errors[] = [
                            "row" => $rowNumber,
                            "message" => $errorMsg,
                        ];
                        $this->saveTempImportError($row, $errorMsg);
                        continue;
                    }

                    if ($enterpriseType === "medium") {
                        $errorMsg ="Medium-scale MSMEs are not eligible for registration on the TEAMS Portal.";
                        $errors[] = [
                            "row" => $rowNumber,
                            "message" => $errorMsg,
                        ];
                        $this->saveTempImportError($row, $errorMsg);
                        continue;
                    }

                    // ✅ Database Duplicate Check

                    if ($this->isDuplicate($mobile, $udyam_no)) {
                        $errorMsg ="Duplicate record (Mobile or Udyam already exists)";
                        $errors[] = [
                            "row" => $rowNumber,
                            "message" => $errorMsg,
                        ];
                        $this->saveTempImportError($row, $errorMsg);
                        continue;
                    }

                    // ✅ Transaction Insert
                    DB::transaction(function () use ($row,$udetails,$mobile,&$successCount) {
                        $uuid = uuid();
                        $userId = uuid();

                        DB::table("team_msme_schemes")->insert($this->prepareMsmeData($row,$udetails,$uuid,$userId));
                        DB::table("users")->insert($this->prepareUserData($row,$udetails,$userId,$mobile));
                        $roleId = DB::table("roles")->where("slug", "msme")->value("id");
                        DB::table("user_roles")->insert(["user_id" => $userId,"role_id" => $roleId,"type" => GrantType::THROUGH_ROLE,]);
                        if (hasRole("snp")) {
                            DB::table("team_snpmsme_mapping")->insert(["id" => uuid(),"snp_id" => $this->getSnpId(AuthId()),"msme_id" => $uuid,"created_at" => currentDateTime(),]);
                        }
                        $successCount++;
                    });
                } catch (\Exception $e) {
                    $errorMsg = $e->getMessage();

                    $errors[] = [
                        "row" => $rowNumber,
                        "message" => $errorMsg,
                    ];

                    $this->saveTempImportError($row, $errorMsg);
                    continue;
                }
            }

            // ✅ Sort errors by row number
            usort($errors, function ($a, $b) {
                return $a["row"] <=> $b["row"];
            });

            // ✅ Format errors for display
            $formattedErrors = [];

            foreach ($errors as $error) {
                $formattedErrors[] = "Row {$error["row"]}: {$error["message"]}";
            }

            // ✅ Failed count
            $failedCount = count($errors);

            // Optional: cross-check from temp table
            $tempFailed = DB::table("team_msme_scheme_temps")
                ->where("created_by", AuthId())
                ->count();

            $this->importErrors = [
                "status" => true,
                "message" => "Import Completed",
                "success" => $successCount,
                "failed" => $failedCount ?: $tempFailed,
                "errors" => [],
            ];
        
    }

    /* -------------------- Helper Functions -------------------- */

	private function validateRow($data, $productCategoryId, $currentBusinessId, $transactionTypeId)
	{
		return Validator::make($data, [
			'mobile' => ['required','digits:10','regex:/^[6-9]\d{9}$/'],
			'udyam_no' => ['required','regex:/^UDYAM-[A-Z]{2}-\d{2}-\d{7}$/'],
			'pan_no' => ['required','regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'],
			'gstin_no' => ['required','regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{1}Z[A-Z0-9]{1}$/'],
			'product_category_id' => ['required', fn($attr, $value, $fail) => empty(json_decode($productCategoryId, true)) ? $fail("Invalid product category: {$value}") : null],
			'current_state_business_id' => ['required', fn($attr, $value, $fail) => !$currentBusinessId ? $fail("Invalid business type: {$value}") : null],
			'ondc_transaction_type_id' => ['required', fn($attr, $value, $fail) => !$transactionTypeId ? $fail("Invalid transaction type: {$value}") : null],
			'attending_ondc_awareness_workshop' => 'required',
			'turnover' => ['nullable','numeric','regex:/^\d{1,15}(\.\d{1,2})?$/'],
			
		], [
			'mobile.required' => 'Mobile required',
			'mobile.digits' => 'Mobile must be 10 digits',
			'mobile.regex' => 'Invalid mobile format',
			'udyam_no.required' => 'Udyam required',
			'udyam_no.regex' => 'Invalid Udyam format',
			'pan_no.required' => 'PAN required',
			'pan_no.regex' => 'Invalid PAN format',
			'gstin_no.required' => 'GST required',
			'gstin_no.regex' => 'Invalid GST format',
			'product_category_id.required' => 'Product category required',
			'current_state_business_id.required' => 'Business type required',
			'ondc_transaction_type_id.required' => 'Transaction type required',
			'attending_ondc_awareness_workshop.required' => 'Workshop required',
			'turnover.regex' => 'Turnover must be a valid amount (max 15 digits and 2 decimals).'
		]);
	}

    private function getUdyamDetailsOrDummy($udyam_no, $mobile)
    {
        if (!config("settings.udyam_api_by_pass")) {
            return $this->getUdyamDetails($udyam_no, $mobile);
        }
        $xml = simplexml_load_string($this->getDummyUdyamXML());
        return json_decode(json_encode($xml), true);
    }

    private function isDuplicate($mobile, $udyamNo)
    {
        return DB::table("team_msme_schemes")
            ->where("mobile", $mobile)
            ->orWhere("udyam_no", $udyamNo)
            ->exists();
    }

   private function prepareMsmeData($payload, $udetails, $uuid, $userId)
	{
		$incDate = \DateTime::createFromFormat('d/m/Y', $udetails['BasicDetail']['IncorporationDate'] ?? null);
		return [
			'id' => $uuid,
			'user_id' => $userId,
			'team_id' => 'TEAM' . rand(10000, 99999),
			'udyam_no' => trim($payload['udyam_no']),
			//'udyam_no' => $udetails['BasicDetail']['UdyamNo'],
			'mobile' => trim($payload['mobile']),
			'email' => $udetails['BasicDetail']['EmailId'],
			'enterprise_name' => $udetails['BasicDetail']['EnterpriseName'],
			'entrepreneur_name' => $udetails['BasicDetail']['EntrepreneurName'],
			'organisation_type' => $udetails['BasicDetail']['OrganisationType'],
			'address' => $udetails['BasicDetail']['CommunicationAddress'],
			'pincode' => $udetails['BasicDetail']['PINCode'],
			'ph' => $udetails['BasicDetail']['PH'],
			'major_activity' => $udetails['BasicDetail']['MajorActivity'],
			'msme_classification' => $udetails['BasicDetail']['EnterpriseType'],
			'gender' => $udetails['BasicDetail']['Gender'],
			'social_category' => $udetails['BasicDetail']['SocialCategory'],
			'incorporation_date' => $incDate?->format('Y-m-d'),
			'total_emp' => $udetails['BasicDetail']['TotalEmp'],
			'state_id' => $this->getStateId('states', $udetails['BasicDetail']['LG_ST_Code']),
			'district_id' => $this->getStateId('locations', $udetails['BasicDetail']['LG_DT_Code']),
			'enterprise_details' => json_encode($udetails['BasicDetail']['EnterpriseDetail']),
			'activity_details' => json_encode($udetails['ActivityDetail'] ?? []),
			'gstin_no' => $payload['gstin_no'] ?? null,
			'pan_no' => $payload['pan_no'],
			'turnover' => $payload['turnover']??null,
			'current_state_business_id' => $this->getCurrentBuisinessTypes('attribute_values', slugify($payload['current_state_business_id'])),
			'ondc_transaction_type_id' => $this->getTransactionTypes('attribute_values', slugify($payload['ondc_transaction_type_id'])),
			'product_category_id' => $this->getSubdomainTypes('sub_domains', $payload['product_category_id']),
			'select_snp' => hasRole('snp') ? 1 : 0,
			'attending_ondc_awareness_workshop' => strtolower($payload['attending_ondc_awareness_workshop']) === 'yes' ? 1 : 0,
			'status' => 1,
			'created_at' => currentDateTime(),
			'updated_at' => currentDateTime(),
			'created_by' => AuthId(),
		];
	}
    private function prepareUserData($payload, $udetails, $userId, $mobile)
    {
        return [
            "id" => $userId,
            "username" => $udetails["BasicDetail"]["EmailId"],
            "first_name" => $udetails["BasicDetail"]["EnterpriseName"],
            "mobile" => $mobile,
            "email" => $udetails["BasicDetail"]["EmailId"],
            "status" => 1,
            "is_msme" => 1,
            "created_at" => currentDateTime(),
        ];
    }

    private function saveTempImportError($payload, $errorMessage)
	{
		
		$uuid = uuid();
		try {
			$mobile   = $payload['mobile'];
			$udyam_no = $payload['udyam_no'];

			/// ✅ DELETE existing temp record
			DB::table('team_msme_scheme_temps')
				->where('mobile', $mobile)
				->where('udyam_no', $udyam_no)
				->where('created_by', AuthId())
				->delete();

			/// ✅ INSERT new temp record
			DB::table('team_msme_scheme_temps')->insert([
				'id'   => $uuid,
				'udyam_no'  => $udyam_no,
				'mobile'    => $mobile,
				'current_state_business_id' =>$payload['current_state_business_id'],
				'attending_ondc_awareness_workshop' =>$payload['attending_ondc_awareness_workshop'],
				'turnover' => $payload['turnover'],
				'pan_no'   => $payload['pan_no'],
				'gstin_no' => $payload['gstin_no'],
				'product_category_id' =>$payload['product_category_id'],
				'ondc_transaction_type_id' =>$payload['ondc_transaction_type_id'],
				'error_message' => $errorMessage,
				'created_by' => AuthId(),
				'created_at' => now()
			]);

		} catch (\Exception $e) {
			\Log::error('Temp Import Save Failed: ' . $e->getMessage());
		}
	}

    public function getDummyUdyamXML()
    {
        $udetails = '<UdyamDetail>
			<BasicDetail>
			<UdyamNo>UDYAM-HR-01-0008625</UdyamNo>
			<EnterpriseName>M/S AGN ENTERPRISES</EnterpriseName>
			<EntrepreneurName>M/S AGN ENTERPRISES</EntrepreneurName>
			<OrganisationType>Partnership</OrganisationType>
			<EmailId>agnenterprises4@gmail.com</EmailId>
			<SocialCategory>OBC</SocialCategory>
			<Gender>Male</Gender>
			<PH>No</PH>
			<CommunicationAddress>FIRST FLOOR, 2556/2, AMBALA, FIRST FLOOR, AMBALA, AMBALA</CommunicationAddress>
			<LG_ST_Code>6</LG_ST_Code>
			<State>HARYANA</State>
			<LG_DT_Code>58</LG_DT_Code>
			<District>AMBALA</District>
			<PINCode>133001</PINCode>
			<IncorporationDate>21/08/2021</IncorporationDate>
			<MajorActivity>Manufacturing</MajorActivity>
			<EnterpriseType>Micro</EnterpriseType>
			<EnterpriseDetail>
			<EType CL_Year="2025-26" CL_Date="01/04/2025" EnterpriseType="A" EnterpriseTypee="Micro"/>
			<EType CL_Year="2024-25" CL_Date="19/09/2024" EnterpriseType="A" EnterpriseTypee="Micro"/>
			<EType CL_Year="2023-24" CL_Date="18/09/2024" EnterpriseType="A" EnterpriseTypee="Micro"/>
			<EType CL_Year="2022-23" CL_Date="26/06/2022" EnterpriseType="A" EnterpriseTypee="Micro"/>
			<EType CL_Year="2021-22" CL_Date="22/10/2021" EnterpriseType="A" EnterpriseTypee="Micro"/>
			</EnterpriseDetail>
			<TotalEmp>4</TotalEmp>
			<AppliedDate>22/10/2021</AppliedDate>
			</BasicDetail>
			<ActivityDetail>
			<Activities>
			<Two_DigitActivity>26 - Manufacture of computer, electronic and optical products</Two_DigitActivity>
			<Four_DigitActivity>2670 - Manufacture of optical instruments and equipment</Four_DigitActivity>
			<Five_DigitActivity>26700 - Manufacture of optical instruments and equipment</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>25 - Manufacture of fabricated metal products, except machinery and equipment</Two_DigitActivity>
			<Four_DigitActivity>2593 - Manufacture of cutlery, hand tools and general hardware</Four_DigitActivity>
			<Five_DigitActivity>25932 - Manufacture of hand tools (non-power-driven) for agricultural/horticulture/forestry</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>22 - Manufacture of rubber and plastics products</Two_DigitActivity>
			<Four_DigitActivity>2220 - Manufacture of plastics products</Four_DigitActivity>
			<Five_DigitActivity>22209 - Manufacture of other plastics products n.e.c</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>23 - Manufacture of other non-metallic mineral products</Two_DigitActivity>
			<Four_DigitActivity>2310 - Manufacture of glass and glass products</Four_DigitActivity>
			<Five_DigitActivity>23104 - Manufacture of laboratory or pharmaceutical glassware</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>27 - Manufacture of electrical equipment</Two_DigitActivity>
			<Four_DigitActivity>2720 - Manufacture of batteries and accumulators</Four_DigitActivity>
			<Five_DigitActivity>27201 - Manufacture of primary cells and primary batteries nd rechargable batteries, cells containing manganese oxide, mercuric oxide silver oxide or other material</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>27 - Manufacture of electrical equipment</Two_DigitActivity>
			<Four_DigitActivity>2790 - Manufacture of other electrical equipment</Four_DigitActivity>
			<Five_DigitActivity>27900 - Manufacture of other electrical equipment</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>28 - Manufacture of machinery and equipment n.e.c.</Two_DigitActivity>
			<Four_DigitActivity>2815 - Manufacture of ovens, furnaces and furnace burners</Four_DigitActivity>
			<Five_DigitActivity>28150 - Manufacture of ovens, furnaces and furnace burners</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>31 - Manufacture of furniture</Two_DigitActivity>
			<Four_DigitActivity>3100 - Manufacture of furniture</Four_DigitActivity>
			<Five_DigitActivity>31009 - Manufacture of other furniture n.e.c.</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>32 - Other manufacturing</Two_DigitActivity>
			<Four_DigitActivity>3250 - Manufacture of medical and dental instruments and supplies</Four_DigitActivity>
			<Five_DigitActivity>32502 - Manufacture of laboratory apparatus (laboratory ultrasonic cleaning machinery, laboratory sterilizers, laboratory type distilling apparatus, laboratory centrifuges etc.)</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>32 - Other manufacturing</Two_DigitActivity>
			<Four_DigitActivity>3250 - Manufacture of medical and dental instruments and supplies</Four_DigitActivity>
			<Five_DigitActivity>32505 - Manufacture of measuring instruments suc as thermometers etc.</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>32 - Other manufacturing</Two_DigitActivity>
			<Four_DigitActivity>3250 - Manufacture of medical and dental instruments and supplies</Four_DigitActivity>
			<Five_DigitActivity>32509 - Manufacture of other medical and dental instruments n.e.c.</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>32 - Other manufacturing</Two_DigitActivity>
			<Four_DigitActivity>3290 - Other manufacturing n.e.c.</Four_DigitActivity>
			<Five_DigitActivity>32909 - Manufacture of other articles n.e.c.</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>85 - Education</Two_DigitActivity>
			<Four_DigitActivity>8510 - Primary education</Four_DigitActivity>
			<Five_DigitActivity>85109 - Other primary education activities n.e.c.</Five_DigitActivity>
			<Activity>Services</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>85 - Education</Two_DigitActivity>
			<Four_DigitActivity>8521 - General secondary education</Four_DigitActivity>
			<Five_DigitActivity>85211 - General school education in the first stage of the secondary level (up to X th standard) without any special subject pre-requisite</Five_DigitActivity>
			<Activity>Services</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>85 - Education</Two_DigitActivity>
			<Four_DigitActivity>8521 - General secondary education</Four_DigitActivity>
			<Five_DigitActivity>85212 - General school education in the second stage of the secondary level (Senior/ Higher secondary) giving, in principle, access to higher education</Five_DigitActivity>
			<Activity>Services</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>85 - Education</Two_DigitActivity>
			<Four_DigitActivity>8522 - Technical and vocational secondary education</Four_DigitActivity>
			<Five_DigitActivity>85222 - Technical and vocational education for handicapped students below the level of higher education</Five_DigitActivity>
			<Activity>Services</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>85 - Education</Two_DigitActivity>
			<Four_DigitActivity>8530 - Higher education</Four_DigitActivity>
			<Five_DigitActivity>85301 - Higher education in science, commerce, humanity and fine arts leading to a university degree or equivalent</Five_DigitActivity>
			<Activity>Services</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>85 - Education</Two_DigitActivity>
			<Four_DigitActivity>8542 - Cultural education</Four_DigitActivity>
			<Five_DigitActivity>85420 - Cultural education</Five_DigitActivity>
			<Activity>Services</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>85 - Education</Two_DigitActivity>
			<Four_DigitActivity>8549 - Other education n.e.c.</Four_DigitActivity>
			<Five_DigitActivity>85499 - Other educational services n.e.c.</Five_DigitActivity>
			<Activity>Services</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>85 - Education</Two_DigitActivity>
			<Four_DigitActivity>8550 - Educational support services</Four_DigitActivity>
			<Five_DigitActivity>85500 - Educational support services</Five_DigitActivity>
			<Activity>Services</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>26 - Manufacture of computer, electronic and optical products</Two_DigitActivity>
			<Four_DigitActivity>2651 - Manufacture of measuring, testing, navigating and control equipment</Four_DigitActivity>
			<Five_DigitActivity>26516 - Manufacture of laboratory analytical instruments and miscellaneous laboratory apparatus for measuring and testing such as scales, balances, incubators etc.</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>28 - Manufacture of machinery and equipment n.e.c.</Two_DigitActivity>
			<Four_DigitActivity>2829 - Manufacture of other special-purpose machinery</Four_DigitActivity>
			<Five_DigitActivity>28292 - Manufacture of machinery for working soft rubber or plastics or for the manufacture of products of these materials</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			</ActivityDetail>
			</UdyamDetail>';

        return $udetails;
    }

    public function prepareForValidation($row, $index)
    {
        $row = array_map(fn($v) => is_string($v) ? trim($v) : $v, $row);

        if (empty($row["mobile"] ?? null) && empty($row["udyam_no"] ?? null)) {
            return [];
        }

        return $row;
    }

    /* ---------------------------------------------------------
     | FAILURE HANDLER
     |----------------------------------------------------------*/
    public function onFailure(Failure ...$failures)
    {
        $messages = [];

        foreach ($failures as $failure) {
            foreach ($failure->errors() as $error) {
                $messages[] = $failure->attribute() . ": " . $error;
            }
        }

        throw new \Exception(implode("<br>", $messages));
    }

    public function getCurrentBuisinessTypes($table, $state_code)
    {
        //return DB::table($table)->where('code',$state_code)->first()->id;
        $record = DB::table($table)
            ->where("code", $state_code)
            ->first();
        return $record ? $record->id : null;
    }

    public function getTransactionTypes($table, $code)
    {
        //return DB::table($table)->where('code',$code)->first()->id;
        $record = DB::table($table)
            ->where("code", $code)
            ->first();
        return $record ? $record->id : null;
    }

    public function getSubdomainTypes($table, $code)
    {
        /*$sub = explode(",", $code);
		 $ids = DB::table($table)->whereIn('code', $sub)->pluck('id'); 
		 return empty($ids) ? null : json_encode($ids);*/

        $sub = explode(",", $code);
        $slugs = collect($sub)
            ->map(fn($item) => \Str::slug(trim($item)))
            ->toArray();
        $ids = DB::table($table)
            ->whereIn("code", $slugs)
            ->pluck("id");
        return $ids->isEmpty() ? null : $ids->toJson();
    }

    public function getSnpId($authId)
    {
        return DB::table("team_snp_scheme")
            ->where("user_id", $authId)
            ->value("id");
    }

    public function getMsmeId($mobile)
    {
        return DB::table("team_msme_schemes")
            ->where("mobile", $mobile)
            ->value("id");
    }

    public function getStateId($table, $state_code)
    {
        //return DB::table($table)->where('code',$state_code)->first()->id;
        $record = DB::table($table)
            ->where("code", $state_code)
            ->first();
        return $record ? $record->id : null;
    }

    public function getUdyamDetails($udyam_no, $mobile)
    {
        $token = config("constant.UDYAM_TOKEN");
        $url = "https://udyogaadhaar.gov.in/sv/Udyam_NsicB2BService.svc/GetUdyam/$udyam_no,$mobile,$token";
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            // 1️⃣ Fast connection detection
            CURLOPT_CONNECTTIMEOUT => 1,

            // 2️⃣ Allow long execution
            CURLOPT_TIMEOUT => 300,

            // 3️⃣ Detect hanging API
            CURLOPT_LOW_SPEED_LIMIT => 5,
            CURLOPT_LOW_SPEED_TIME => 2,

            CURLOPT_NOSIGNAL => 1,

            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);

        $errorNo = curl_errno($ch);
        $errorMsg = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        /* ---------- ERROR HANDLING ---------- */

        if ($errorNo) {
            switch ($errorNo) {
                case CURLE_OPERATION_TIMEDOUT:
                    return [
                        "error" => true,
                        "message" =>
                            "Udyam API is currently not responding. Please try again later",
                    ];

                case CURLE_COULDNT_CONNECT:
                    return [
                        "error" => true,
                        "message" =>
                            "Udyam API timeout — server not responding",
                    ];

                case CURLE_RECV_ERROR:
                    return [
                        "error" => true,
                        "message" => "Udyam API stopped responding",
                    ];

                case CURLE_SSL_CONNECT_ERROR:
                    return [
                        "error" => true,
                        "message" =>
                            "External government API rejected connection. Please try again.",
                    ];

                default:
                    return [
                        "error" => true,
                        "message" => "API error: " . $errorMsg,
                    ];
            }
        }
        /* ---------- HTTP RESPONSE CHECK ---------- */

        if ($httpCode >= 200 && $httpCode < 300) {
            $xml = @simplexml_load_string($response);

            if ($xml === false) {
                return [
                    "error" => true,
                    "message" => "Invalid XML received from Udyam API",
                ];
            }

            $data = json_decode(json_encode($xml), true);

            return [
                "data" => $data,
            ];
        }
        return [
            "error" => true,
            "message" => "Udyam API responded with HTTP " . $httpCode,
        ];
    }
}
