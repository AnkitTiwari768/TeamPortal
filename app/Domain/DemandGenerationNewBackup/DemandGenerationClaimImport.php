<?php

declare(strict_types=1);

namespace App\Domain\DemandGeneration;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use App\Web\Claim\ClaimService;
use App\Web\Claim\ClaimReviewStatus;
use App\Domain\Batch\BatchStatus;
use App\Web\Import\Importable;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DemandGenerationClaimImport implements ToCollection, SkipsEmptyRows, WithMultipleSheets
{
    use Importable;
    public array $validRows = [];
    public array $invalidRows = [];
    private array $teamMsmeMap = [];
    private array $domainCategoryMap = [];
    private array $aovMap = [];
    private array $existingOrders = [];
    private array $existingTransactions = [];
    private array $existingInvoices = [];
    private \Illuminate\Support\Collection $msmeData;
    public string $claimId;
    private bool $headerValidated = false;

    public int $totalRows = 0;
    public int $insertedCount = 0;
    public int $invalidCount = 0;



    private const HIGH_AOV = 'High AOV';
    private const LOW_AOV = 'Low AOV';

    private const ORDER_STATUS_COMPLETED = 'Completed';

    private static array $excelNetworkOrderIds = [];
    private static array $excelTransactionIds = [];
    private static array $excelInvoiceNumbers = [];

    private string $claimStartDate;
    private string $claimEndDate;


    public function sheets(): array
    {
        return [
            0 => $this
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    protected array $requiredHeaders = [
        'buyer_np_name',
        'unique_team_registration_id_of_the_mse',
        'udyam_number',
        'provider_id',
        'domain',
        'item_consolidated_category',
        'network_order_id',
        'network_transaction_id',
        'seller_np_name',
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
            'buyer_np_name'                                 => 'Buyer np name',
            'unique_team_registration_id_of_the_mse'        => 'Unique TEAM Registration Id of the MSE',
            'udyam_number'                                  => 'Udyam Number',
            'provider_id'                                   => 'Provider Id',
            'domain'                                        => 'Domain',
            'item_consolidated_category'                    => 'Item Consolidated Category',
            'network_order_id'                              => 'Network Order ID',
            'network_transaction_id'                        => 'Network Transaction ID',
            'seller_np_name'                                => 'Seller NP Name',
            'order_status'                                  => 'Order Status',
            'order_creation_timestamp'                      => 'Order Creation Timestamp',
            'order_completed_timestamp'                     => 'Order Completed Timestamp',
            'invoice_number'                                => 'Invoice Number',
            'invoice_date'                                  => 'Invoice Date',
            'cart_level_item_price'                         => 'Cart Level Item Price',
            'delivery_fee'                                  => 'Delivery Fee',
            'total_fee'                                     => 'Total Fee',
        ];
    }

    public function rules($row): array
    {
        return [

            'buyer_np_name' => [
                'required',
                'max:255',
            ],

            'unique_team_registration_id_of_the_mse' => [
                'required',
                'max:100',
                function ($attr, $value, $fail) {
                    if (!isset($this->teamMsmeMap[$value])) {
                        $fail('The TEAM Registration ID does not exist.');
                    }
                }
            ],

            'udyam_number' => [
                'required',
                'max:50',
                function ($attr, $value, $fail) use ($row) {

                    $teamId = $row['unique_team_registration_id_of_the_mse'] ?? null;

                    if (!$teamId || !isset($this->teamMsmeMap[$teamId])) {
                        $fail('Invalid TEAM ID for Udyam validation.');
                        return;
                    }

                    if (!in_array($value, $this->teamMsmeMap[$teamId]['udyams'])) {
                        $fail('Udyam Number does not match the TEAM ID.');
                    }
                }
            ],

            'provider_id' => [
                'required',
                'max:200',
                'digits_between:1,200'
            ],

            'domain' => [
                'required',
                function ($attr, $value, $fail) {
                    if (!isset($this->domainCategoryMap[$value])) {
                        $fail('The selected domain is invalid.');
                    }
                }
            ],

            'item_consolidated_category' => [
                'required',
                function ($attr, $value, $fail) use ($row) {

                    $domain = $row['domain'] ?? null;

                    if (!$domain || !isset($this->domainCategoryMap[$domain][$value])) {
                        $fail("Invalid Item Category '{$value}' for selected Domain.");
                    }
                }
            ],

            'network_order_id' => [
                'required',
                'max:100',
                function ($attr, $value, $fail) {

                    if (in_array($value, self::$excelNetworkOrderIds)) {
                        $fail("{$attr} '{$value}' is duplicated in Excel.");
                        return;
                    }

                    if (in_array($value, $this->existingOrders)) {
                        $fail("{$attr} '{$value}' already exists.");
                        return;
                    }

                    self::$excelNetworkOrderIds[] = $value;
                }
            ],

            'network_transaction_id' => [
                'required',
                'max:100',
                function ($attr, $value, $fail) {

                    if (in_array($value, self::$excelTransactionIds)) {
                        $fail("{$attr} '{$value}' is duplicated in Excel.");
                        return;
                    }

                    if (in_array($value, $this->existingTransactions)) {
                        $fail("{$attr} '{$value}' already exists.");
                        return;
                    }

                    self::$excelTransactionIds[] = $value;
                }
            ],

            'seller_np_name' => [
                'required',
                'max:200'
            ],

            'order_creation_timestamp' => [
                'required',
                $this->isoTimestampRule(),
                // new OrderDateWithinClaimPeriod(
                //     request()->input('low_aov_claim_start_date'),
                //     request()->input('low_aov_claim_end_date'),
                // ),
            ],

            'order_completed_timestamp' => [
                'required',
                $this->isoTimestampRule()
            ],

            'order_status' => [
                'required',
                function ($attr, $value, $fail) {
                    if (strtoupper($value) !== 'COMPLETED') {
                        $fail("Order Status must be Completed.");
                    }
                }
            ],

            'invoice_number' => [
                'required',
                'max:100',
                function ($attr, $value, $fail) {

                    if (in_array($value, self::$excelInvoiceNumbers)) {
                        $fail("{$attr} '{$value}' is duplicated in Excel.");
                        return;
                    }

                    if (in_array($value, $this->existingInvoices)) {
                        $fail("{$attr} '{$value}' already exists.");
                        return;
                    }

                    self::$excelInvoiceNumbers[] = $value;
                }
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
            'buyer_np_name.required' => 'Buyer NP Name is required.',
            'buyer_np_name.max' => 'Buyer NP Name must not exceed 255 characters.',

            'unique_team_registration_id_of_the_mse.required' => 'TEAM Registration ID of the MSE is required.',
            'unique_team_registration_id_of_the_mse.max' => 'TEAM Registration ID must not exceed 100 characters.',
            'unique_team_registration_id_of_the_mse.exists' => 'The provided TEAM Registration ID does not exist in the system.',

            'udyam_number.required' => 'Udyam Number is required.',
            'udyam_number.max' => 'Udyam Number must not exceed 50 characters.',

            'provider_id.required' => 'Provider ID is required.',
            'provider_id.max' => 'Provider ID must not exceed 200 characters.',
            'provider_id.digits_between' => 'Provider ID must contain only numeric digits and be within valid length.',

            'domain.required' => 'Domain is required.',
            'domain.exists' => 'The selected domain is invalid or does not exist.',

            'item_consolidated_category.required' => 'Item Consolidated Category is required.',

            'network_order_id.required' => 'Network Order ID is required.',
            'network_order_id.max' => 'Network Order ID must not exceed 100 characters.',

            'network_transaction_id.required' => 'Network Transaction ID is required.',
            'network_transaction_id.max' => 'Network Transaction ID must not exceed 100 characters.',

            'seller_np_name.required' => 'Seller NP Name is required.',
            'seller_np_name.max' => 'Seller NP Name must not exceed 200 characters.',

            'order_creation_timestamp.required' => 'Order Creation Timestamp is required.',
            'order_completed_timestamp.required' => 'Order Completed Timestamp is required.',

            'order_status.required' => 'Order Status is required.',

            'invoice_number.required' => 'Invoice Number is required.',
            'invoice_number.max' => 'Invoice Number must not exceed 100 characters.',

            'invoice_date.required' => 'Invoice Date is required.',

            'cart_level_item_price.required' => 'Cart Level Item Price is required.',
            'cart_level_item_price.numeric' => 'Cart Level Item Price must be a valid number.',
            'cart_level_item_price.min' => 'Cart Level Item Price cannot be negative.',
            'cart_level_item_price.regex' => 'Cart Level Item Price can have up to 2 decimal places only.',

            'delivery_fee.required' => 'Delivery Fee is required.',
            'delivery_fee.numeric' => 'Delivery Fee must be a valid number.',
            'delivery_fee.min' => 'Delivery Fee cannot be negative.',
            'delivery_fee.regex' => 'Delivery Fee can have up to 2 decimal places only.',

            'total_fee.required' => 'Total Fee is required.',
            'total_fee.numeric' => 'Total Fee must be a valid number.',
            'total_fee.min' => 'Total Fee cannot be negative.',
            'total_fee.regex' => 'Total Fee can have up to 2 decimal places only.',
        ];
    }


    public function collection(Collection $rows)
    {
        // Header validation (only once)
        if (!isset($this->headerValidated)) {
            $headerRow = $rows->first()->toArray();
            $this->validateExcelHeader($headerRow);
            $this->headerValidated = true;
        }

        // Preload only once
        if (empty($this->teamMsmeMap)) {
            $this->preloadData();
        }

        // Initialize claim only once
        if (!isset($this->claimId)) {

            $this->claimId = uuid();

            $this->claimStartDate = request('low_aov_claim_start_date')
                ?? request('high_aov_claim_period_start');

            $this->claimEndDate = request('low_aov_claim_end_date')
                ?? request('high_aov_claim_period_end');

            $claimMonth = $this->claimStartDate
                ? Carbon::parse($this->claimStartDate)->format('Y-m')
                : null;

            $claimTypeId = $this->getClaimTypeId('claim-for-demand-generation');

            $demandGenerationService = app(DemandGenerationClaimImportService::class);
            $networkParticipant = $demandGenerationService->getNetworkProviderDetailsByUserID(auth()->id());

            DB::table('temporary_claims')->insert([
                'id'                            => $this->claimId,
                'claim_type_id'                 => $claimTypeId,
                'snp_id'                        => auth()->user()->username,
                'bpp_id'                        => $networkParticipant->bppid_providerid ?? null,
                'is_bulk'                       => true,
                'claim_month'                   => $claimMonth,
                'claim_period_start_date'       => $this->claimStartDate,
                'claim_period_end_date'         => $this->claimEndDate,
                'total_uploaded_records'        => 0,
                'total_valid_records'           => 0,
                'total_invalid_records'         => 0,
                'total_unique_mse_count'        => 0,
                'status'                        => ClaimReviewStatus::TEMPORARY->value,
                'created_by'                    => auth()->id(),
                'created_at'                    => now(),
                'updated_at'                    => now(),
            ]);
        }

        $batchInsert = [];

        foreach ($rows as $index => $row) {

            if ($index == 0) continue;

            if ($this->SkipEmptyRow($row)) continue;

            $this->totalRows++;

            $rowData = array_combine(
                $this->requiredHeaders,
                array_map(fn($value) => $this->clean($value), $row->toArray())
            );

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

                $this->invalidCount++;
                continue;
            }

            $this->insertedCount++;


            // ✅ AOV TYPE (NO DB CALL)
            $aovKey = $rowData['domain'] . '_' . Str::slug($rowData['item_consolidated_category'], '-');
            $aovType = $this->aovMap[$aovKey] ?? null;

            $teamId = $rowData['unique_team_registration_id_of_the_mse'];
            $msmeDetails = $this->msmeData[$teamId] ?? null;

            $batchInsert[] = [
                'id' => uuid(),
                'claim_id' => $this->claimId,
                'provider_id' => $rowData['provider_id'],
                'team_id' => $teamId,

                'msme_name' => $msmeDetails->enterprise_name ?? null,
                'msme_udyam_number' => $msmeDetails->udyam_no ?? null,
                'msme_classification' => $msmeDetails->msme_classification ?? null,
                'msme_category' => $msmeDetails->major_activity ?? null,
                'msme_transaction_type' => $msmeDetails->ondc_transaction_type_id ?? null,

                'ondc_order_id' => $rowData['network_order_id'],
                'buyer_np_name' => $rowData['buyer_np_name'],
                'seller_np_name' => $rowData['seller_np_name'],

                'domain' => $rowData['domain'],
                'item_consolidated_category' => $rowData['item_consolidated_category'],
                'aov_grouping_type' => $aovType,

                'network_transaction_id' => $rowData['network_transaction_id'],
                'order_status' => $rowData['order_status'],

                'order_creation_timestamp' => $this->transformIsoTimestamp($rowData['order_creation_timestamp']),
                'order_completed_timestamp' => $this->transformIsoTimestamp($rowData['order_completed_timestamp']),

                'invoice_number' => $rowData['invoice_number'],
                'invoice_date' => $rowData['invoice_date']
                    ? $this->transformDate($rowData['invoice_date'])
                    : null,

                'cart_level_item_price' => $rowData['cart_level_item_price'],
                'delivery_fee' => $rowData['delivery_fee'],
                'total_fee' => $rowData['total_fee'],

                'created_at' => now(),
                'updated_at' => now(),
            ];

            // ✅ Batch insert every 500 rows
            if (count($batchInsert) >= 500) {
                DB::table('temporary_claim_orders')->insert($batchInsert);
                $batchInsert = [];
            }
        }

        // Final insert
        if (!empty($batchInsert)) {
            DB::table('temporary_claim_orders')->insert($batchInsert);
        }
    }


    public function store($userId)
    {
        DB::beginTransaction();

        try {

            $claimService = app(ClaimService::class);
            $claimTypeId = DB::table('claim_types')->where('slug', 'claim-for-demand-generation')->value('id');

            $temporaryClaims = DB::table('temporary_claims')
                ->where('claim_type_id', $claimTypeId)
                ->where('created_by', $userId)
                ->where('status', ClaimReviewStatus::TEMPORARY->value)
                ->get();

            $calculator = app(DemandGenerationClaimAmountCalculator::class);

            $claimData = [];
            $claimOrdersData = [];

            if ($temporaryClaims) {

                foreach ($temporaryClaims as $index => $claim) {

                    $response = $calculator->calculateClaimAmount(
                        $claim->id,
                        $claimTypeId,
                        $claim->snp_id,
                        Carbon::parse($claim->claim_period_start_date),
                        Carbon::parse($claim->claim_period_end_date)
                    );

                    // dd($response);

                    $amount = 0;
                    if (isset($response['Low AOV']['claim_amount']) && isset($response['High AOV']['claim_amount'])) {
                        $amount = $response['Low AOV']['claim_amount'] + $response['High AOV']['claim_amount'];
                    } elseif (isset($response['Low AOV']['claim_amount'])) {
                        $amount = $response['Low AOV']['claim_amount'];
                    } elseif (isset($response['High AOV']['claim_amount'])) {
                        $amount = $response['High AOV']['claim_amount'];
                    }

                    $summary = $calculator->prepareClaimSummary($response);

                    $this->claimId = $claim->id;

                    DB::table('claims')->insert([
                        'id'                            => $claim->id,
                        'claim_type_id'                 => $claim->claim_type_id,
                        'snp_id'                        => $claim->snp_id,
                        'application_number'            => $claimService->generateDGApplicationNumber($claim->snp_id, $index),
                        'claim_month'                   => $claim->claim_month,
                        'claim_period_start_date'       => $claim->claim_period_start_date,
                        'claim_period_end_date'         => $claim->claim_period_end_date,
                        'total_uploaded_records'        => $claim->total_uploaded_records,
                        'total_valid_records'           => $claim->total_valid_records,
                        'total_invalid_records'         => $claim->total_invalid_records,
                        'total_eligible_records'        => $summary['low_aov_eligible_records'] + $summary['high_aov_eligible_records'],
                        'total_unique_mse_count'        => $summary['low_aov_unique_mse_count'] + $summary['high_aov_unique_mse_count'],
                        'low_aov_eligible_records'      => $summary['low_aov_eligible_records'],
                        'high_aov_eligible_records'     => $summary['high_aov_eligible_records'],
                        'low_aov_unique_mse_count'      => $summary['low_aov_unique_mse_count'],
                        'high_aov_unique_mse_count'     => $summary['high_aov_unique_mse_count'],
                        'low_aov_amount'                => $summary['low_aov_amount'],
                        'high_aov_amount'               => $summary['high_aov_amount'],
                        'bpp_id'                        => $claim->bpp_id,
                        'msme_transaction_type'         => $claim->msme_transaction_type,
                        'is_bulk'                       => $claim->is_bulk,
                        'status'                        => ClaimReviewStatus::DRAFT->value,
                        'created_by'                    => $claim->created_by,
                        'is_declaration_agreed'         => true,
                        'amount'                        => $amount,
                        'created_at'                    => now(),
                        'updated_at'                    => now(),
                    ]);

                    DB::table('temporary_claim_orders')
                        ->where('claim_id', $claim->id)
                        ->orderBy('id')
                        ->chunk(500, function ($orders) use (&$claimOrdersData) {

                            foreach ($orders as $claimOrder) {
                                $claimOrdersData[] = [
                                    'id'                                    => $claimOrder->id,
                                    'claim_id'                              => $claimOrder->claim_id,
                                    'provider_id'                           => $claimOrder->provider_id,
                                    'buyer_np_name'                         => $claimOrder->buyer_np_name,
                                    'ondc_order_id'                         => $claimOrder->ondc_order_id,
                                    'domain'                                => $claimOrder->domain,
                                    'item_consolidated_category'            => $claimOrder->item_consolidated_category,
                                    'aov_grouping_type'                     => $claimOrder->aov_grouping_type,
                                    'network_transaction_id'                => $claimOrder->network_transaction_id,
                                    'seller_np_name'                        => $claimOrder->seller_np_name,
                                    'order_status'                          => $claimOrder->order_status,
                                    'order_creation_timestamp'              => $claimOrder->order_creation_timestamp,
                                    'order_completed_timestamp'             => $claimOrder->order_completed_timestamp,
                                    'invoice_number'                        => $claimOrder->invoice_number,
                                    'invoice_date'                          => $claimOrder->invoice_date,
                                    'cart_level_item_price'                 => $claimOrder->cart_level_item_price,
                                    'delivery_fee'                          => $claimOrder->delivery_fee,
                                    'total_fee'                             => $claimOrder->total_fee,
                                    'created_at'                            => now(),
                                    'updated_at'                            => now(),
                                    'team_id'                               => $claimOrder->team_id,
                                    'msme_name'                             => $claimOrder->msme_name,
                                    'msme_udyam_number'                     => $claimOrder->msme_udyam_number,
                                    'msme_classification'                   => $claimOrder->msme_classification,
                                    'msme_category'                         => $claimOrder->msme_category,
                                    'msme_transaction_type'                 => $claimOrder->msme_transaction_type,
                                ];
                            }

                            // 🔥 Insert immediately instead of storing in array
                            DB::table('claim_orders')->insert($claimOrdersData);

                            // Clear memory
                            $claimOrdersData = [];
                        });



                    $this->validRows[] = [
                        'code' => '',
                        'message' => $response['message'] ?? null,
                        'msme_name' => $claim->msme_name,
                        'team_id' => $claim->team_registration_id,
                        'udyam_number' => $claim->msme_udyam_number,
                    ];
                }
            }

            DB::table('temporary_claim_orders')
                ->whereIn('claim_id', array_column($claimOrdersData, 'claim_id'))
                ->delete();


            DB::table('temporary_claims')
                ->whereIn('id', array_column($claimData, 'id'))
                ->delete();


            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function checkMSE()
    {
        return function ($attribute, $value, $fail) {

            // ❌ TEAM not found
            if (!isset($this->teamMsmeMap[$value])) {
                $fail("The {$attribute} does not exist.");
                return;
            }

            $msme = $this->teamMsmeMap[$value];

            // ❌ TEAM inactive / expired
            if (empty($msme['is_active'])) {
                $fail("The {$attribute} is inactive or expired and not eligible.");
                return;
            }

            // ❌ Not approved for claim
            if (empty($msme['is_catalogue_claim_approved'])) {
                $fail("The {$attribute} is not eligible for demand generation incentive claim.");
                return;
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

                foreach ($rows as $index => $row) {
                    $this->invalidRows[] = [
                        'row_number' => ($index + 1) ?? null,
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
                ->reject(fn($row) => in_array($row['unique_team_registration_id_of_the_mse'], $invalidTeamIds, true))
                ->map(function ($row) {
                    unset($row['_row_number']);
                    return $row;
                })
                ->values()
                ->all();
        } else {
            $this->validRows = collect($this->validRows)
                ->map(function ($row) {
                    unset($row['_row_number']);
                    return $row;
                })
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
                        'row_number' => ($rowIndex + 1) ?? null,
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
        $this->validRows = collect($this->validRows)
            ->reject(function ($row) use ($invalidRowKeys) {
                return in_array($row['_row_number'] ?? null, $invalidRowKeys, true);
            })
            ->map(function ($row) {
                unset($row['_row_number']);
                return $row;
            })
            ->values()
            ->all();
    }

    private function preloadData(): void
    {
        // TEAM + UDYAM
       $this->teamMsmeMap = DB::table('team_msme_schemes')
            ->select('team_id', 'udyam_no', 'is_catalogue_claim_approved', DB::raw('1 as is_active'))
            ->get()
            ->groupBy('team_id')
            ->map(function ($items) {
                return [
                    'udyams' => $items->pluck('udyam_no')->toArray(),

                    'is_catalogue_claim_approved' => $items
                        ->pluck('is_catalogue_claim_approved')
                        ->contains(1),

                    'is_active' => $items
                        ->pluck('is_active')
                        ->contains(1),
                ];
            })
            ->toArray();
        // DOMAIN + CATEGORY
        $this->domainCategoryMap = DB::table('sub_domains')
            ->get()
            ->groupBy('ondc_domain_id')
            ->map(function ($items) {
                return $items->pluck('name')->flip()->toArray();
            })
            ->toArray();

        // AOV MAP
        $this->aovMap = DB::table('sub_domains')
            ->select('ondc_domain_id', 'code', 'aov_grouping_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->ondc_domain_id . '_' . $item->code => $item->aov_grouping_type
                ];
            })
            ->toArray();

        if (empty($this->msmeData)) {
            $teamIds = array_keys($this->teamMsmeMap);

            $this->msmeData = app(DemandGenerationClaimImportService::class)
                ->getMsmeDetailsBulk($teamIds)
                ->keyBy('team_id');
        }

        // EXISTING DUPLICATES
        $this->existingOrders = DB::table('claim_orders')->pluck('ondc_order_id')->toArray();
        $this->existingTransactions = DB::table('claim_orders')->pluck('network_transaction_id')->toArray();
        $this->existingInvoices = DB::table('claim_orders')->pluck('invoice_number')->toArray();
    }


    public function getClaimTypeId($slug)
    {
        return DB::table('claim_types')->where('slug', $slug)->value('id');
    }

  
}
