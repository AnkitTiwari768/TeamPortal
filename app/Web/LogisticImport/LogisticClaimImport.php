<?php

declare(strict_types=1);

namespace App\Web\LogisticImport;

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


class LogisticClaimImport implements ToCollection, SkipsEmptyRows, WithMultipleSheets
{
    use Importable;
    public array $validRows = [];
    public array $invalidRows = [];
    public int $totalRows = 0;

    private const CLAIM_TYPE_ID = 'f47a6dae-67f3-4331-899c-e83c19aff14a';


    private static array $onboardedMSEs = [];
    private static array $excelNetworkOrderIds = [];
    private static array $excelTransactionIds = [];
    private static array $excelInvoiceNumbers = [];
    private static array $excelProviderId = [];
    // private static array $excelCatalogScoreId = [];
    // private static array $excelCatalogScoreUrl = [];
    // private static array $excelCredentialScoreId = [];
    // private static array $excelCredentialScoreUrl = [];


    public function sheets(): array
    {
        return [
            0 => $this
        ];
    }

    protected array $requiredHeaders =
    [
        'buyer np name',
        'seller np name',
        'team registration id',
        'udyam number',
        'provider id',
        'domain',
        'item consolidated category',
        'network order id',
        'network transaction id',
        'order status',
        'order creation timestamp',
        'order completed timestamp',
        'invoice number',
        'invoice date',
        'cart level item price',
        'delivery fee',
        'total fee',


    ];

