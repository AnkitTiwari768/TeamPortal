<?php

declare(strict_types=1);

namespace App\Web\LogisticImport;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use App\Web\Claim\ClaimService;
use App\Web\Claim\ClaimReviewStatus;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class LogisticClaimImport implements ToCollection, SkipsEmptyRows
{
    public array $validRows = [];
    public array $invalidRows = [];
    public int $totalRows = 0;

    private const CLAIM_TYPE_ID = 'f47a6dae-67f3-4331-899c-e83c19aff14a';

    private static array $excelNetworkOrderIds = [];
    private static array $excelTransactionIds = [];
    private static array $excelInvoiceNumbers = [];

    public function sheets(): array
    {
        return [
            0 => $this
        ];
    }

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
            ],
            'seller_np_name' => ['required', 'max:255'],
            'udyam_number' => ['required', 'max:50', 'exists:team_msme_schemes,udyam_no'],
            'provider_id' => ['required', 'max:200'],
            'domain' => ['required', 'exists:sub_domains,ondc_domain_id'],
            'item_consolidated_category' => ['required', $this->checkSelectedDomainForItemCategory($row, 'domain')],
            'network_order_id' => ['required', 'max:100', $this->checkNetworkOrderId()],
            'network_transaction_id' => ['required', 'max:100', $this->checkNetworkTransactionId()],
            'buyer_np_name' => ['required', 'max:200'],
            'order_status' => ['required', $this->checkOrderStatus()],
            'order_creation_timestamp' => ['required', $this->isoTimestampRule()],
            'order_completed_timestamp' => ['required', $this->isoTimestampRule()],
            'invoice_number' => ['nullable', 'max:100', $this->checkInvoiceNumber()], // Made nullable as per sample potentially having blanks
            'invoice_date' => ['nullable', $this->dateRule()],
            'cart_level_item_price' => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'delivery_fee' => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'total_fee' => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/', $this->checkTotalFee($row, 'cart_level_item_price', 'delivery_fee')],
        ];
    }

    public function collection(Collection $rows)
    {
        $this->totalRows = $rows->count();
        // Reset static arrays to handle multiple imports in same request if any (unlikely but safe)
        self::$excelNetworkOrderIds = [];
        self::$excelTransactionIds = [];
        self::$excelInvoiceNumbers = [];

        foreach ($rows as $index => $row) {
            if ($index == 0)
                continue; // Skip header

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

            $validator = Validator::make($rowData, $this->rules($rowData), [], $this->customAttributes());

            if ($validator->fails()) {
                $this->invalidRows[] = [
                    'row_number' => $index + 1, // Excel row number (1-based)
                    'data' => $rowData,
                    'errors' => $validator->errors()
                ];
                continue;
            }

            $this->validRows[] = $rowData;
        }

        if (!empty($this->validRows)) {
            $this->processValidRows();
        }
    }

    private function processValidRows()
    {
        DB::beginTransaction();

        try {
            $claimService = app(ClaimService::class);
            $groupedRows = collect($this->validRows)->groupBy('team_id');
            $currentUser = auth()->user()->username;
            $currentUserId = auth()->id();

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
                    'seller_np_name' => $firstRow['seller_np_name'],
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

            if ($temporaryClaims) {
                foreach ($temporaryClaims as $claim) {

                    // Calculate Amount: STRICTLY based on first 12 Completed orders sorted by timestamp
                    $payableOrders = DB::table('temporary_claim_orders')
                        ->where('claim_id', $claim->id)
                        ->where('order_status', 'Completed')
                        ->orderBy('order_creation_timestamp', 'asc')
                        ->limit(12)
                        ->get();

                    $amount = $payableOrders->count() * 50;

                    $claimData[] = [
                        'id' => $claim->id,
                        'claim_type_id' => $claim->claim_type_id,
                        'snp_id' => $claim->snp_id,
                        'application_number' => $claimService->generateApplicationNumber(),
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
                        'seller_np_name' => $claim->seller_np_name,
                        'ondc_seller_network_id' => $claim->ondc_seller_network_id,
                        'team_id' => $claim->team_id,
                        'created_by' => $claim->created_by,
                        'is_declaration_agreed' => true,
                        'amount' => $amount,
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

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // --- Helpers ---

    private function clean($value)
    {
        return is_string($value) ? trim($value) : $value;
    }

    private function transformDate($value): ?string
    {
        if (empty($value))
            return null;
        try {
            if (is_numeric($value)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->format('Y-m-d');
            }
            return Carbon::createFromFormat('Y-m-d', trim($value))->format('Y-m-d'); // Input is Y-m-d in sample
        } catch (\Throwable $e) {
            try {
                // Try dd-mm-yyyy just in case
                return Carbon::createFromFormat('d-m-Y', trim($value))->format('Y-m-d');
            } catch (\Throwable $ex) {
                return null;
            }
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
                if (is_numeric($value)) {
                    ExcelDate::excelToDateTimeObject($value);
                } else {
                    // Try both formats
                    try {
                        Carbon::createFromFormat('Y-m-d', $value);
                    } catch (\Exception $e) {
                        Carbon::createFromFormat('d-m-Y', $value);
                    }
                }
            } catch (\Exception $e) {
                $fail("{$attr} must be in YYYY-MM-DD or dd-mm-yyyy format.");
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
                ->where('name', $value)
                ->exists();
            if (!$exists) {
                $fail('Invalid Item Consolidated Category for the selected Domain.');
            }
        };
    }


}
