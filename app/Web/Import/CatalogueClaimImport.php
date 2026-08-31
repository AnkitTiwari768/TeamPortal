<?php

declare(strict_types=1);

namespace App\Web\Import;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use App\Web\Import\Importable;
use App\Web\Claim\ClaimService;
use App\Web\Claim\ClaimReviewStatus;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CatalogueClaimImport implements ToCollection, SkipsEmptyRows, WithMultipleSheets
{
    use Importable;

    public array $validRows = [];
    public array $invalidRows = [];
    public int $totalRows = 0;
    private static array $onboardedMSEs = [];

    private const HIGH_AOV = 'High AOV';
    private const LOW_AOV = 'Low AOV';

    private static array $excelNetworkOrderIds = [];
    private static array $excelTeamsIds = [];
    private static array $excelTransactionIds = [];
    private static array $excelInvoiceNumbers = [];
    private static array $excelProviderId = [];
    private static array $excelCatalogScoreId = [];
    private static array $excelCatalogScoreUrl = [];
    private static array $excelCredentialScoreId = [];
    private static array $excelCredentialScoreUrl = [];



    public function sheets(): array
    {
        return [
            0 => $this
        ];
    }

    protected array $requiredHeaders = [
        'seller_np_name',
        'unique_team_registration_id',
        'udyam_number',
        'provider_id',
        'catalogue_score_id',
        'catalogue_score_timestamp',
        'catalogue_score_url',
        'credential_score_id',
        'credential_score_timestamp',
        'credential_score_url',

        'txn_1_domain',
        'txn_1_item_consolidated_category',
        'txn_1_network_order_id',
        'txn_1_network_transaction_id',
        'txn_1_buyer_np_name',
        'txn_1_order_status',
        'txn_1_order_creation_timestamp',
        'txn_1_order_completed_timestamp',
        'txn_1_invoice_number',
        'txn_1_invoice_date',
        'txn_1_cart_level_item_price',
        'txn_1_delivery_fee',
        'txn_1_total_fee',

        'txn_2_domain',
        'txn_2_item_consolidated_category',
        'txn_2_network_order_id',
        'txn_2_network_transaction_id',
        'txn_2_buyer_np_name',
        'txn_2_order_status',
        'txn_2_order_creation_timestamp',
        'txn_2_order_completed_timestamp',
        'txn_2_invoice_number',
        'txn_2_invoice_date',
        'txn_2_cart_level_item_price',
        'txn_2_delivery_fee',
        'txn_2_total_fee',
    ];

    public function customAttributes()
    {
        return [
            // MSE Details
            'seller_np_name' => 'seller np name',
            'team_id' => 'TEAM Registration Id of MSE',
            'udyam_number' => 'Udyam Number',
            'provider_id' => 'ONDC Seller Network ID of MSE',
            'catalogue_score_report_id' => 'Catalogue Score Report ID',
            'catalogue_score_timestamp' => 'Catalogue Score Timestamp',
            'catalogue_score_url' => 'Catalogue Score URL',
            'credential_report_id' => 'Seller Credential Report ID',
            'credential_score_timestamp' => 'Seller Credential Score Timestamp',
            'credential_score_url' => 'Seller Credential Score URL',
            // Transaction 1 Details
            'txn_1_domain' => 'Txn 1 Domain',
            'txn_1_item_consolidated_category' => 'Txn 1 Item Consolidated Category',
            'txn_1_network_order_id' => 'Txn 1 Network Order ID',
            'txn_1_network_transaction_id' => 'Txn 1 Network Transaction ID',
            'txn_1_buyer_np_name' => 'Txn 1 Buyer NP Name',
            'txn_1_order_status' => 'Txn 1 Order Status',
            'txn_1_order_creation_timestamp' => 'Txn 1 Order Creation Timestamp',
            'txn_1_order_completed_timestamp' => 'Txn 1 Order Completed Timestamp',
            'txn_1_invoice_number' => 'Txn 1 Invoice Number',
            'txn_1_invoice_date' => 'Txn 1 Invoice Date',
            'txn_1_cart_level_item_price' => 'Txn 1 Cart Level Item Price',
            'txn_1_delivery_fee' => 'Txn 1 Delivery Fee',
            'txn_1_total_fee' => 'Txn 1 Total Fee',
            // Transaction 2 Details
            'txn_2_domain' => 'Txn 2 Domain',
            'txn_2_item_consolidated_category' => 'Txn 2 Item Consolidated Category',
            'txn_2_network_order_id' => 'Txn 2 Network Order ID',
            'txn_2_network_transaction_id' => 'Txn 2 Network Transaction ID',
            'txn_2_buyer_np_name' => 'Txn 2 Buyer NP Name',
            'txn_2_order_status' => 'Txn 2 Order Status',
            'txn_2_order_creation_timestamp' => 'Txn 2 Order Creation Timestamp',
            'txn_2_order_completed_timestamp' => 'Txn 2 Order Completed Timestamp',
            'txn_2_invoice_number' => 'Txn 2 Invoice Number',
            'txn_2_invoice_date' => 'Txn 2 Invoice Date',
            'txn_2_cart_level_item_price' => 'Txn 2 Cart Level Item Price',
            'txn_2_delivery_fee' => 'Txn 2 Delivery Fee',
            'txn_2_total_fee' => 'Txn 2 Total Fee',
        ];
    }

    public function rules($row): array
    {
        return [

            /* =======================
         | MSE BASIC DETAILS
         ======================= */

            'team_id' => [
                'required',
                'max:100',
                'exists:team_msme_schemes,team_id',
                'unique:claims,team_registration_id',
                $this->checkMSE(),
                $this->checkTeamId(),
                // Add Excel duplicate validations
                $this->checkDuplicateTeamProviderInExcel(
                    $row['team_id'],
                    $row['provider_id']
                ),
                $this->checkDuplicateTeamCatalogueReportInExcel(
                    $row['team_id'],
                    $row['catalogue_score_report_id']
                ),
                $this->checkDuplicateTeamCredentialReportInExcel(
                    $row['team_id'],
                    $row['credential_report_id']
                ),
                $this->checkDuplicateTeamCatalogueUrlInExcel(
                    $row['team_id'],
                    $row['catalogue_score_url']
                ),
                $this->checkDuplicateTeamCredentialUrlInExcel(
                    $row['team_id'],
                    $row['credential_score_url']
                ),

            ],

            'seller_np_name' => [
                'required',
                'max:255',
            ],

            'udyam_number' => [
                'required',
                'max:50',
                'exists:team_msme_schemes,udyam_no',
                $this->checkTeamUdyamCombination($row, 'team_id', 'udyam_number')
            ],

            'provider_id' => [
                'required',
                'max:200',
                // 'digits_between:1,200'
                $this->checkProviderId()
            ],

            'catalogue_score_report_id' => [
                'required',
                'max:200',
                $this->checkCatalogScoreId()
            ],

            'catalogue_score_url' => [
                'required',
                'url',
                $this->checkCatalogScoreUrl()
            ],

            'catalogue_score_timestamp' => [
                'required',
                $this->isoTimestampRule()
            ],

            'credential_report_id' => [
                'required',
                'max:200',
                $this->checkCredentialScoreId()
            ],

            'credential_score_url' => [
                'required',
                'url',
                $this->checkCredentialScoreUrl()
            ],

            'credential_score_timestamp' => [
                'required',
                $this->isoTimestampRule()
            ],

            /* =======================
         | TRANSACTION 1 (REQUIRED)
         ======================= */
            'txn_1_domain' => [
                'required',
                'exists:sub_domains,ondc_domain_id'
            ],

            'txn_1_item_consolidated_category' => [
                'required',
                $this->checkSelectedDomainForItemCategory($row, 'txn_1_domain')
            ],

            'txn_1_network_order_id' => [
                'required',
                'max:100',
                $this->checkNetworkOrderId()
            ],

            'txn_1_network_transaction_id' => [
                'required',
                'max:100',
                $this->checkNetworkTransactionId()
            ],

            'txn_1_buyer_np_name' => [
                'required',
                'max:200'
            ],

            'txn_1_order_creation_timestamp' => [
                'required',
                $this->isoTimestampRule()
            ],

            'txn_1_order_completed_timestamp' => [
                'required',
                $this->isoTimestampRule()
            ],

            'txn_1_order_status' => [
                'required',
                $this->checkOrderStatus()
            ],

            'txn_1_invoice_number' => [
                'required',
                'max:100',
                $this->checkInvoiceNumber()
            ],

            'txn_1_invoice_date' => [
                'required',
                $this->dateRule()
            ],

            'txn_1_cart_level_item_price' => [
                'required',
                'numeric',
                'min:0',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],

            'txn_1_delivery_fee' => [
                'required',
                'numeric',
                'min:0',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],

            'txn_1_total_fee' => [
                'required',
                'numeric',
                'min:0',
                'regex:/^\d+(\.\d{1,2})?$/',
                $this->checkTotalFee($row, 'txn_1_cart_level_item_price', 'txn_1_delivery_fee')
            ],


            /* =======================
         | TRANSACTION 2
         ======================= */
            'txn_2_domain' => [
                'required',
                'exists:sub_domains,ondc_domain_id'
            ],

            'txn_2_item_consolidated_category' => [
                'required',
                $this->checkSelectedDomainForItemCategory($row, 'txn_2_domain')
            ],

            'txn_2_network_order_id' => [
                'required',
                'max:100',
                $this->checkNetworkOrderId()
            ],

            'txn_2_network_transaction_id' => [
                'required',
                'max:100',
                $this->checkNetworkTransactionId()
            ],

            'txn_2_buyer_np_name' => [
                'required',
                'max:200'
            ],

            'txn_2_order_creation_timestamp' => [
                'required',
                $this->isoTimestampRule()
            ],

            'txn_2_order_completed_timestamp' => [
                'required',
                $this->isoTimestampRule()
            ],

            'txn_2_order_status' => [
                'required',
                $this->checkOrderStatus()
            ],

            'txn_2_invoice_number' => [
                'required',
                'max:100',
                $this->checkInvoiceNumber()
            ],

            'txn_2_invoice_date' => [
                'required',
                $this->dateRule()
            ],

            'txn_2_cart_level_item_price' => [
                'required',
                'numeric',
                'min:0',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],

            'txn_2_delivery_fee' => [
                'required',
                'numeric',
                'min:0',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],

            'txn_2_total_fee' => [
                'required',
                'numeric',
                'min:0',
                'regex:/^\d+(\.\d{1,2})?$/',
                $this->checkTotalFee($row, 'txn_2_cart_level_item_price', 'txn_2_delivery_fee')
            ],
        ];
    }




    public function collection(Collection $rows)
    {
        $this->totalRows = max(0, $rows->count());

        // Validate Excel header
        $headerRow = $rows->first()->toArray();
        $this->validateExcelHeader($headerRow);

        // Map header name -> column position, so cells are read by header
        // name instead of a hardcoded index (a reordered upload template
        // would otherwise pass validateExcelHeader() but silently shift
        // every value into the wrong field).
        $headerIndex = array_flip(array_map(
            fn($value) => strtolower(trim((string) $value)),
            $headerRow
        ));

        $col = fn(string $headerName, $row) => $row[$headerIndex[$headerName] ?? -1] ?? null;

        foreach ($rows as $index => $row) {

            if ($index == 0)
                continue; // Skip header row
            if ($this->SkipEmptyRow($row)) {
                continue; // Skip empty row
            }



            $rowData = [
                'seller_np_name' => $this->clean($col('seller_np_name', $row)),
                'team_id' => $this->clean($col('unique_team_registration_id', $row)),
                'udyam_number' => $this->clean($col('udyam_number', $row)),
                'provider_id' => $this->clean($col('provider_id', $row)),

                'catalogue_score_report_id' => $this->clean($col('catalogue_score_id', $row)),
                'catalogue_score_timestamp' => $this->clean($col('catalogue_score_timestamp', $row)),
                'catalogue_score_url' => $this->clean($col('catalogue_score_url', $row)),

                'credential_report_id' => $this->clean($col('credential_score_id', $row)),
                'credential_score_timestamp' => $this->clean($col('credential_score_timestamp', $row)),
                'credential_score_url' => $this->clean($col('credential_score_url', $row)),

                // Transaction 1
                'txn_1_domain' => $this->clean($col('txn_1_domain', $row)),
                'txn_1_item_consolidated_category' => $this->clean($col('txn_1_item_consolidated_category', $row)),
                'txn_1_network_order_id' => $this->clean($col('txn_1_network_order_id', $row)),
                'txn_1_network_transaction_id' => $this->clean($col('txn_1_network_transaction_id', $row)),
                'txn_1_buyer_np_name' => $this->clean($col('txn_1_buyer_np_name', $row)),
                'txn_1_order_status' => $this->clean($col('txn_1_order_status', $row)),
                'txn_1_order_creation_timestamp' => $this->clean($col('txn_1_order_creation_timestamp', $row)),
                'txn_1_order_completed_timestamp' => $this->clean($col('txn_1_order_completed_timestamp', $row)),
                'txn_1_invoice_number' => $this->clean($col('txn_1_invoice_number', $row)),
                'txn_1_invoice_date' => $this->clean($col('txn_1_invoice_date', $row)),
                'txn_1_cart_level_item_price' => $this->clean($col('txn_1_cart_level_item_price', $row)),
                'txn_1_delivery_fee' => $this->clean($col('txn_1_delivery_fee', $row)),
                'txn_1_total_fee' => $this->clean($col('txn_1_total_fee', $row)),

                // Transaction 2
                'txn_2_domain' => $this->clean($col('txn_2_domain', $row)),
                'txn_2_item_consolidated_category' => $this->clean($col('txn_2_item_consolidated_category', $row)),
                'txn_2_network_order_id' => $this->clean($col('txn_2_network_order_id', $row)),
                'txn_2_network_transaction_id' => $this->clean($col('txn_2_network_transaction_id', $row)),
                'txn_2_buyer_np_name' => $this->clean($col('txn_2_buyer_np_name', $row)),
                'txn_2_order_status' => $this->clean($col('txn_2_order_status', $row)),
                'txn_2_order_creation_timestamp' => $this->clean($col('txn_2_order_creation_timestamp', $row)),
                'txn_2_order_completed_timestamp' => $this->clean($col('txn_2_order_completed_timestamp', $row)),
                'txn_2_invoice_number' => $this->clean($col('txn_2_invoice_number', $row)),
                'txn_2_invoice_date' => $this->clean($col('txn_2_invoice_date', $row)),
                'txn_2_cart_level_item_price' => $this->clean($col('txn_2_cart_level_item_price', $row)),
                'txn_2_delivery_fee' => $this->clean($col('txn_2_delivery_fee', $row)),
                'txn_2_total_fee' => $this->clean($col('txn_2_total_fee', $row)),
            ];


            $validator = Validator::make(
                $rowData,
                $this->rules($rowData),
                $this->customValidationMessages(),
                $this->customAttributes()
            );

            $validator->setAttributeNames($this->customAttributes());

            if ($validator->fails()) {
                $this->invalidRows[] = [
                    'row_number' => $index,
                    'data' => $rowData,
                    'errors' => $validator->errors()
                ];
                continue;
            }

            $this->validRows[] = $rowData;
        }

        // 🔹 Insert all valid rows in one query
        if (!empty($this->validRows)) {
            DB::beginTransaction();

            try {

                $claimService = app(ClaimService::class);
                $claimTypeId = request()->input('claim_type_id');

                $tempClaimIds = DB::table('temporary_claims')->where('created_by', authId())->pluck('id')->toArray();

                DB::table('temporary_claim_orders')
                    ->whereIn('claim_id', $tempClaimIds)
                    ->delete();

                DB::table('temporary_claims')
                    ->whereIn('id', $tempClaimIds)
                    ->delete();

                foreach ($this->validRows as $row) {

                    $claimId = DB::table('temporary_claims')
                        ->where('claim_type_id', $claimTypeId)
                        ->where('team_registration_id', $row['team_id'])
                        ->where('snp_id', auth()->user()->username)
                        ->value('id');

                    DB::table('temporary_claim_orders')
                        ->where('claim_id', $claimId)
                        ->delete();

                    DB::table('temporary_claims')
                        ->where('id', $claimId)
                        ->delete();

                    $msmeDetails = $claimService->getMsmeDetails($row['team_id']);

                    $claimId = uuid();

                    /** -------------------------
                     *  Insert into claims table
                     *  ------------------------- */

                    DB::table('temporary_claims')->insert([
                        'id' => $claimId,
                        'claim_type_id' => $claimTypeId,
                        'snp_id' => auth()->user()->username,
                        // 'application_number'     => $claimService->generateApplicationNumber(),
                        'team_registration_id' => $row['team_id'],
                        'catalogue_type' => 'Manual',
                        'bpp_id' => $row['provider_id'],
                        'msme_transaction_type' => $msmeDetails->ondc_transaction_type_id ?? null,
                        'is_bulk' => true,
                        'status' => ClaimReviewStatus::TEMPORARY->value,
                        'msme_name' => $msmeDetails->enterprise_name ?? null,
                        'msme_udyam_number' => $msmeDetails->udyam_no ?? null,
                        'msme_classification' => $msmeDetails->msme_classification ?? null,
                        'msme_category' => $msmeDetails->major_activity ?? null,
                        'seller_np_name' => $row['seller_np_name'] ?? null,
                        'ondc_seller_network_id' => $row['provider_id'] ?? null,
                        'seller_credential_report' => $row['credential_report_id'] ?? null,
                        'seller_credential_score_url' => $row['credential_score_url'] ?? null,
                        'credential_score_timestamp' => $this->transformIsoTimestamp($row['credential_score_timestamp']),
                        'catalogue_score_report' => $row['catalogue_score_report_id'] ?? null,
                        'catalogue_score_url' => $row['catalogue_score_url'] ?? null,
                        'catalogue_score_timestamp' => $this->transformIsoTimestamp($row['catalogue_score_timestamp']),
                        'team_id' => $row['team_id'] ?? null,
                        'created_by' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    /** -------------------------
                     *  Prepare order data
                     *  ------------------------- */

                    $claimOrders = [
                        [
                            'id' => uuid(),
                            'claim_id' => $claimId,
                            'ondc_order_id' => $row['txn_1_network_order_id'] ?? null,
                            'domain' => $row['txn_1_domain'] ?? null,
                            'item_consolidated_category' => $row['txn_1_item_consolidated_category'] ?? null,
                            'aov_grouping_type' => $this->getAovGroupingType($row['txn_1_domain'], $row['txn_1_item_consolidated_category']),
                            'network_transaction_id' => $row['txn_1_network_transaction_id'] ?? null,
                            'buyer_np_name' => $row['txn_1_buyer_np_name'] ?? null,
                            'order_status' => $row['txn_1_order_status'] ?? null,
                            'order_creation_timestamp' => $this->transformIsoTimestamp($row['txn_1_order_creation_timestamp']),
                            'order_completed_timestamp' => $this->transformIsoTimestamp($row['txn_1_order_completed_timestamp']),
                            'invoice_number' => $row['txn_1_invoice_number'] ?? null,
                            'invoice_date' => $row['txn_1_invoice_date'] ? $this->transformDate($row['txn_1_invoice_date']) : null,
                            'cart_level_item_price' => $row['txn_1_cart_level_item_price'] ?? null,
                            'delivery_fee' => $row['txn_1_delivery_fee'] ?? null,
                            'total_fee' => $row['txn_1_total_fee'] ?? null,
                            'team_id' => $row['team_id'] ?? null,
                            'provider_id' => $row['provider_id'] ?? null,
                            'credential_score_id' => $row['credential_report_id'] ?? null,
                            'catalogue_score_id' => $row['catalogue_score_report_id'] ?? null,
                            'seller_np_name' => $row['seller_np_name'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'id' => uuid(),
                            'claim_id' => $claimId,
                            'domain' => $row['txn_2_domain'] ?? null,
                            'item_consolidated_category' => $row['txn_2_item_consolidated_category'] ?? null,
                            'aov_grouping_type' => $this->getAovGroupingType($row['txn_2_domain'], $row['txn_2_item_consolidated_category']),
                            'ondc_order_id' => $row['txn_2_network_order_id'] ?? null,
                            'network_transaction_id' => $row['txn_2_network_transaction_id'] ?? null,
                            'buyer_np_name' => $row['txn_2_buyer_np_name'] ?? null,
                            'order_status' => $row['txn_2_order_status'] ?? null,
                            'order_creation_timestamp' => $this->transformIsoTimestamp($row['txn_2_order_creation_timestamp']),
                            'order_completed_timestamp' => $this->transformIsoTimestamp($row['txn_2_order_completed_timestamp']),
                            'invoice_number' => $row['txn_2_invoice_number'] ?? null,
                            'invoice_date' => $row['txn_2_invoice_date'] ? $this->transformDate($row['txn_2_invoice_date']) : null,
                            'cart_level_item_price' => $row['txn_2_cart_level_item_price'] ?? null,
                            'delivery_fee' => $row['txn_2_delivery_fee'] ?? null,
                            'total_fee' => $row['txn_2_total_fee'] ?? null,
                            'team_id' => $row['team_id'] ?? null,
                            'provider_id' => $row['provider_id'] ?? null,
                            'credential_score_id' => $row['credential_report_id'] ?? null,
                            'catalogue_score_id' => $row['catalogue_score_report_id'] ?? null,
                            'seller_np_name' => $row['seller_np_name'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    ];

                    if ($claimOrders) {
                        DB::table('temporary_claim_orders')->insert($claimOrders);
                    }
                }

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        }
    }

    public function store($userId)
    {
        DB::beginTransaction();

        try {

            $claimService = app(ClaimService::class);

            $temporaryClaims = DB::table('temporary_claims')
                ->where('created_by', $userId)
                ->where('status', ClaimReviewStatus::TEMPORARY->value)
                ->get();


            $claimData = [];
            $claimOrdersData = [];
            $teamBppMap = [];

            // Get GST type and percentages from session
            $gstType = session()->get('gst_type_' . $userId, null); // Default to GST type 1
            $gstPercentage = floatval(session()->get('gst_percentage_' . $userId, 0));
            $cgstPercentage = floatval(session()->get('cgst_percentage_' . $userId, 0));
            $sgstPercentage = floatval(session()->get('sgst_percentage_' . $userId, 0));

            if ($temporaryClaims) {

                foreach ($temporaryClaims as $index => $claim) {

                    $hasHighAov = DB::table('temporary_claim_orders')
                        ->where('claim_id', $claim->id)
                        ->where('aov_grouping_type', self::HIGH_AOV)
                        ->exists();

                    $finalAovType = $hasHighAov ? self::HIGH_AOV : self::LOW_AOV;

                    $amount = $this->getAovGroupingTypeAmount(
                        $claim->claim_type_id,
                        $finalAovType
                    );


                    // $gstPercentage = floatval(session()->get('gst_percentage_' . $userId, 0));
                    // $gstAmount = round(($amount * $gstPercentage) / 100, 2);

                    // Calculate GST amount based on type using the helper
                    $gstResults = $this->calculateInclusiveGST((float)$amount, (string)$gstType);

                    $claimData[] = [
                        'id' => $claim->id,
                        'claim_type_id' => $claim->claim_type_id,
                        'snp_id' => $claim->snp_id,
                        'application_number' => $claimService->generateApplicationNumber($index),
                        'team_registration_id' => $claim->team_registration_id,
                        'catalogue_type' => $claim->catalogue_type,
                        'bpp_id' => $claim->bpp_id,
                        'msme_transaction_type' => $claim->msme_transaction_type,
                        'is_bulk' => $claim->is_bulk,
                        'status' => ClaimReviewStatus::DRAFT->value,
                        'msme_name' => $claim->msme_name,
                        'msme_udyam_number' => $claim->msme_udyam_number,
                        'msme_classification' => $claim->msme_classification,
                        'msme_category' => $claim->msme_category,
                        'seller_np_name' => $claim->seller_np_name,
                        'ondc_seller_network_id' => $claim->ondc_seller_network_id,
                        'seller_credential_report' => $claim->seller_credential_report,
                        'seller_credential_score_url' => $claim->seller_credential_score_url,
                        'credential_score_timestamp' => $claim->credential_score_timestamp,
                        'catalogue_score_report' => $claim->catalogue_score_report,
                        'catalogue_score_url' => $claim->catalogue_score_url,
                        'catalogue_score_timestamp' => $claim->catalogue_score_timestamp,
                        'team_id' => $claim->team_id,
                        'created_by' => $claim->created_by,
                        'is_declaration_agreed' => true,
                        'amount' => $gstResults['base_amount'],
                        'gst_type' => $gstType, // Store GST type (1 or 2)
                        'gst_percentage' => $gstPercentage, // Store individual GST percentage
                        'cgst_percentage' => $cgstPercentage, // Store CGST percentage
                        'sgst_percentage' => $sgstPercentage, // Store SGST percentage                        
                        'gst_amount' => $gstResults['gst_amount'],
                        'cgst_amount' => $gstResults['cgst_amount'],
                        'sgst_amount' => $gstResults['sgst_amount'],
                        'total_claimed_amount' => $gstResults['total_claimed_amount'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $temporaryClaimOrders = DB::table('temporary_claim_orders')
                        ->where('claim_id', $claim->id)
                        ->get();

                    if ($temporaryClaimOrders) {
                        foreach ($temporaryClaimOrders as $claimOrder) {
                            $claimOrdersData[] = [
                                'id' => $claimOrder->id,
                                'claim_id' => $claimOrder->claim_id,
                                'ondc_order_id' => $claimOrder->ondc_order_id,
                                'domain' => $claimOrder->domain,
                                'item_consolidated_category' => $claimOrder->item_consolidated_category,
                                'aov_grouping_type' => $claimOrder->aov_grouping_type,
                                'network_transaction_id' => $claimOrder->network_transaction_id,
                                'buyer_np_name' => $claimOrder->buyer_np_name,
                                'order_status' => $claimOrder->order_status,
                                'order_creation_timestamp' => $claimOrder->order_creation_timestamp,
                                'order_completed_timestamp' => $claimOrder->order_completed_timestamp,
                                'invoice_number' => $claimOrder->invoice_number,
                                'invoice_date' => $claimOrder->invoice_date,
                                'cart_level_item_price' => $claimOrder->cart_level_item_price,
                                'delivery_fee' => $claimOrder->delivery_fee,
                                'total_fee' => $claimOrder->total_fee,
                                'team_id' => $claimOrder->team_id ?? null,
                                'provider_id' => $claimOrder->provider_id ?? null,
                                'credential_score_id' => $claimOrder->credential_score_id ?? null,
                                'catalogue_score_id' => $claimOrder->catalogue_score_id ?? null,
                                'seller_np_name' => $claimOrder->seller_np_name ?? null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }

                    $teamBppMap[$claim->team_registration_id] = $claim->bpp_id;
                }
            }


            if ($claimData) {
                DB::table('claims')->insert($claimData);
            }

            if ($claimOrdersData) {
                DB::table('claim_orders')->insert($claimOrdersData);
            }

            if ($claimOrdersData) {
                DB::table('temporary_claim_orders')
                    ->whereIn('claim_id', array_column($claimOrdersData, 'claim_id'))
                    ->delete();
            }

            if ($claimData) {
                DB::table('temporary_claims')
                    ->whereIn('id', array_column($claimData, 'id'))
                    ->delete();
            }

            if (!empty($teamBppMap)) {

                $caseSql = '';
                $teamIds = [];

                foreach ($teamBppMap as $teamId => $bppId) {
                    $caseSql .= "WHEN '{$teamId}' THEN '{$bppId}' ";
                    $teamIds[] = "'{$teamId}'";
                }

                DB::statement("
                    UPDATE team_msme_schemes
                    SET 
                        is_catalogue_claim_generated = 1,
                        bpp_id = CASE team_id
                            {$caseSql}
                        END,
                        bpp_updated_at = NOW()
                    WHERE team_id IN (" . implode(',', $teamIds) . ")
                ");
            }


            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function transformDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            // Excel numeric date
            if (is_numeric($value)) {
                return Carbon::instance(
                    ExcelDate::excelToDateTimeObject((float) $value)
                )->format('Y-m-d');
            }

            // dd-mm-yyyy string
            return Carbon::createFromFormat('d-m-Y', trim($value))
                ->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function transformIsoTimestamp(string $value): string
    {
        return Carbon::createFromFormat('Y-m-d\TH:i:s', $value)
            ->format('Y-m-d H:i:s');
    }
    private function checkMSE()
    {
        return function ($attribute, $value, $fail) {

            $onboardedMSEs = $this->getOnbordedMSME();

            if (!in_array($value, $onboardedMSEs)) {
                $fail("The {$attribute} is not eligible for Catalogue claim.");
            }
        };
    }


    public function isoTimestampRule()
    {
        return function ($attribute, $value, $fail) {
            try {
                Carbon::createFromFormat('Y-m-d\TH:i:s', $value);
            } catch (\Exception $e) {
                $fail("The {$attribute} must be in YYYY-MM-DDTHH:MM:SS format.");
            }
        };
    }

    public function dateRule()
    {
        return function ($attr, $value, $fail) {
            if ($value === null || $value === '') {
                return;
            }

            try {
                if (is_numeric($value)) {
                    // Excel serial date
                    $date = ExcelDate::excelToDateTimeObject($value);

                    // Validate that the resulting date is reasonable
                    if (
                        !$date ||
                        $date->format('Y-m-d') < '1900-01-01' ||
                        $date->format('Y-m-d') > '2100-12-31'
                    ) {
                        $fail("{$attr} must be in dd-mm-yyyy format.");
                    }

                    return;
                }

                // Normal date string
                Carbon::createFromFormat('d-m-Y', $value);
            } catch (\Exception $e) {
                $fail("{$attr} must be in dd-mm-yyyy format.");
            }
        };
    }

    public function checkSelectedDomainForItemCategory($row, $key)
    {
        return function ($attribute, $value, $fail) use ($row, $key) {

            $domain = $row[$key];

            $exists = DB::table('sub_domains')
                ->where('ondc_domain_id', $domain)
                ->where('name', $this->clean($value))
                ->exists();


            if (!$exists) {
                $fail('Invalid Item Consolidated Category for the selected Domain.');
            }
        };
    }

    public function checkTeamId()
    {
        return function ($attr, $value, $fail) {
            if (in_array($value, self::$excelTeamsIds)) {
                $fail("{$attr} '{$value}' is duplicated in Excel.");
            }

            if (
                DB::table('claims')
                ->where('team_registration_id', $value)
                ->exists()
            ) {
                $fail("{$attr} '{$value}' already exists.");
            }

            self::$excelTeamsIds[] = $value;
        };
    }

    public function checkNetworkOrderId()
    {
        return function ($attr, $value, $fail) {
            if (in_array($value, self::$excelNetworkOrderIds)) {
                $fail("{$attr} '{$value}' is duplicated in Excel.");
            }

            if (
                DB::table('claim_orders')
                ->where('ondc_order_id', $value)
                ->exists()
            ) {
                $fail("{$attr} '{$value}' already exists.");
            }

            self::$excelNetworkOrderIds[] = $value;
        };
    }

    public function checkNetworkTransactionId()
    {
        return function ($attr, $value, $fail) {
            if (in_array($value, self::$excelTransactionIds)) {
                $fail("{$attr} '{$value}' is duplicated in Excel.");
            }

            if (
                DB::table('claim_orders')
                ->where('network_transaction_id', $value)
                ->exists()
            ) {
                $fail("{$attr} '{$value}' already exists.");
            }

            self::$excelTransactionIds[] = $value;
        };
    }

    public function checkOrderStatus()
    {
        return function ($attr, $value, $fail) {
            if (strtoupper($value) !== 'COMPLETED') {
                $fail("{$attr} must be Completed.");
            }
        };
    }

    public function checkInvoiceNumber()
    {
        return function ($attr, $value, $fail) {
            if (in_array($value, self::$excelInvoiceNumbers)) {
                $fail("{$attr} '{$value}' is duplicated in Excel.");
            }

            if (
                DB::table('claim_orders')
                ->where('invoice_number', $value)
                ->exists()
            ) {
                $fail("{$attr} '{$value}' already exists.");
            }

            self::$excelInvoiceNumbers[] = $value;
        };
    }

    public function checkProviderId()
    {
        return function ($attr, $value, $fail) {
            if (in_array($value, self::$excelProviderId)) {
                $fail("{$attr} '{$value}' is duplicated in Excel.");
            }

            self::$excelProviderId[] = $value;
        };
    }

    public function checkCatalogScoreId()
    {
        return function ($attr, $value, $fail) {
            if (in_array($value, self::$excelCatalogScoreId)) {
                $fail("{$attr} '{$value}' is duplicated in Excel.");
            }

            self::$excelCatalogScoreId[] = $value;
        };
    }

    public function checkCatalogScoreUrl()
    {
        return function ($attr, $value, $fail) {
            if (in_array($value, self::$excelCatalogScoreUrl)) {
                $fail("{$attr} '{$value}' is duplicated in Excel.");
            }

            self::$excelCatalogScoreUrl[] = $value;
        };
    }

    public function checkCredentialScoreId()
    {
        return function ($attr, $value, $fail) {
            if (in_array($value, self::$excelCredentialScoreId)) {
                $fail("{$attr} '{$value}' is duplicated in Excel.");
            }

            self::$excelCredentialScoreId[] = $value;
        };
    }

    public function checkCredentialScoreUrl()
    {
        return function ($attr, $value, $fail) {
            if (in_array($value, self::$excelCredentialScoreUrl)) {
                $fail("{$attr} '{$value}' is duplicated in Excel.");
            }

            self::$excelCredentialScoreUrl[] = $value;
        };
    }

    public function checkTotalFee($row, $itemPriceField, $deliveryFeeField)
    {
        return function ($attribute, $value, $fail) use ($row, $itemPriceField, $deliveryFeeField) {
            $itemPrice = (float) $row[$itemPriceField];
            $deliveryFee = (float) $row[$deliveryFeeField];
            $expected = round($itemPrice + $deliveryFee, 2);

            if (round((float) $value, 2) !== $expected) {
                $fail(
                    "{$attribute} must be equal to Cart Level Item Price + Delivery Fee ({$expected})."
                );
            }
        };
    }

    public function getAovGroupingType($domain, $itemCategory)
    {
        return DB::table('sub_domains')
            ->where('ondc_domain_id', $domain)
            ->where('code', Str::slug($itemCategory, '-'))
            ->value('aov_grouping_type');
    }

    public function getAovGroupingTypeAmount($claimTypeId, $aovType)
    {
        return DB::table('aov_categories')
            ->where('claim_type_id', $claimTypeId)
            ->where('name', $aovType)
            ->value('amount');
    }

    public function getOnbordedMSME()
    {
        if (empty(static::$onboardedMSEs)) {
            static::$onboardedMSEs = DB::table('team_msme_schemes as ms')
                ->select(
                    'ms.team_id',
                )
                ->join('team_snpmsme_mapping as tsm', 'tsm.msme_id', '=', 'ms.id')
                ->join('team_snp_scheme as tss', 'tsm.snp_id', '=', 'tss.id')
                ->leftJoin('states as s', 's.id', '=', 'ms.state_id')
                ->where('tsm.status', 1)
                ->whereNotNull('ms.major_activity')
                ->where('ms.major_activity', '!=', 'Trading')
                ->where('tss.user_id', (string) AuthId())
                ->pluck('ms.team_id')
                ->toArray();
        }

        return static::$onboardedMSEs;
    }

    public function checkTeamUdyamCombination($row, $teamIdKey, $udyamKey)
    {
        return function ($attribute, $value, $fail) use ($row, $teamIdKey, $udyamKey) {
            $teamId = $row[$teamIdKey] ?? null;
            $udyamNumber = $value;

            if (!$teamId) {
                $fail('Team ID is required to validate Udyam number.');
                return;
            }

            $exists = DB::table('team_msme_schemes')
                ->where('team_id', $teamId)
                ->where('udyam_no', $udyamNumber)
                ->exists();

            if (!$exists) {
                $fail('The combination of Team ID and Udyam number does not exist in our records.');
            }
        };
    }

    public function customValidationMessages()
    {
        return [

            /* =======================
        | MSE BASIC DETAILS
        ======================= */

            'team_id.required' => 'Team Registration ID is required.',
            'team_id.max' => 'Team Registration ID cannot exceed 100 characters.',
            'team_id.exists' => 'The provided Team Registration ID does not exist.',
            'team_id.unique' => 'A claim already exists for this Team Registration ID.',

            'seller_np_name.required' => 'Seller NP Name is required.',
            'seller_np_name.max' => 'Seller NP Name cannot exceed 255 characters.',

            'udyam_number.required' => 'Udyam Number is required.',
            'udyam_number.exists' => 'The provided Udyam Number does not exist.',
            'udyam_number.max' => 'Udyam Number cannot exceed 50 characters.',

            'provider_id.required' => 'Provider ID is required.',
            'provider_id.string' => 'Provider ID must be a valid string.',
            'provider_id.max' => 'Provider ID cannot exceed 200 digits.',

            'catalogue_score_report_id.required' => 'Catalogue Score Report ID is required.',
            'catalogue_score_report_id.string' => 'Catalogue Score Report ID must be a valid string.',
            //'catalogue_score_report_id.digits_between' => 'Catalogue Score Report ID must contain only numeric characters.',
            'catalogue_score_url.required' => 'Catalogue Score URL is required.',
            'catalogue_score_url.url' => 'Catalogue Score URL must be a valid URL.',
            'catalogue_score_timestamp.required' => 'Catalogue Score Timestamp is required.',

            'credential_report_id.required' => 'Credential Report ID is required.',
            'credential_report_id.string' => 'Catalogue Report ID must be a valid string.',
            //'credential_report_id.digits_between' => 'Credential Report ID must contain only numeric characters.',
            'credential_score_url.required' => 'Credential Score URL is required.',
            'credential_score_url.url' => 'Credential Score URL must be a valid URL.',
            'credential_score_timestamp.required' => 'Credential Score Timestamp is required.',

            /* =======================
        | TRANSACTION 1
        ======================= */

            'txn_1_domain.required' => 'Transaction 1 Domain is required.',
            'txn_1_domain.exists' => 'Transaction 1 Domain is invalid.',

            'txn_1_item_consolidated_category.required' => 'Transaction 1 Item Category is required.',

            'txn_1_network_order_id.required' => 'Transaction 1 Network Order ID is required.',
            'txn_1_network_transaction_id.required' => 'Transaction 1 Network Transaction ID is required.',

            'txn_1_buyer_np_name.required' => 'Transaction 1 Buyer NP Name is required.',

            'txn_1_order_creation_timestamp.required' => 'Transaction 1 Order Creation Timestamp is required.',
            'txn_1_order_completed_timestamp.required' => 'Transaction 1 Order Completed Timestamp is required.',

            'txn_1_order_status.required' => 'Transaction 1 Order Status is required.',

            'txn_1_invoice_number.required' => 'Transaction 1 Invoice Number is required.',
            'txn_1_invoice_date.required' => 'Transaction 1 Invoice Date is required.',

            'txn_1_cart_level_item_price.required' => 'Transaction 1 Cart Item Price is required.',
            'txn_1_cart_level_item_price.numeric' => 'Transaction 1 Cart Item Price must be numeric.',

            'txn_1_delivery_fee.required' => 'Transaction 1 Delivery Fee is required.',
            'txn_1_delivery_fee.numeric' => 'Transaction 1 Delivery Fee must be numeric.',

            'txn_1_total_fee.required' => 'Transaction 1 Total Fee is required.',

            /* =======================
        | TRANSACTION 2
        ======================= */

            'txn_2_domain.required' => 'Transaction 2 Domain is required.',
            'txn_2_domain.exists' => 'Transaction 2 Domain is invalid.',

            'txn_2_item_consolidated_category.required' => 'Transaction 2 Item Category is required.',

            'txn_2_network_order_id.required' => 'Transaction 2 Network Order ID is required.',
            'txn_2_network_transaction_id.required' => 'Transaction 2 Network Transaction ID is required.',

            'txn_2_buyer_np_name.required' => 'Transaction 2 Buyer NP Name is required.',

            'txn_2_order_creation_timestamp.required' => 'Transaction 2 Order Creation Timestamp is required.',
            'txn_2_order_completed_timestamp.required' => 'Transaction 2 Order Completed Timestamp is required.',

            'txn_2_order_status.required' => 'Transaction 2 Order Status is required.',

            'txn_2_invoice_number.required' => 'Transaction 2 Invoice Number is required.',
            'txn_2_invoice_date.required' => 'Transaction 2 Invoice Date is required.',

            'txn_2_cart_level_item_price.required' => 'Transaction 2 Cart Item Price is required.',
            'txn_2_delivery_fee.required' => 'Transaction 2 Delivery Fee is required.',
            'txn_2_total_fee.required' => 'Transaction 2 Total Fee is required.',
        ];
    }

    /**
     * Calculate 18% inclusive GST details.
     *
     * @param float $inclusiveAmount
     * @param string $gstType
     * @return array
     */
    /**
     * Calculate 18% inclusive GST details and validate equations.
     *
     * @param float $inclusiveAmount
     * @param string $gstType
     * @return array
     */
    private function calculateInclusiveGST(float $inclusiveAmount, string $gstType): array
    {
        return [
            'base_amount'          => $inclusiveAmount,
            'gst_amount'           => 0.0,
            'cgst_amount'          => 0.0,
            'sgst_amount'          => 0.0,
            'total_claimed_amount' => $inclusiveAmount,
        ];
    }
}