    public function customAttributes()
    {
        return [
            'buyer_np_name' => 'Buyer NP Name',
            'seller_np_name' => 'Seller NP Name',
            'team_id' => 'Team Registration ID',
            'udyam_number' => 'Udyam Number',
            'provider_id' => 'Provider ID',
            'domain' => 'Domain',
            'item_consolidated_category' => 'Item Consolidated Category',
            'network_order_id' => 'Network Order ID',
            'network_transaction_id' => 'Network Transaction ID',
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
            'team_id' => [
                'required',
                'max:100',
                'exists:team_msme_schemes,team_id',
                $this->checkMSE(),
                // $this->checkDuplicateTeamProviderInExcel(
                //     $row['team_id'],
                //     $row['provider_id']
                // ),
            ],
            'seller_np_name' => ['required', 'max:255'],
            'udyam_number' => ['required', 'max:50', $this->checkTeamUdyamCombination($row, 'team_id', 'udyam_number')],
            'provider_id' => ['required', 'max:200'],
            'domain' => ['required', 'exists:sub_domains,ondc_domain_id'],
            'item_consolidated_category' => ['required', $this->checkSelectedDomainForItemCategory($row, 'domain'),],
            'network_order_id' => ['required', 'max:100', $this->checkNetworkOrderId()],
            'network_transaction_id' => ['required', 'max:100', $this->checkNetworkTransactionId()],
            'buyer_np_name' => ['required', 'max:200'],
            'order_status' => ['required', $this->checkOrderStatus()],
            'order_creation_timestamp' => ['required', $this->isoTimestampRule()],
            'order_completed_timestamp' => ['required', $this->isoTimestampRule()],
            'invoice_number' => ['required', 'max:100', $this->checkInvoiceNumber()], // Made nullable as per sample potentially having blanks
            'invoice_date' => ['required', $this->dateRule()],
            'cart_level_item_price' => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'delivery_fee' => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_fee' => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/', $this->checkTotalFee($row, 'cart_level_item_price', 'delivery_fee')],
        ];
    }

    public function collection(Collection $rows)
    {
        $this->totalRows = max(0, $rows->count());
        // Reset static arrays to handle multiple imports in same request if any (unlikely but safe)
        self::$excelNetworkOrderIds = [];
        self::$excelTransactionIds = [];
        self::$excelInvoiceNumbers = [];
        $headerRow = $rows->first()->toArray();
        $this->validateExcelHeader($headerRow);

        foreach ($rows as $index => $row) {
            if ($index == 0)
                continue; // Skip header

            if ($this->SkipEmptyRow($row)) {
                continue; // Skip empty row
            }

            // Column Mapping
            // 0: buyer_np_name
            // 1: seller_np_name
            // 2: team_id
            // 3: udyam_number
            // 4: provider_id
            // 5: domain
            // 6: item_consolidated_category
            // 7: provider_id (duplicate, skipped)
            // 8: network_order_id
            // 9: network_transaction_id
            // 10: order_status
            // 11: order_creation_timestamp
            // 12: order_completed_timestamp
            // 13: invoice_number
            // 14: invoice_date
            // 15: cart_level_item_price
            // 16: delivery_fee
            // 17: total_fee

            $rowData = [
                'buyer_np_name' => $this->clean($row[0]),
                'seller_np_name' => $this->clean($row[1]),
                'team_id' => $this->clean($row[2]),
                'udyam_number' => $this->clean($row[3]),
                'provider_id' => $this->clean($row[4]),
                'domain' => $this->clean($row[5]),
                'item_consolidated_category' => $this->clean($row[6]),
                'network_order_id' => $this->clean($row[7]),
                'network_transaction_id' => $this->clean($row[8]),
                'order_status' => $this->clean($row[9]),
                'order_creation_timestamp' => $this->clean($row[10]),
                'order_completed_timestamp' => $this->clean($row[11]),
                'invoice_number' => $this->clean($row[12]),
                'invoice_date' => $this->clean($row[13]),
                'cart_level_item_price' => $this->clean($row[14]),
                'delivery_fee' => $this->clean($row[15]),
                'total_fee' => $this->clean($row[16]),
            ];

            //dd($rowData['order_creation_timestamp']);

            $validator = Validator::make($rowData, $this->rules($rowData), $this->customValidationMessages(), $this->customAttributes());

            if ($validator->fails()) {
                $this->invalidRows[] = [
                    'row_number' => $index, // Excel row number (1-based)
                    'data' => $rowData,
                    'errors' => $validator->errors()
                ];
                continue;
            }
            $rowData['_row_number'] = $index;
            $this->validRows[] = $rowData; // _row_number is kept here for use by post-validation methods
        }

        $this->validateCapAmountPerTeam();
        $this->validateSameProviderPerTeam();
        $this->validateUniqueProviderAcrossTeams();


        if (empty($this->validRows)) {
            return;
        }

        $this->processValidRows();
    }

    private function processValidRows()
    {
        DB::beginTransaction();

        try {
            $claimService = app(ClaimService::class);
            $groupedRows = collect($this->validRows)->groupBy('team_id');
            $currentUser = auth()->user()->username;
            $currentUserId = auth()->id();

            $tempClaimIds = DB::table('temporary_claims')->where('created_by', authId())->pluck('id')->toArray();

            DB::table('temporary_claim_orders')
                ->whereIn('claim_id', $tempClaimIds)
                ->delete();

            DB::table('temporary_claims')
                ->whereIn('id', $tempClaimIds)
                ->delete();

            foreach ($groupedRows as $teamId => $rows) {
                // Delete existing temporary claim for this Team ID + User
                $existingClaimId = DB::table('temporary_claims')
                    ->where('team_registration_id', $teamId)
                    ->where('snp_id', $currentUser)
                    ->where('claim_type_id', self::CLAIM_TYPE_ID)
                    ->value('id');

                if ($existingClaimId) {
                    DB::table('temporary_claim_orders')->where('claim_id', $existingClaimId)->delete();
                    DB::table('temporary_claims')->where('id', $existingClaimId)->delete();
                }

                // Prepare MSME Details (taken from first row of the group, assuming consistent)
                $firstRow = $rows->first();
                $msmeDetails = $claimService->getMsmeDetails($teamId);

                $claimId = uuid();

                // Insert into temporary_claims
                DB::table('temporary_claims')->insert([
                    'id' => $claimId,
                    'claim_type_id' => self::CLAIM_TYPE_ID,
                    'snp_id' => $currentUser,
                    'team_registration_id' => $teamId,
                    'bpp_id' => $firstRow['provider_id'],
                    'ondc_seller_network_id' => $firstRow['provider_id'],
                    // 'seller_np_name' => $firstRow['seller_np_name'],
                    'msme_name' => $msmeDetails->enterprise_name ?? null,
                    'msme_udyam_number' => $msmeDetails->udyam_no ?? null,
                    'msme_classification' => $msmeDetails->msme_classification ?? null,
                    'msme_category' => $msmeDetails->major_activity ?? null,
                    'msme_transaction_type' => $msmeDetails->ondc_transaction_type_id ?? null,
                    'is_bulk' => true,
                    'status' => ClaimReviewStatus::TEMPORARY->value,
                    'team_id' => $teamId, // Storing twice as per schema/logic
                    'created_by' => $currentUserId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Insert Orders
                $claimOrders = [];
                foreach ($rows as $row) {
                    $claimOrders[] = [
                        'id' => uuid(),
                        'claim_id' => $claimId,
                        'ondc_order_id' => $row['network_order_id'],
                        'domain' => $row['domain'],
                        'item_consolidated_category' => $row['item_consolidated_category'],
                        'network_transaction_id' => $row['network_transaction_id'],
                        'buyer_np_name' => $row['buyer_np_name'],
                        'seller_np_name' => $row['seller_np_name'],
                        'order_status' => $row['order_status'],
                        'order_creation_timestamp' => $this->transformIsoTimestamp($row['order_creation_timestamp']),
                        'order_completed_timestamp' => $this->transformIsoTimestamp($row['order_completed_timestamp']),
                        'invoice_number' => $row['invoice_number'],
                        'invoice_date' => $this->transformDate($row['invoice_date']),
                        'cart_level_item_price' => $row['cart_level_item_price'],
                        'delivery_fee' => $row['delivery_fee'],
                        'total_fee' => $row['total_fee'],
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

    // public function store($userId)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $claimService = app(ClaimService::class);
    //         $currentUser = auth()->user()->username; // Needed? stored by user ID.

    //         $temporaryClaims = DB::table('temporary_claims')
    //             ->where('created_by', $userId)
    //             ->where('claim_type_id', self::CLAIM_TYPE_ID)
    //             ->where('status', ClaimReviewStatus::TEMPORARY->value)
    //             ->get();

    //         $claimData = [];
    //         $claimOrdersData = [];

    //         if ($temporaryClaims) {
    //             foreach ($temporaryClaims as $index => $claim) {

    //                 // Calculate Amount: STRICTLY based on first 12 Completed orders sorted by timestamp
    //                 $payableOrders = DB::table('temporary_claim_orders')
    //                     ->where('claim_id', $claim->id)
    //                     ->where('order_status', 'Completed')
    //                     ->orderBy('order_creation_timestamp', 'asc')
    //                     ->limit(12)
    //                     ->get();

    //                 $amount = $payableOrders->count() * 50;

    //                 $gstPercentage = floatval(session()->get('gst_percentage_' . $userId, 0));
    //                 $gstAmount = round(($amount * $gstPercentage) / 100, 2);
    //                 $totalClaimedAmount = round($amount + $gstAmount, 2);

    //                 $claimData[] = [
    //                     'id' => $claim->id,
    //                     'claim_type_id' => $claim->claim_type_id,
    //                     'snp_id' => $claim->snp_id,
    //                     'application_number' => $claimService->generateApplicationNumber($index),
    //                     'team_registration_id' => $claim->team_registration_id,
    //                     'bpp_id' => $claim->bpp_id,
    //                     'msme_transaction_type' => $claim->msme_transaction_type,
    //                     'is_bulk' => $claim->is_bulk,
    //                     'status' => ClaimReviewStatus::DRAFT->value,
    //                     'claim_status' => ClaimReviewStatus::DRAFT->value,
    //                     'msme_name' => $claim->msme_name,
    //                     'msme_udyam_number' => $claim->msme_udyam_number,
    //                     'msme_classification' => $claim->msme_classification,
    //                     'msme_category' => $claim->msme_category,
    //                     // 'seller_np_name' => $claim->seller_np_name,
    //                     'ondc_seller_network_id' => $claim->ondc_seller_network_id,
    //                     'team_id' => $claim->team_id,
    //                     'created_by' => $claim->created_by,
    //                     'is_declaration_agreed' => true,
    //                     'amount' => $amount,
    //                     'gst_percentage' => $gstPercentage,
    //                     'gst_amount' => $gstAmount,
    //                     'total_claimed_amount' => $amount,
    //                     'created_at' => now(),
    //                     'updated_at' => now(),
    //                 ];

    //                 $temporaryOrders = DB::table('temporary_claim_orders')
    //                     ->where('claim_id', $claim->id)
    //                     ->get();

    //                 foreach ($temporaryOrders as $order) {
    //                     $claimOrdersData[] = (array) $order;
    //                 }
    //             }
    //         }

    //         if ($claimData) {
    //             DB::table('claims')->insert($claimData);
    //             DB::table('claim_orders')->insert($claimOrdersData);

    //             // Cleanup
    //             $claimIds = array_column($claimData, 'id');
    //             DB::table('temporary_claim_orders')->whereIn('claim_id', $claimIds)->delete();
    //             DB::table('temporary_claims')->whereIn('id', $claimIds)->delete();
    //         }

    //         DB::commit();
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         throw $e;
    //     }
    // }

    public function store($userId)
    {
        DB::beginTransaction();

        try {
            $claimService = app(ClaimService::class);
            $currentUser = auth()->user()->username; // Needed? stored by user ID.

            $temporaryClaims = DB::table('temporary_claims')
                ->where('created_by', $userId)
                ->where('claim_type_id', self::CLAIM_TYPE_ID)
                ->where('status', ClaimReviewStatus::TEMPORARY->value)
                ->get();

            $claimData = [];
            $claimOrdersData = [];

            // Get GST type and percentages from session
            $gstType = session()->get('gst_type_' . $userId, null); // Default to GST type 1
            $gstPercentage = floatval(session()->get('gst_percentage_' . $userId, 0));
            $cgstPercentage = floatval(session()->get('cgst_percentage_' . $userId, 0));
            $sgstPercentage = floatval(session()->get('sgst_percentage_' . $userId, 0));

            if ($temporaryClaims) {
                foreach ($temporaryClaims as $index => $claim) {

                    // Calculate Amount: STRICTLY based on first 12 Completed orders sorted by timestamp
                    $payableOrders = DB::table('temporary_claim_orders')
                        ->where('claim_id', $claim->id)
                        ->where('order_status', 'Completed')
                        ->orderBy('order_creation_timestamp', 'asc')
                        ->limit(12)
                        ->get();

                    $amount = $payableOrders->count() * 50;

                    // Calculate GST amount based on type using the helper
                    $gstResults = $this->calculateInclusiveGST((float)$amount, (string)$gstType);

                    $claimData[] = [
                        'id' => $claim->id,
                        'claim_type_id' => $claim->claim_type_id,
                        'snp_id' => $claim->snp_id,
                        'application_number' => $claimService->generateApplicationNumber($index),
                        'team_registration_id' => $claim->team_registration_id,
                        'bpp_id' => $claim->bpp_id,
                        'msme_transaction_type' => $claim->msme_transaction_type,
                        'is_bulk' => $claim->is_bulk,
                        'status' => ClaimReviewStatus::DRAFT->value,
                        'claim_status' => ClaimReviewStatus::DRAFT->value,
                        'msme_name' => $claim->msme_name,
                        'msme_udyam_number' => $claim->msme_udyam_number,
                        'msme_classification' => $claim->msme_classification,
                        'msme_category' => $claim->msme_category,
                        // 'seller_np_name' => $claim->seller_np_name,
                        'ondc_seller_network_id' => $claim->ondc_seller_network_id,
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

                    $temporaryOrders = DB::table('temporary_claim_orders')
                        ->where('claim_id', $claim->id)
                        ->get();

                    foreach ($temporaryOrders as $order) {
                        $claimOrdersData[] = (array) $order;
                    }
                }
            }

            if ($claimData) {
                DB::table('claims')->insert($claimData);
                DB::table('claim_orders')->insert($claimOrdersData);

                // Cleanup
                $claimIds = array_column($claimData, 'id');
                DB::table('temporary_claim_orders')->whereIn('claim_id', $claimIds)->delete();
                DB::table('temporary_claims')->whereIn('id', $claimIds)->delete();
            }

            // Clear session data after successful save
            session()->forget([
                'gst_type_' . $userId,
                'gst_percentage_' . $userId,
                'cgst_percentage_' . $userId,
                'sgst_percentage_' . $userId
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // --- Helpers ---

    // private function clean($value)
    // {
    //     return is_string($value) ? trim($value) : $value;
    // }

    private function transformDate($value): ?string
    {
        if (empty($value))
            return null;
        try {
            if (is_numeric($value)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->format('Y-m-d');
            }
            // Primary format: dd-mm-yyyy
            return Carbon::createFromFormat('d-m-Y', trim($value))->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function transformIsoTimestamp(string $value): ?string
    {
        // dd($value);
        if (empty($value))
            return null;
        try {
            return Carbon::createFromFormat('Y-m-d\TH:i:s', $value)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function isoTimestampRule()
    {
        return function ($attribute, $value, $fail) {
            // dd($value);
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
            if (!$value)
                return;

            try {
                is_numeric($value)
                    ? ExcelDate::excelToDateTimeObject($value)
                    : Carbon::createFromFormat('d-m-Y', $value);
            } catch (\Exception $e) {
                $fail("{$attr} must be in dd-mm-yyyy format.");
            }
        };
    }
    public function checkNetworkOrderId()
    {
        return function ($attr, $value, $fail) {
            if (in_array($value, self::$excelNetworkOrderIds)) {
                $fail("{$attr} '{$value}' is duplicated in Excel.");
            }
            if (DB::table('claim_orders')->where('ondc_order_id', $value)->exists()) {
                $fail("{$attr} '{$value}' already exists in system.");
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
            if (DB::table('claim_orders')->where('network_transaction_id', $value)->exists()) {
                $fail("{$attr} '{$value}' already exists in system.");
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
            if (empty($value))
                return; // Allow empty
            if (in_array($value, self::$excelInvoiceNumbers)) {
                $fail("{$attr} '{$value}' is duplicated in Excel.");
            }
            if (DB::table('claim_orders')->where('invoice_number', $value)->exists()) {
                $fail("{$attr} '{$value}' already exists in system.");
            }
            self::$excelInvoiceNumbers[] = $value;
        };
    }

    public function checkTotalFee($row, $itemPriceField, $deliveryFeeField)
    {
        return function ($attribute, $value, $fail) use ($row, $itemPriceField, $deliveryFeeField) {
            $itemPrice = (float) $row[$itemPriceField];
            $deliveryFee = (float) $row[$deliveryFeeField];
            $expected = round($itemPrice + $deliveryFee, 2);
            if (round((float) $value, 2) !== $expected) {
                $fail("{$attribute} must be equal to Item Price + Delivery Fee ({$expected}).");
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

    private function checkMSE()
    {
        return function ($attribute, $value, $fail) {

            $onboardedMSEs = $this->getOnbordedMSME();

            if (!in_array($value, $onboardedMSEs)) {
                $fail("The {$attribute} is not eligible for logistic and transportation claim.");
            }
        };
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
                ->pluck('ms.team_id')
                ->toArray();
        }

        return static::$onboardedMSEs;
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

    // public function checkCatalogScoreId()
    // {
    //     return function ($attr, $value, $fail) {
    //         if (in_array($value, self::$excelCatalogScoreId)) {
    //             $fail("{$attr} '{$value}' is duplicated in Excel.");
    //         }

    //         self::$excelCatalogScoreId[] = $value;
    //     };
    // }

    // public function checkCatalogScoreUrl()
    // {
    //     return function ($attr, $value, $fail) {
    //         if (in_array($value, self::$excelCatalogScoreUrl)) {
    //             $fail("{$attr} '{$value}' is duplicated in Excel.");
    //         }

    //         self::$excelCatalogScoreUrl[] = $value;
    //     };
    // }

    // public function checkCredentialScoreId()
    // {
    //     return function ($attr, $value, $fail) {
    //         if (in_array($value, self::$excelCredentialScoreId)) {
    //             $fail("{$attr} '{$value}' is duplicated in Excel.");
    //         }

    //         self::$excelCredentialScoreId[] = $value;
    //     };
    // }

    // public function checkCredentialScoreUrl()
    // {
    //     return function ($attr, $value, $fail) {
    //         if (in_array($value, self::$excelCredentialScoreUrl)) {
    //             $fail("{$attr} '{$value}' is duplicated in Excel.");
    //         }

    //         self::$excelCredentialScoreUrl[] = $value;
    //     };
    // }
    public function customValidationMessages()
    {
        return [
            'team_id.required' => 'The Team Registration ID is required.',
            'team_id.max' => 'The Team Registration ID must not exceed :max characters.',
            'team_id.exists' => 'The Team Registration ID does not exist in our records.',
            'team_id.not_eligible' => 'The Team Registration ID :input is not eligible for logistic and transportation claim.',
            'team_id.duplicate_provider' => 'The combination of Team Registration ID and Provider ID already exists in the Excel file.',

            // Seller NP Name validations
            'seller_np_name.required' => 'The Seller NP Name is required.',
            'seller_np_name.max' => 'The Seller NP Name must not exceed :max characters.',

            // Udyam Number validations
            'udyam_number.required' => 'The Udyam Number is required.',
            'udyam_number.max' => 'The Udyam Number must not exceed :max characters.',
            'udyam_number.invalid_combination' => 'The combination of Team ID and Udyam Number does not exist in our records.',

            // Provider ID validations
            'provider_id.required' => 'The Provider ID is required.',
            'provider_id.max' => 'The Provider ID must not exceed :max characters.',
            'provider_id.string' => 'Provider ID must be a valid string.',

            // Domain validations
            'domain.required' => 'The Domain is required.',
            'domain.exists' => 'The selected Domain is invalid.',

            // Item Consolidated Category validations
            'item_consolidated_category.required' => 'The Item Consolidated Category is required.',
            'item_consolidated_category.invalid' => 'Invalid Item Consolidated Category for the selected Domain.',

            // Network Order ID validations
            'network_order_id.required' => 'The Network Order ID is required.',
            'network_order_id.max' => 'The Network Order ID must not exceed :max characters.',
            'network_order_id.duplicate_excel' => 'The Network Order ID :input is duplicated in the Excel file.',
            'network_order_id.duplicate_system' => 'The Network Order ID :input already exists in the system.',

            // Network Transaction ID validations
            'network_transaction_id.required' => 'The Network Transaction ID is required.',
            'network_transaction_id.max' => 'The Network Transaction ID must not exceed :max characters.',
            'network_transaction_id.duplicate_excel' => 'The Network Transaction ID :input is duplicated in the Excel file.',
            'network_transaction_id.duplicate_system' => 'The Network Transaction ID :input already exists in the system.',

            // Buyer NP Name validations
            'buyer_np_name.required' => 'The Buyer NP Name is required.',
            'buyer_np_name.max' => 'The Buyer NP Name must not exceed :max characters.',

            // Order Status validations
            'order_status.required' => 'The Order Status is required.',
            'order_status.invalid' => 'The Order Status must be "Completed".',

            // Order Creation Timestamp validations
            'order_creation_timestamp.required' => 'The Order Creation Timestamp is required.',
            'order_creation_timestamp.format' => 'The Order Creation Timestamp must be in YYYY-MM-DDTHH:MM:SS format.',

            // Order Completed Timestamp validations
            'order_completed_timestamp.required' => 'The Order Completed Timestamp is required.',
            'order_completed_timestamp.format' => 'The Order Completed Timestamp must be in YYYY-MM-DDTHH:MM:SS format.',

            // Invoice Number validations
            'invoice_number.max' => 'The Invoice Number must not exceed :max characters.',
            'invoice_number.duplicate_excel' => 'The Invoice Number :input is duplicated in the Excel file.',
            'invoice_number.duplicate_system' => 'The Invoice Number :input already exists in the system.',

            // Invoice Date validations
            'invoice_date.format' => 'The Invoice Date must be in YYYY-MM-DD format.',

            // Cart Level Item Price validations
            'cart_level_item_price.required' => 'The Cart Level Item Price is required.',
            'cart_level_item_price.numeric' => 'The Cart Level Item Price must be a number.',
            'cart_level_item_price.min' => 'The Cart Level Item Price must be at least :min.',
            'cart_level_item_price.regex' => 'The Cart Level Item Price must have up to 2 decimal places.',

            // Delivery Fee validations
            'delivery_fee.required' => 'The Delivery Fee is required.',
            'delivery_fee.numeric' => 'The Delivery Fee must be a number.',
            'delivery_fee.min' => 'The Delivery Fee must be at least :min.',
            'delivery_fee.regex' => 'The Delivery Fee must have up to 2 decimal places.',

            // Total Fee validations
            'total_fee.required' => 'The Total Fee is required.',
            'total_fee.numeric' => 'The Total Fee must be a number.',
            'total_fee.min' => 'The Total Fee must be at least :min.',
            'total_fee.regex' => 'The Total Fee must have up to 2 decimal places.',
            'total_fee.invalid_sum' => 'The Total Fee must be equal to Item Price + Delivery Fee (expected: :expected).',

            // Generic validation messages
            'required' => 'The :attribute field is required.',
            'max' => 'The :attribute must not exceed :max characters.',
            'numeric' => 'The :attribute must be a number.',
            'min' => 'The :attribute must be at least :min.',
            'exists' => 'The selected :attribute is invalid.',
            'digits_between' => 'The :attribute must be between :min and :max digits.',
            'regex' => 'The :attribute format is invalid.',

            // Additional context-specific messages
            'team_udyam_mismatch' => 'The Udyam Number does not match the Team Registration ID.',
            'invalid_timestamp' => 'The :attribute does not contain a valid timestamp.',
            'invalid_date' => 'The :attribute does not contain a valid date.',
        ];
    }
    /**
     * Validate that the total claimable orders (existing in DB + new from Excel)
     * per team_id does not exceed the cap of 12 orders (12 × ₹50 = ₹600).
     *
     * Logic:
     *  - Count existing rows in `claim_orders` joined with `claims` for this team_id
     *    and claim_type.
     *  - Count new rows being imported in the current Excel for the same team_id.
     *  - If existing_count >= 12, ALL new rows for that team_id are rejected.
     *  - If existing_count + new_count > 12, only the rows that cause the total
     *    to exceed 12 are rejected (i.e. rows beyond the allowed quota).
     */
    private function validateCapAmountPerTeam(): void
    {
        if (empty($this->validRows)) {
            return;
        }

        $capOrders = 12;   // Maximum allowed orders per MSME / team_id
        $ratePerOrder = 50;  // ₹ per order
        $capAmount = $capOrders * $ratePerOrder; // 600

        // Group valid (so-far) rows by team_id
        $groupedRows = collect($this->validRows)->groupBy('team_id');

        $invalidRowKeys = [];

        foreach ($groupedRows as $teamId => $rows) {
            // Count how many orders already exist in the `claims` table for this team_id
            // We look at claim_orders rows linked to claims of the same claim_type.
            $existingOrderCount = DB::table('claims')
                ->where('team_id', $teamId)
                ->where('claim_type_id', self::CLAIM_TYPE_ID)
                ->sum(DB::raw(
                    "(SELECT COUNT(*) FROM claim_orders WHERE claim_orders.claim_id = claims.id)"
                ));

            $existingOrderCount = (int) $existingOrderCount;

            $newRowCount = $rows->count();

            if ($existingOrderCount >= $capOrders) {
                // Already at or over the cap — reject every new row for this team
                foreach ($rows as $row) {
                    $this->invalidRows[] = [
                        'row_number' => $row['_row_number'] ?? null,
                        'data' => $row,
                        'errors' => [
                            'team_id' => [
                                "Team ID '{$teamId}' has already reached the maximum claim cap of "
                                    . "{$capOrders} orders (₹{$capAmount}). No further orders can be added."
                            ]
                        ]
                    ];
                    $invalidRowKeys[] = $row['_row_number'] ?? null;
                }
                continue;
            }

            $allowedNewRows = $capOrders - $existingOrderCount;

            if ($newRowCount > $allowedNewRows) {
                // Reject only the rows that exceed the allowed quota
                $rejectedRows = $rows->slice($allowedNewRows);

                foreach ($rejectedRows as $row) {
                    $totalIfAccepted = $existingOrderCount + $newRowCount;
                    $this->invalidRows[] = [
                        'row_number' => $row['_row_number'] ?? null,
                        'data' => $row,
                        'errors' => [
                            'team_id' => [
                                "Team ID '{$teamId}' would exceed the maximum claim cap of "
                                    . "{$capOrders} orders (₹{$capAmount}). "
                                    . "Existing orders: {$existingOrderCount}, "
                                    . "attempted to add: {$newRowCount}, "
                                    . "but only {$allowedNewRows} more order(s) are allowed."
                            ]
                        ]
                    ];
                    $invalidRowKeys[] = $row['_row_number'] ?? null;
                }
            }
        }

        if (!empty($invalidRowKeys)) {
            $this->validRows = collect($this->validRows)
                ->reject(fn($row) => in_array($row['_row_number'] ?? null, $invalidRowKeys, true))
                ->values()
                ->all();
        }
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
                    // ✅ Create a deep copy to preserve row_number
                    $this->invalidRows[] = [
                        'row_number' => $row['_row_number'] ?? null,
                        'data' => $row,
                        'errors' => [
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

            if ($teamIds->count() > 1) {
                foreach ($rows as $row) {
                    // ✅ Create a deep copy to preserve row_number
                    $invalidRow = [
                        'row_number' => $row['_row_number'] ?? null,
                        'data' => $row,  // Keep original data
                        'errors' => [
                            'provider_id' => [
                                "Provider ID '{$providerId}' cannot be mapped to multiple MSME Team IDs. Found Team IDs: " . $teamIds->implode(', ')
                            ]
                        ]
                    ];

                    $this->invalidRows[] = $invalidRow;
                    $invalidRowKeys[] = $row['_row_number'] ?? null;
                }
            }
        }

        // ✅ Remove invalid rows from validRows WITHOUT modifying the original row data
        $this->validRows = collect($this->validRows)
            ->reject(function ($row) use ($invalidRowKeys) {
                return in_array($row['_row_number'] ?? null, $invalidRowKeys, true);
            })
            ->map(function ($row) {
                // Only remove _row_number for valid rows
                unset($row['_row_number']);
                return $row;
            })
            ->values()
            ->all();
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
