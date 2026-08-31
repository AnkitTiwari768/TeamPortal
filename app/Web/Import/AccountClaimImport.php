<?php

declare(strict_types=1);

namespace App\Web\Import;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use App\Web\Claim\ClaimService;
use App\Web\Claim\ClaimReviewStatus;
use App\Domain\Batch\BatchStatus;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AccountClaimImport implements ToCollection, SkipsEmptyRows, WithMultipleSheets
{
    use Importable;
    public array $validRows = [];
    public array $invalidRows = [];
    public int $totalRows = 0;

    private const HIGH_AOV = 'High AOV';
    private const LOW_AOV = 'Low AOV';

    private const ORDER_STATUS_COMPLETED = 'Completed';

    private static array $excelNetworkOrderIds = [];
    private static array $excelTransactionIds = [];
    private static array $excelInvoiceNumbers = [];



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
        'credential_score_id',
        'domain',
        'item_consolidated_category',
        'network_order_id',
        'network_transaction_id',
        'buyer_np_name',
        'order_status',
        'order_creation_timestamp',
        'order_completed_timestamp',
        'invoice_number',
        'invoice_date',
        'cart_level_item_price',
        'delivery_fee',
        'total_fee'
    ];
    public function customAttributes()
    {
        return [
            // MSE Details
            'seller_np_name' => 'seller np name',
            'team_id'        => 'TEAM Registration Id of MSE',
            'udyam_number'   => 'Udyam Number',
            'provider_id'    => 'ONDC Seller Network ID of MSE',
            'catalogue_score_report_id' => 'Catalogue Score Report ID',
            'credential_report_id'      => 'Seller Credential Report ID',

            // Transaction 1 Details
            'domain' => 'Domain',
            'item_consolidated_category' => 'Item Consolidated Category',
            'network_order_id' => 'Network Order ID',
            'network_transaction_id' => 'Network Transaction ID',
            'buyer_np_name' => 'Buyer NP Name',
            'order_status' => 'Order Status',
            'order_creation_timestamp' => 'Order Creation Timestamp',
            'order_completed_timestamp' => 'Order Completed Timestamp',
            'invoice_number' => 'Invoice Number',
            'invoice_date' => 'Invoice Date',
            'cart_level_item_price' => 'Cart Level Item Price',
            'delivery_fee' => 'Delivery Fee',
            'total_fee' => 'Total Fee',
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
                // $this->checkMSE(),
            ],

            'seller_np_name' => [
                'required',
                'max:255',
            ],

            'udyam_number' => [
                'required',
                'max:50',
                $this->checkTeamUdyamCombination($row, 'team_id', 'udyam_number')
            ],

            'provider_id' => [
                'required',
                'max:200',
                // 'digits_between:1,200'
            ],

            'catalogue_score_report_id' => [
                'required',
                'max:200',
                $this->checkUniqueTeamCatalogueScoreId($row, 'team_id', 'catalogue_score_report_id'),
            ],

            'credential_report_id' => [
                'required',
                'max:200',
                $this->checkUniqueTeamCredentialScoreId($row, 'team_id', 'credential_report_id'),
            ],
            /* =======================
         | TRANSACTION 1 (REQUIRED)
         ======================= */
            'domain' => [
                'required',
                'exists:sub_domains,ondc_domain_id'
            ],

            'item_consolidated_category' => [
                'required',
                $this->checkSelectedDomainForItemCategory($row, 'domain')
            ],

            'network_order_id' => [
                'required',
                'max:100',
                $this->checkNetworkOrderId()
            ],

            'network_transaction_id' => [
                'required',
                'max:100',
                $this->checkNetworkTransactionId()
            ],

            'buyer_np_name' => [
                'required',
                'max:200'
            ],

            'order_creation_timestamp' => [
                'required',
                $this->isoTimestampRule()
            ],

            'order_completed_timestamp' => [
                'required',
                $this->isoTimestampRule()
            ],

            'order_status' => [
                'required',
                $this->checkOrderStatus()
            ],

            'invoice_number' => [
                'required',
                'max:100',
                $this->checkInvoiceNumber()
            ],

            'invoice_date' => [
                'required',
                $this->dateRule()
            ],

            'cart_level_item_price' => [
                'required',
                'numeric',
                'min:0',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],

            'delivery_fee' => [
                'required',
                'numeric',
                'min:0',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],

            'total_fee' => [
                'required',
                'numeric',
                'min:0',
                'regex:/^\d+(\.\d{1,2})?$/',
                $this->checkTotalFee($row, 'cart_level_item_price', 'delivery_fee')
            ],
        ];
    }

    public function customValidationMessages(): array
    {
        return [

            /* =======================
        | MSE BASIC DETAILS
        ======================= */

            'team_id.required' => 'Team ID is required.',
            'team_id.max' => 'Team ID must not exceed 100 characters.',
            'team_id.exists' => 'The selected Team ID does not exist in the system.',

            'seller_np_name.required' => 'Seller NP Name is required.',
            'seller_np_name.max' => 'Seller NP Name must not exceed 255 characters.',

            'udyam_number.required' => 'Udyam Number is required.',
            'udyam_number.max' => 'Udyam Number must not exceed 50 characters.',

            'provider_id.required' => 'Provider ID is required.',
            'provider_id.max' => 'Provider ID must not exceed 200 characters.',
            'provider_id.string' => 'Provider ID must be a valid string.',

            'catalogue_score_report_id.required' => 'Catalogue Score Report ID is required.',
            'catalogue_score_report_id.string' => 'Catalogue Score Report ID must be a valid string.',

            'credential_report_id.required' => 'Credential Report ID is required.',
            'credential_report_id.string' => 'Catalogue Report ID must be a valid string.',

            /* =======================
        | TRANSACTION 1 (REQUIRED)
        ======================= */

            'domain.required' => 'Domain is required.',
            'domain.exists' => 'The selected domain is invalid.',

            'item_consolidated_category.required' => 'Item consolidated category is required.',

            'network_order_id.required' => 'Network Order ID is required.',
            'network_order_id.max' => 'Network Order ID must not exceed 100 characters.',

            'network_transaction_id.required' => 'Network Transaction ID is required.',
            'network_transaction_id.max' => 'Network Transaction ID must not exceed 100 characters.',

            'buyer_np_name.required' => 'Buyer NP Name is required.',
            'buyer_np_name.max' => 'Buyer NP Name must not exceed 200 characters.',

            'order_creation_timestamp.required' => 'Order creation timestamp is required.',
            'order_completed_timestamp.required' => 'Order completed timestamp is required.',

            'order_status.required' => 'Order status is required.',

            'invoice_number.required' => 'Invoice number is required.',
            'invoice_number.max' => 'Invoice number must not exceed 100 characters.',

            'invoice_date.required' => 'Invoice date is required.',

            'cart_level_item_price.required' => 'Cart level item price is required.',
            'cart_level_item_price.numeric' => 'Cart level item price must be a numeric value.',
            'cart_level_item_price.min' => 'Cart level item price must be greater than or equal to 0.',
            'cart_level_item_price.regex' => 'Cart level item price must have up to 2 decimal places.',

            'delivery_fee.required' => 'Delivery fee is required.',
            'delivery_fee.numeric' => 'Delivery fee must be a numeric value.',
            'delivery_fee.min' => 'Delivery fee must be greater than or equal to 0.',
            'delivery_fee.regex' => 'Delivery fee must have up to 2 decimal places.',

            'total_fee.required' => 'Total fee is required.',
            'total_fee.numeric' => 'Total fee must be a numeric value.',
            'total_fee.min' => 'Total fee must be greater than or equal to 0.',
            'total_fee.regex' => 'Total fee must have up to 2 decimal places.',
        ];
    }


    public function collection(Collection $rows)
    {
        $this->totalRows = max(0, $rows->count() - 1);
        // Validate Excel header
        $headerRow = $rows->first()->toArray();
        $this->validateExcelHeader($headerRow);

        foreach ($rows as $index => $row) {

            if ($index == 0) continue; // Skip header row

            if ($this->SkipEmptyRow($row)) {
                continue; // Skip empty row
            }


            $rowData = [
                'seller_np_name' => $this->clean($row[0]),
                'team_id' => $this->clean($row[1]),
                'udyam_number' => $this->clean($row[2]),
                'provider_id' => $this->clean($row[3]),

                'catalogue_score_report_id' => $this->clean($row[4]),
                'credential_report_id' => $this->clean($row[5]),

                // Transaction
                'domain' => $this->clean($row[6]),
                'item_consolidated_category' => $this->clean($row[7]),
                'network_order_id' => $this->clean($row[8]),
                'network_transaction_id' => $this->clean($row[9]),
                'buyer_np_name' => $this->clean($row[10]),
                'order_status' => $this->clean($row[11]),
                'order_creation_timestamp' => $this->clean($row[12]),
                'order_completed_timestamp' => $this->clean($row[13]),
                'invoice_number' => $this->clean($row[14]),
                'invoice_date' => $this->clean($row[15]),
                'cart_level_item_price' => $this->clean($row[16]),
                'delivery_fee' => $this->clean($row[17]),
                'total_fee' => $this->clean($row[18]),
            ];

            $validator = Validator::make(
                $rowData,
                $this->rules($rowData),
                $this->customValidationMessages(),
                $this->customAttributes()
            );


            if ($validator->fails()) {
                $this->invalidRows[] = [
                    'row_number' => $index,
                    'data'       => $rowData,
                    'errors'     => $validator->errors()
                ];
                continue;
            }

            $rowData['_row_number'] = $index;
            $this->validRows[] = $rowData;
        }


        $this->validateSameProviderPerTeam();
        $this->validateUniqueProviderAcrossTeams();
        $this->validateSameCatalogueReportPerTeam();
        $this->validateUniqueCatalogueReportAcrossTeams();
        $this->validateSameCredentialReportPerTeam();
        $this->validateUniqueCredentialReportAcrossTeams();
        $this->cleanValidRows();

        if (empty($this->validRows)) {
            return;
        }

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


            /**
             * --------------------------------------
             * GROUP BY MSME (team_id)
             * --------------------------------------
             */
            $groupedMsmes = collect($this->validRows)->groupBy('team_id');

            foreach ($groupedMsmes as $teamId => $transactions) {

                $firstRow = $transactions->first();

                // Delete old temporary data
                $existingClaimId = DB::table('temporary_claims')
                    ->where('claim_type_id', $claimTypeId)
                    ->where('team_registration_id', $teamId)
                    ->where('snp_id', auth()->user()->username)
                    ->value('id');

                if ($existingClaimId) {
                    DB::table('temporary_claim_orders')
                        ->where('claim_id', $existingClaimId)
                        ->delete();

                    DB::table('temporary_claims')
                        ->where('id', $existingClaimId)
                        ->delete();
                }

                $msmeDetails = $claimService->getMsmeDetails($teamId);

                $claimId = uuid();

                /**
                 * --------------------------------------
                 * INSERT ONE CLAIM PER MSME
                 * --------------------------------------
                 */
                DB::table('temporary_claims')->insert([
                    'id'                            => $claimId,
                    'claim_type_id'                 => $claimTypeId,
                    'snp_id'                        => auth()->user()->username,
                    'team_registration_id'          => $teamId,
                    'catalogue_type'                => 'Manual',
                    'bpp_id'                        => $firstRow['provider_id'],
                    'msme_transaction_type'         => $msmeDetails->ondc_transaction_type_id ?? null,
                    'is_bulk'                       => true,
                    'status'                        => ClaimReviewStatus::TEMPORARY->value,
                    'msme_name'                     => $msmeDetails->enterprise_name ?? null,
                    'msme_udyam_number'             => $msmeDetails->udyam_no ?? null,
                    'msme_classification'           => $msmeDetails->msme_classification ?? null,
                    'msme_category'                 => $msmeDetails->major_activity ?? null,
                    'seller_np_name'                => $firstRow['seller_np_name'],
                    'ondc_seller_network_id'        => $firstRow['provider_id'],
                    'seller_credential_report'      => $firstRow['credential_report_id'] ?? null,
                    'catalogue_score_report'        => $firstRow['catalogue_score_report_id'] ?? null,
                    'team_id'                       => $teamId,
                    'created_by'                    => auth()->id(),
                    'created_at'                    => now(),
                    'updated_at'                    => now(),
                ]);

                /**
                 * --------------------------------------
                 * PREPARE MULTIPLE ORDER RECORDS
                 * --------------------------------------
                 */
                $claimOrders = [];

                foreach ($transactions as $row) {

                    $claimOrders[] = [
                        'id' => uuid(),
                        'claim_id' => $claimId,
                        'ondc_order_id' => $row['network_order_id'],
                        'seller_np_name' => $firstRow['seller_np_name'],
                        'domain' => $row['domain'],
                        'item_consolidated_category' => $row['item_consolidated_category'],
                        'aov_grouping_type' => $this->getAovGroupingType(
                            $row['domain'],
                            $row['item_consolidated_category']
                        ),
                        'network_transaction_id' => $row['network_transaction_id'],
                        'buyer_np_name' => $row['buyer_np_name'],
                        'order_status' => $row['order_status'],
                        'order_creation_timestamp' =>
                        $this->transformIsoTimestamp($row['order_creation_timestamp']),
                        'order_completed_timestamp' =>
                        $this->transformIsoTimestamp($row['order_completed_timestamp']),
                        'invoice_number' => $row['invoice_number'],
                        'invoice_date' =>
                        $row['invoice_date']
                            ? $this->transformDate($row['invoice_date'])
                            : null,
                        'cart_level_item_price' => $row['cart_level_item_price'],
                        'delivery_fee' => $row['delivery_fee'],
                        'total_fee' => $row['total_fee'],
                        'team_id' => $row['team_id'] ?? null,
                        'provider_id' => $row['provider_id'] ?? null,
                        'credential_score_id' => $row['credential_report_id'] ?? null,
                        'catalogue_score_id' => $row['catalogue_score_report_id'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($claimOrders)) {
                    DB::table('temporary_claim_orders')->insert($claimOrders);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function store($userId)
    {
        DB::beginTransaction();

        try {

            $claimService = app(ClaimService::class);
            $claimTypeId = DB::table('claim_types')->where('slug', 'claim-for-accounts-management')->value('id');

            $temporaryClaims = DB::table('temporary_claims')
                ->where('claim_type_id', $claimTypeId)
                ->where('created_by', $userId)
                ->where('status', ClaimReviewStatus::TEMPORARY->value)
                ->get();

            $claimData = [];
            $claimOrdersData = [];

            // Get GST type and percentages from session
            $gstType = session()->get('gst_type_' . $userId, null); // Default to GST type 1
            $gstPercentage = floatval(session()->get('gst_percentage_' . $userId, 0));
            $cgstPercentage = floatval(session()->get('cgst_percentage_' . $userId, 0));
            $sgstPercentage = floatval(session()->get('sgst_percentage_' . $userId, 0));

            $mseTeamId = [];

            if ($temporaryClaims) {

                foreach ($temporaryClaims as $index => $claim) {

                    $response = $this->calculateAccountIncentiveAmount($claim->id, $claimTypeId, $claim->team_registration_id);

                    if (! $response['status']) {
                        $this->invalidRows[] = [
                            'code' => $response['status_code'],
                            'message' => $response['message'],
                            'msme_name' => $claim->msme_name,
                            'team_id' => $claim->team_registration_id,
                            'udyam_number' => $claim->msme_udyam_number,
                        ];
                        continue;
                    }

                    $amount = $response['data']['amount'];
                    $bonusAmount = $response['data']['bonus_amount'];

                    // Calculate GST amount based on type using the helper
                    $gstResults = $this->calculateInclusiveGST((float)$amount, (string)$gstType);

                    $totalClaimedAmount = round($gstResults['total_claimed_amount'] + $bonusAmount, 2);

                    $claimData[] = [
                        'id'                            => $claim->id,
                        'claim_type_id'                 => $claim->claim_type_id,
                        'snp_id'                        => $claim->snp_id,
                        'application_number'            => $claimService->generateApplicationNumber($index),
                        'team_registration_id'          => $claim->team_registration_id,
                        'catalogue_type'                => $claim->catalogue_type,
                        'bpp_id'                        => $claim->bpp_id,
                        'msme_transaction_type'         => $claim->msme_transaction_type,
                        'is_bulk'                       => $claim->is_bulk,
                        'status'                        => ClaimReviewStatus::DRAFT->value,
                        'msme_name'                     => $claim->msme_name,
                        'msme_udyam_number'             => $claim->msme_udyam_number,
                        'msme_classification'           => $claim->msme_classification,
                        'msme_category'                 => $claim->msme_category,
                        'seller_np_name'                => $claim->seller_np_name,
                        'ondc_seller_network_id'        => $claim->ondc_seller_network_id,
                        'seller_credential_report'      => $claim->seller_credential_report,
                        'catalogue_score_report'        => $claim->catalogue_score_report,
                        'incentive_details'             => json_encode($response['data']),
                        'team_id'                       => $claim->team_id,
                        'created_by'                    => $claim->created_by,
                        'is_declaration_agreed'         => true,
                        'amount'                        => $gstResults['base_amount'],
                        'gst_type'                      => $gstType,
                        'gst_percentage'                => $gstPercentage,
                        'cgst_percentage'               => $cgstPercentage,
                        'sgst_percentage'               => $sgstPercentage,
                        'bonus_amount'                  => $bonusAmount,
                        'gst_amount'                    => $gstResults['gst_amount'],
                        'cgst_amount'                   => $gstResults['cgst_amount'],
                        'sgst_amount'                   => $gstResults['sgst_amount'],
                        'total_claimed_amount'          => $totalClaimedAmount,
                        'created_at'                    => now(),
                        'updated_at'                    => now(),
                    ];

                    $mseTeamId[] = $claim->team_registration_id;

                    $temporaryClaimOrders = DB::table('temporary_claim_orders')
                        ->where('claim_id', $claim->id)
                        ->get();

                    if ($temporaryClaimOrders) {
                        foreach ($temporaryClaimOrders as $claimOrder) {
                            $claimOrdersData[] = [
                                'id'                                    => $claimOrder->id,
                                'claim_id'                              => $claimOrder->claim_id,
                                'seller_np_name'                        => $claimOrder->seller_np_name,
                                'ondc_order_id'                         => $claimOrder->ondc_order_id,
                                'domain'                                => $claimOrder->domain,
                                'item_consolidated_category'            => $claimOrder->item_consolidated_category,
                                'aov_grouping_type'                     => $claimOrder->aov_grouping_type,
                                'network_transaction_id'                => $claimOrder->network_transaction_id,
                                'buyer_np_name'                         => $claimOrder->buyer_np_name,
                                'order_status'                          => $claimOrder->order_status,
                                'order_creation_timestamp'              => $claimOrder->order_creation_timestamp,
                                'order_completed_timestamp'             => $claimOrder->order_completed_timestamp,
                                'invoice_number'                        => $claimOrder->invoice_number,
                                'invoice_date'                          => $claimOrder->invoice_date,
                                'cart_level_item_price'                 => $claimOrder->cart_level_item_price,
                                'delivery_fee'                          => $claimOrder->delivery_fee,
                                'total_fee'                             => $claimOrder->total_fee,
                                'team_id'                               => $claimOrder->team_id ?? null,
                                'provider_id'                           => $claimOrder->provider_id ?? null,
                                'credential_score_id'                  => $claimOrder->credential_score_id ?? null,
                                'catalogue_score_id'                   => $claimOrder->catalogue_score_id ?? null,
                                'created_at'                            => now(),
                                'updated_at'                            => now(),
                            ];
                        }
                    }

                    $this->validRows[] = [
                        'code' => $response['status_code'],
                        'message' => $response['message'],
                        'msme_name' => $claim->msme_name,
                        'team_id' => $claim->team_registration_id,
                        'udyam_number' => $claim->msme_udyam_number,
                    ];
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

            DB::table('team_msme_schemes')->whereIn('team_id', $mseTeamId)->update([
                'is_account_claim_generated' => true
            ]);


            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function checkMSE()
    {
        return function ($attribute, $value, $fail) {
            $isCatalogueClaimApproved = DB::table('team_msme_schemes')
                ->where('team_id', $value)
                ->value('is_catalogue_claim_approved');

            if (! $isCatalogueClaimApproved) {
                $fail("The {$attribute} is not eligible for accounts management claim.");
            }
        };
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
            if (!$value) return;

            try {
                is_numeric($value)
                    ? ExcelDate::excelToDateTimeObject($value)
                    : Carbon::createFromFormat('d-m-Y', $value);
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

            if (! $exists) {
                $fail('Invalid Item Consolidated Category for the selected Domain - value:' . $value . ' and domain: ' . $domain);
            }
        };
    }

    public function checkNetworkOrderId()
    {
        return function ($attr, $value, $fail) {
            if (in_array($value, self::$excelNetworkOrderIds)) {
                $fail("{$attr} '{$value}' is duplicated in Excel.");
            }

            if (DB::table('claim_orders')
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

            if (DB::table('claim_orders')
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

            if (DB::table('claim_orders')
                ->where('invoice_number', $value)
                ->exists()
            ) {
                $fail("{$attr} '{$value}' already exists.");
            }

            self::$excelInvoiceNumbers[] = $value;
        };
    }

    public function checkTotalFee($row, $itemPriceField, $deliveryFeeField)
    {
        return function ($attribute, $value, $fail) use ($row, $itemPriceField, $deliveryFeeField) {
            $itemPrice   = (float) $row[$itemPriceField];
            $deliveryFee = (float) $row[$deliveryFeeField];
            $expected    = round($itemPrice + $deliveryFee, 2);

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

    /**
     * Standardized response format
     */
    private function response(bool $status, string $statusCode, string $message, ?array $data = []): array
    {
        return [
            'status' => $status,
            'status_code' => $statusCode,
            'message' => $message,
            'data' => $data
        ];
    }

    public function calculateAccountIncentiveAmount(string $claimId, string $claimTypeId, string $msmeTeamId)
    {
        $claim = DB::table('temporary_claims')->where('id', $claimId)->first();

        if (!$claim) {
            return $this->response(status: false, statusCode: 'CLAIM_NOT_FOUND', message: 'Claim not found');
        }


        $orders = DB::table('temporary_claim_orders')
            ->where('claim_id', $claimId)
            ->where('order_status', self::ORDER_STATUS_COMPLETED) // SOP condition
            ->get();


        if ($orders->isEmpty()) {
            return $this->response(status: false, statusCode: 'CLAIM_TRANSACTIONS_NOT_FOUND', message: 'No eligible transactions.');
        }

        $orders = collect($orders);

        /**
         * STEP 1: Remove duplicate orders
         */
        $orders = $orders->unique('ondc_order_id');

        /**
         * STEP 2: Apply HIGH AOV override rule
         * If HIGH exists → ignore LOW
         */
        $highAov = $orders->where('aov_grouping_type', 'High AOV');
        $lowAov  = $orders->where('aov_grouping_type', 'Low AOV');

        if ($highAov->count() > 0) {
            $eligibleOrders = $highAov;
            $aovType = 'High AOV';
        } else {
            $eligibleOrders = $lowAov;
            $aovType = 'Low AOV';
        }

        $transactionCount = $eligibleOrders->count();

        if ($transactionCount === 0) {
            return $this->response(status: false, statusCode: 'ELIGIBLE_AOV_TRANSACTIONS_NOT_FOUND', message: 'No eligible AOV transactions.');
        }

        $rules = DB::table('claim_incentive_validations_rules')
            ->where('claim_type', 'claim-for-accounts-management')
            ->where('aov_grouping_type', $aovType)
            ->where('status', 1)
            ->orderBy('min_transactions')
            ->get();

        if ($rules->isEmpty()) {
            return $this->response(status: false, statusCode: 'ELIGIBLE_RULE_NOT_FOUND', message: 'No incentive rules configured.');
        }

        $eligibleRule = collect($rules)
            ->where('min_transactions', '<=', $transactionCount)
            ->sortByDesc('min_transactions')
            ->first();

        if (!$eligibleRule) {
            return $this->response(status: false, statusCode: 'MINIMUM_MILESTONE_NOT_FOUND', message: 'Minimum milestone not achieved.');
        }

        $claims = DB::table('claims as c')
            ->join('team_msme_schemes as msme', 'msme.team_id', '=', 'c.team_registration_id')
            ->join('claim_types as ct', 'ct.id', '=', 'c.claim_type_id')
            ->where('msme.team_id', $msmeTeamId)
            ->where('ct.id', $claimTypeId)
            ->whereNotIn('c.claim_status', [
                BatchStatus::REJECTED_BY_ONDC->value,
                BatchStatus::REJECTED_BY_NSIC->value,
                BatchStatus::REJECTED_NSIC_FINANCE->value,
            ])
            ->where('c.updated_at', '>=',  now()->subYear())
            ->orderBy('c.updated_at')
            ->select('c.*')
            ->get();

        $capAmount = (float) $eligibleRule->incentive_threshold_amount;
        $paidAmount = (float) $claims->sum('amount');
        $totalBonusPaid = (int) $claims->sum('bonus_amount');
        $incentiveAmount = (float) $eligibleRule->incentive_amount;

        if ($paidAmount >= $capAmount) {
            return $this->response(status: false, statusCode: 'MAXIMUM_CAP_EXHAUSTED', message: 'Maximum incentive limit already exhausted.');
        } elseif ($paidAmount >= $capAmount && $totalBonusPaid === 0) {
            $bonusDays = (int)   $eligibleRule->bonus_days;
            $bonusPercent = (float) $eligibleRule->bonus_percent;
            $cycleStartDate = $claims->first()?->updated_at;

            if ($paidAmount >= $capAmount && $bonusDays > 0 && $bonusPercent > 0) {
                $daysToComplete = Carbon::parse($cycleStartDate)->diffInDays(now());

                if ($daysToComplete <= $bonusDays) {
                    $bonusAmount = $capAmount * ($bonusPercent / 100);

                    $statusCode = 'BONUS_CREDITED';
                    $message = "Bonus incentive has been successfully credited of ₹{$bonusAmount}.";
                }
            }

            $statusCode = 'MAXIMUM_CAP_EXHAUSTED';
            $message = 'Maximum incentive limit already exhausted.';
        } else {
            $remainingCap  = max(0, $capAmount - $paidAmount);
            $payableAmount = min($incentiveAmount, $remainingCap);
            $amountPaidTillNow = $paidAmount + $payableAmount;
            $remainingAmount = max(0, $capAmount - $amountPaidTillNow);

            if ($remainingAmount > 0) {
                $statusCode = 'PARTIAL_INCENTIVE';
                $message = "You are eligible for ₹{$payableAmount}. Remaining incentive balance is ₹{$remainingAmount}.";
            } else {
                $statusCode = 'INCENTIVE_CAP_COMPLETED';
                $message = "You have exhausted the maximum incentive limit of ₹{$capAmount}.";
            }
        }

        $incentiveDetails = [
            'aov_type'         => $aovType,
            'rule_id'          => $eligibleRule->id,
            'milestone'        => $eligibleRule->milestone_code,
            'cap_amount'       => $capAmount,
            'remainingCap'     => isset($remainingCap) ? round($remainingCap, 2) : 0,
            'amount'           => isset($payableAmount) ? round($payableAmount, 2) : 0,
            'amount_paid_till_now' => isset($amountPaidTillNow) ? round($amountPaidTillNow, 2) : round($paidAmount, 2),
            'remaining_amount' => isset($remainingAmount) ? round($remainingAmount, 2) : 0,
            'bonus_amount'     => isset($bonusAmount) ? round($bonusAmount, 2) : 0,
            'paidAmount'       => round($paidAmount, 2),
        ];

        return $this->response(status: true, statusCode: $statusCode, message: $message, data: $incentiveDetails);
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

    private function validateSameProviderPerTeam(): void
    {
        if (empty($this->validRows)) {
            return;
        }

        $groupedRows = collect($this->validRows)->groupBy('team_id');

        $invalidTeamIds = [];

        foreach ($groupedRows as $teamId => $rows) {
            $providerIds = collect($rows)
                ->pluck('provider_id')
                ->filter(fn($value) => $value !== null && $value !== '')
                ->unique()
                ->values();

            if ($providerIds->count() > 1) {
                $invalidTeamIds[] = $teamId;

                foreach ($rows as $row) {
                    $this->invalidRows[] = [
                        'row_number' => $row['_row_number'] ?? null,
                        'data'       => $row,
                        'errors'     => [
                            'provider_id' => [
                                "For Team ID '{$teamId}', Provider ID must be same in all rows. Found Provider IDs: " . $providerIds->implode(', ')
                            ]
                        ]
                    ];
                }
            }
        }

        if (!empty($invalidTeamIds)) {
            $this->validRows = collect($this->validRows)
                ->reject(fn($row) => in_array($row['team_id'], $invalidTeamIds, true))
                ->values()
                ->all();
        }
    }

    private function validateUniqueProviderAcrossTeams(): void
    {
        if (empty($this->validRows)) {
            return;
        }

        $groupedByProvider = collect($this->validRows)->groupBy('provider_id');

        $invalidRowKeys = [];

        foreach ($groupedByProvider as $providerId => $rows) {
            $teamIds = collect($rows)
                ->pluck('team_id')
                ->filter(fn($value) => $value !== null && $value !== '')
                ->unique()
                ->values();

            // Same provider_id found against multiple different team_id
            if ($teamIds->count() > 1) {
                foreach ($rows as $rowIndex => $row) {
                    $this->invalidRows[] = [
                        'row_number' => $row['_row_number'] ?? null,
                        'data'       => $row,
                        'errors'     => [
                            'provider_id' => [
                                "Provider ID '{$providerId}' cannot be mapped to multiple MSME Team IDs. Found Team IDs: " . $teamIds->implode(', ')
                            ]
                        ]
                    ];

                    $invalidRowKeys[] = ($row['_row_number'] ?? null);
                }
            }
        }

        // Remove invalid rows from validRows
        if (!empty($invalidRowKeys)) {
            $this->validRows = collect($this->validRows)
                ->reject(function ($row) use ($invalidRowKeys) {
                    return in_array($row['_row_number'] ?? null, $invalidRowKeys, true);
                })
                ->values()
                ->all();
        }
    }

    private function validateSameCatalogueReportPerTeam(): void
    {
        if (empty($this->validRows)) {
            return;
        }

        $groupedRows = collect($this->validRows)->groupBy('team_id');

        $invalidTeamIds = [];

        foreach ($groupedRows as $teamId => $rows) {
            $reportIds = collect($rows)
                ->pluck('catalogue_score_report_id')
                ->filter(fn($value) => $value !== null && $value !== '')
                ->unique()
                ->values();

            if ($reportIds->count() > 1) {
                $invalidTeamIds[] = $teamId;

                foreach ($rows as $row) {
                    $this->invalidRows[] = [
                        'row_number' => $row['_row_number'] ?? null,
                        'data'       => $row,
                        'errors'     => [
                            'catalogue_score_report_id' => [
                                "For Team ID '{$teamId}', Catalogue Score Report ID must be same in all rows. Found Catalogue Score Report IDs: " . $reportIds->implode(', ')
                            ]
                        ]
                    ];
                }
            }
        }

        if (!empty($invalidTeamIds)) {
            $this->validRows = collect($this->validRows)
                ->reject(fn($row) => in_array($row['team_id'], $invalidTeamIds, true))
                ->values()
                ->all();
        }
    }

    private function validateUniqueCatalogueReportAcrossTeams(): void
    {
        if (empty($this->validRows)) {
            return;
        }

        $groupedByReport = collect($this->validRows)->groupBy('catalogue_score_report_id');

        $invalidRowKeys = [];

        foreach ($groupedByReport as $reportId => $rows) {
            if ($reportId === null || $reportId === '') {
                continue;
            }

            $teamIds = collect($rows)
                ->pluck('team_id')
                ->filter(fn($value) => $value !== null && $value !== '')
                ->unique()
                ->values();

            // Same catalogue_score_report_id found against multiple different team_id
            if ($teamIds->count() > 1) {
                foreach ($rows as $row) {
                    $this->invalidRows[] = [
                        'row_number' => $row['_row_number'] ?? null,
                        'data'       => $row,
                        'errors'     => [
                            'catalogue_score_report_id' => [
                                "Catalogue Score Report ID '{$reportId}' cannot be mapped to multiple MSME Team IDs. Found Team IDs: " . $teamIds->implode(', ')
                            ]
                        ]
                    ];

                    $invalidRowKeys[] = ($row['_row_number'] ?? null);
                }
            }
        }

        if (!empty($invalidRowKeys)) {
            $this->validRows = collect($this->validRows)
                ->reject(function ($row) use ($invalidRowKeys) {
                    return in_array($row['_row_number'] ?? null, $invalidRowKeys, true);
                })
                ->values()
                ->all();
        }
    }

    private function validateSameCredentialReportPerTeam(): void
    {
        if (empty($this->validRows)) {
            return;
        }

        $groupedRows = collect($this->validRows)->groupBy('team_id');

        $invalidTeamIds = [];

        foreach ($groupedRows as $teamId => $rows) {
            $reportIds = collect($rows)
                ->pluck('credential_report_id')
                ->filter(fn($value) => $value !== null && $value !== '')
                ->unique()
                ->values();

            if ($reportIds->count() > 1) {
                $invalidTeamIds[] = $teamId;

                foreach ($rows as $row) {
                    $this->invalidRows[] = [
                        'row_number' => $row['_row_number'] ?? null,
                        'data'       => $row,
                        'errors'     => [
                            'credential_report_id' => [
                                "For Team ID '{$teamId}', Credential Report ID must be same in all rows. Found Credential Report IDs: " . $reportIds->implode(', ')
                            ]
                        ]
                    ];
                }
            }
        }

        if (!empty($invalidTeamIds)) {
            $this->validRows = collect($this->validRows)
                ->reject(fn($row) => in_array($row['team_id'], $invalidTeamIds, true))
                ->values()
                ->all();
        }
    }

    private function validateUniqueCredentialReportAcrossTeams(): void
    {
        if (empty($this->validRows)) {
            return;
        }

        $groupedByReport = collect($this->validRows)->groupBy('credential_report_id');

        $invalidRowKeys = [];

        foreach ($groupedByReport as $reportId => $rows) {
            if ($reportId === null || $reportId === '') {
                continue;
            }

            $teamIds = collect($rows)
                ->pluck('team_id')
                ->filter(fn($value) => $value !== null && $value !== '')
                ->unique()
                ->values();

            // Same credential_report_id found against multiple different team_id
            if ($teamIds->count() > 1) {
                foreach ($rows as $row) {
                    $this->invalidRows[] = [
                        'row_number' => $row['_row_number'] ?? null,
                        'data'       => $row,
                        'errors'     => [
                            'credential_report_id' => [
                                "Credential Report ID '{$reportId}' cannot be mapped to multiple MSME Team IDs. Found Team IDs: " . $teamIds->implode(', ')
                            ]
                        ]
                    ];

                    $invalidRowKeys[] = ($row['_row_number'] ?? null);
                }
            }
        }

        if (!empty($invalidRowKeys)) {
            $this->validRows = collect($this->validRows)
                ->reject(function ($row) use ($invalidRowKeys) {
                    return in_array($row['_row_number'] ?? null, $invalidRowKeys, true);
                })
                ->values()
                ->all();
        }
    }

    private function cleanValidRows(): void
    {
        $this->validRows = collect($this->validRows)
            ->map(function ($row) {
                unset($row['_row_number']);
                return $row;
            })
            ->values()
            ->all();
    }

    public function checkUniqueTeamCatalogueScoreId($row, $teamIdKey, $catalogueScoreIdKey)
    {
        return function ($attribute, $value, $fail) use ($row, $teamIdKey, $catalogueScoreIdKey) {
            $teamId = $row[$teamIdKey] ?? null;
            $catalogueScoreId = $value;

            if (!$teamId || !$catalogueScoreId) {
                return;
            }

            $existsOtherTeam = DB::table('claims')
                ->where('catalogue_score_report', $catalogueScoreId)
                ->where('team_registration_id', '!=', $teamId)
                ->exists();

            if ($existsOtherTeam) {
                $fail("Catalogue Score Report ID '{$catalogueScoreId}' cannot be assigned to another MSME (Team ID).");
                return;
            }

            $existsSameTeam = DB::table('claims')
                ->where('team_registration_id', $teamId)
                ->where('catalogue_score_report', $catalogueScoreId)
                ->exists();

            if ($existsSameTeam) {
                $fail("The combination of Team ID '{$teamId}' and Catalogue Score ID '{$catalogueScoreId}' already exists.");
            }
        };
    }

    public function checkUniqueTeamCredentialScoreId($row, $teamIdKey, $credentialScoreIdKey)
    {
        return function ($attribute, $value, $fail) use ($row, $teamIdKey, $credentialScoreIdKey) {
            $teamId = $row[$teamIdKey] ?? null;
            $credentialScoreId = $value;

            if (!$teamId || !$credentialScoreId) {
                return;
            }

            $existsOtherTeam = DB::table('claims')
                ->where('seller_credential_report', $credentialScoreId)
                ->where('team_registration_id', '!=', $teamId)
                ->exists();

            if ($existsOtherTeam) {
                $fail("Credential Report ID '{$credentialScoreId}' cannot be assigned to another MSME (Team ID).");
                return;
            }

            $existsSameTeam = DB::table('claims')
                ->where('team_registration_id', $teamId)
                ->where('seller_credential_report', $credentialScoreId)
                ->exists();

            if ($existsSameTeam) {
                $fail("The combination of Team ID '{$teamId}' and Credential Score ID '{$credentialScoreId}' already exists.");
            }
        };
    }

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
