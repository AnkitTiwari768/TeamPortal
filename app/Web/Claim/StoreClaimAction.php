<?php

namespace App\Web\Claim;

use App\Traits\HasCreateSubject;
use App\Web\Claim\ClaimDto;
use App\Web\Claim\Claim;
use App\Utils\UuidGenerator;
use App\Web\ApplicationWorkflow\ApplicationWorkflowService;
use App\Web\ApplicationWorkflow\WorkflowType;
use App\Web\Timeline\TimelineService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Domain\Batch\BatchStatus;
use App\Utils\Calendar;

class StoreClaimAction
{
    use HasCreateSubject;

    public function __construct(
        private ApplicationWorkflowService $workflowService,
        private ClaimService $claimService
    ) {}


    public function calculateIncentiveTax($number_of_skus, $msme_classification, $msme_category, $target_audience)
    {

        /*// Step 1: Define allowed classifications and categories
        $classifications = ['micro', 'small'];
        $categories      = ['manufacturing', 'services'];
		$businessTypes=['business to consumers (b2c)', 'business to business (b2b)'];

        // Step 2: Check eligibility
        if (in_array($msme_classification, $classifications) &&
            in_array($msme_category, $categories) &&
            in_array($target_audience, $businessTypes)
        ) {
            // Step 3: Calculate total incentive
			
            $taxData=getTotalTax(); // 18% GST + 5% other charges => 1 + 0.23
            $totalAmount = $this->calculateIncentive($number_of_skus, $taxData['totalTax']);
			
			$gst_charge_amount=($totalAmount*$taxData['gst_charge'])/100;
			$other_charge_amount=($totalAmount*$taxData['other_charge'])/100;
			
			return [
				'gst_charge' => $taxData['gst_charge'],
				'gst_charge_amount' => $gst_charge_amount,
				'other_charge' => $taxData['other_charge'],
				'other_charge_amount' => $other_charge_amount,
				'totalAmount'=>$totalAmount
			];
        }*/


        // Step 1: Define allowed classifications and categories
        $classifications = ['micro', 'small'];
        $categories      = ['manufacturing', 'services'];
        $businessTypes = ['business to consumers (b2c)', 'business to business (b2b)'];

        // Step 2: Check eligibility
        if (
            in_array($msme_classification, $classifications) &&
            in_array($msme_category, $categories) &&
            in_array($target_audience, $businessTypes)
        ) {
            // Step 3: Calculate total incentive

            $taxData = getTotalTax(); // 18% GST + 5% other charges => 1 + 0.23
            $totalAmount = $this->calculateIncentive($number_of_skus, $taxData['totalTax']);

            $gst_charge_amount = ($totalAmount * $taxData['gst_charge']) / 100;

            return [
                'gst_charge' => $taxData['gst_charge'],
                'gst_charge_amount' => $gst_charge_amount,
                'totalAmount' => $totalAmount
            ];
        }
    }


    public function calculateIncentive($skuCount, $totalTax)
    {
        // Step 1: Base incentive (₹50 per SKU, max 50 SKUs)
        $base = min($skuCount, 50) * 50;

        // Step 2: Apply tax/charges
        $finalBeforeCap = $base * $totalTax;

        // Step 3: Cap at ₹2,500
        $incentive = min($finalBeforeCap, 2500);

        return $incentive;
    }


    // public function calculateIncentiveTaxAccount($invoiceValue, $transactionType, $targetAudience, $numTransactions = 0)
    // {
    //     $taxData = getTotalTax();
    //     $GST_RATE = config('constant.GST_RATE', 18) / 100; // 0.18
    //     $B2C_RATE = config('constant.B2C_RATE', 5); // 5%
    //     $B2B_PER_TXN = config('constant.B2B_PER_TXN', 250); // 250 per transaction
    //     $MAX_INCENTIVE = config('constant.MAX_INCENTIVE', 5000); // 5000 max cap

    //     $baseIncentive = 0;
    //     $cappedIncentive = 0;
    //     $gstChargeAmount = 0;
    //     $totalAmount = 0;

    //     $targetAudience = strtolower(trim($targetAudience));

    //     if ($targetAudience === 'business to consumers (b2c)') {
    //         $baseIncentive = $invoiceValue * ($B2C_RATE / 100);
    //     } elseif ($targetAudience === 'business to business (b2b)') {
    //         $baseIncentive = $numTransactions * $B2B_PER_TXN;
    //     }

    //     $baseIncentive = round($baseIncentive);

    //     if ($baseIncentive > $MAX_INCENTIVE) {
    //         $cappedIncentive = $MAX_INCENTIVE;
    //     } else {
    //         $cappedIncentive = $baseIncentive;
    //     }

    //     $gstChargeAmount = round($cappedIncentive * $GST_RATE);

    //     $totalAmount = round($cappedIncentive);

    //     return [
    //         'gst_charge' => $GST_RATE * 100,
    //         'gst_charge_amount' => $gstChargeAmount,
    //         'totalAmount' => $totalAmount,
    //     ];
    // }


    // public function calculateIncentiveTaxLogistics($numberOfOrders, $targetAudience)
    // {
    //     $taxData = getTotalTax();
    //     $GST_RATE = config('constant.GST_RATE', 18) / 100; // 0.18
    //     $B2C_RATE_PER_ORDER = config('constant.B2C_LOGISTICS_RATE_PER_ORDER', 50);
    //     $B2B_RATE_PER_ORDER = config('constant.B2B_LOGISTICS_RATE_PER_ORDER', 200);
    //     $LOGISTICS_B2C_MAX_INCENTIVE = config('constant.LOGISTICS_B2C_MAX_INCENTIVE', 2000);
    //     $LOGISTICS_B2B_MAX_INCENTIVE = config('constant.LOGISTICS_B2B_MAX_INCENTIVE', 500);

    //     $baseIncentive = 0;
    //     $cappedIncentive = 0;
    //     $gstChargeAmount = 0;
    //     $totalAmount = 0;

    //     $targetAudience = strtolower(trim($targetAudience));

    //     if ($targetAudience === 'business to consumers (b2c)') {

    //         $baseIncentive = $numberOfOrders * $B2C_RATE_PER_ORDER;

    //         if ($numberOfOrders > 10) {
    //             if ($baseIncentive > $LOGISTICS_B2C_MAX_INCENTIVE) {
    //                 $cappedIncentive = $LOGISTICS_B2C_MAX_INCENTIVE;
    //             } else {
    //                 $cappedIncentive = $baseIncentive;
    //             }
    //         } else {
    //             $cappedIncentive = $baseIncentive;
    //         }

    //     } elseif ($targetAudience === 'business to business (b2b)') {

    //         $baseIncentive = $numberOfOrders * $B2B_RATE_PER_ORDER;

    //         if ($numberOfOrders > 10) {
    //             if ($baseIncentive > $LOGISTICS_B2B_MAX_INCENTIVE) {
    //                 $cappedIncentive = $LOGISTICS_B2B_MAX_INCENTIVE;
    //             } else {
    //                 $cappedIncentive = $baseIncentive;
    //             }
    //         } else {
    //             $cappedIncentive = $baseIncentive;
    //         }
    //     }

    //     $gstChargeAmount = round($cappedIncentive * $GST_RATE);
    //     $totalAmount = round($cappedIncentive);

    //     return [
    //         'gst_charge' => $GST_RATE * 100,
    //         'gst_charge_amount' => $gstChargeAmount,
    //         'totalAmount' => $totalAmount,
    //     ];
    // }




    private function getSubsidyAmount($designCost)
    {
        $designCost = (float) $designCost;

        $subsidy = $designCost * 0.2;
        // Max subsidy is 2000
        if ($subsidy > 2000) {
            $subsidy = 2000;
        }
        return round($subsidy, 2);
    }


    public function processCatalogueCreationClaim($dto): array
    {

        $txnIds = [];
        $invoiceNumbers = [];

        if (is_array($dto->order_details)) {
            foreach ($dto->order_details as $row) {

                if (!empty($row['network_transaction_id'])) {
                    $txnIds[] = $row['network_transaction_id'];
                }

                if (!empty($row['order_invoice_number'])) {
                    $invoiceNumbers[] = $row['order_invoice_number'];
                }
            }
        }

        $txnIds = array_values(array_unique($txnIds));
        $invoiceNumbers = array_values(array_unique($invoiceNumbers));
        $txnCount = count($txnIds);
        if ($txnCount !== 2) {

            if ($txnCount < 2) {
                $message = 'Please enter at least 2 unique network transactions.';
                $code    = 'MIN_TXN_REQUIRED';
            } else {
                $message = 'You can enter only 2 unique network transactions.';
                $code    = 'MAX_TXN_LIMIT';
            }

            return [
                'success' => false,
                'code'    => $code,
                'message' => $message,
                'errors'  => [
                    'order_details' => [
                        $message
                    ]
                ]
            ];
        }

        /*if (!empty($txnIds) || !empty($invoiceNumbers)) {

            $alreadyExists = DB::table('claim_orders as co')
                ->join('claims as c', 'c.id', '=', 'co.claim_id')
                ->join('team_msme_schemes as msme', 'msme.team_id', '=', 'c.team_registration_id')
                ->join('claim_types as ct', 'ct.id', '=', 'c.claim_type_id')
                ->where('msme.id', $dto->msme_id)
                ->where('ct.slug', $dto->claim_type_id)
                ->where('c.claim_status', BatchStatus::PAYMENT_COMPLETED->value)
                ->where(function ($q) use ($txnIds, $invoiceNumbers) {

                    if (!empty($txnIds)) {
                        $q->whereIn('co.network_transaction_id', $txnIds);
                    }

                    if (!empty($invoiceNumbers)) {
                        $q->orWhereIn('co.order_invoice_number', $invoiceNumbers);
                    }
                })
                ->exists();

            if ($alreadyExists) {
                return [
                    'success' => false,
                    'code'    => 'DUPLICATE_TRANSACTION_OR_INVOICE',
                    'message' => 'One or more network transaction IDs or invoice numbers are already used.',
                    'data'    => []
                ];
            }
        }*/

        $errors   = [];
        $messages = [];

        foreach (($dto->order_details ?? []) as $rowId => $row) {

            if (!empty($row['network_transaction_id'])) {

                $txnExists = DB::table('claim_orders')
                    ->where('network_transaction_id', $row['network_transaction_id'])
                    ->exists();

                if ($txnExists) {
                    $msg = 'This Network Transaction ID already exists.';

                    $errors["order_details.$rowId.network_transaction_id"][] = $msg;
                    $messages[] = $msg;
                }
            }

            if (!empty($row['order_invoice_number'])) {

                $invoiceExists = DB::table('claim_orders')
                    ->where('order_invoice_number', $row['order_invoice_number'])
                    ->exists();

                if ($invoiceExists) {
                    $msg = 'This Invoice Number already exists.';

                    $errors["order_details.$rowId.order_invoice_number"][] = $msg;
                    $messages[] = $msg;
                }
            }
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'code'    => 'DUPLICATE_TRANSACTION_OR_INVOICE',
                'message' => $messages[0],
                'errors'  => $errors,
            ];
        }



        $categoryAovTypes = DB::table('team_msme_schemes as msme')
            ->leftJoin('sub_domains as sd', function ($join) {
                $join->whereRaw(
                    "JSON_CONTAINS(msme.product_category_id, JSON_QUOTE(sd.id))"
                );
            })
            ->where('msme.id', $dto->msme_id)
            ->pluck('sd.aov_grouping_type')
            ->toArray();

        if (empty($categoryAovTypes)) {
            return [
                'success' => false,
                'code'    => 'AOV_NOT_FOUND',
                'message' => 'AOV type not found MSME.',
                'errors' => [
                    '__business__' => [
                        'AOV category not found for MSME.'
                    ]
                ]
            ];
        }

        $msmeAovTypes = array_values(array_unique($categoryAovTypes));

        if (count($msmeAovTypes) > 1) {
            $finalAovType = in_array('High AOV', $msmeAovTypes, true)
                ? 'High AOV'
                : 'Low AOV';
        } else {
            $finalAovType = $msmeAovTypes[0];
        }


        $productAmount = DB::table('team_msme_schemes as msme')
            ->leftJoin('sub_domains as sd', function ($join) {
                $join->whereRaw(
                    "JSON_CONTAINS(msme.product_category_id, JSON_QUOTE(sd.id))"
                );
            })
            ->leftJoin(
                'catalogue_aov_categories as cac',
                'cac.sub_domain_id',
                '=',
                'sd.id'
            )
            ->where('msme.id', $dto->msme_id)
            ->where('sd.aov_grouping_type', $finalAovType)
            ->select('cac.rate as amount')
            ->first();

        if (!$productAmount || empty($productAmount->amount)) {
            return [
                'success' => false,
                'code'    => 'CATALOGUE_AMOUNT_NOT_CONFIGURED',
                'message' => 'Catalogue incentive is not configured for this MSME.',
                'data'    => [
                    'aov_type' => $finalAovType
                ],
                'errors' => [
                    '__business__' => [
                        'Catalogue incentive is not configured for this MSME.'
                    ]
                ]
            ];
        }


        return [
            'success' => true,
            'code'    => 'CATALOGUE_INCENTIVE_CALCULATED',
            'message' => '',
            'data'    => [
                'amount'   => (float) $productAmount->amount,
                'aov_type' => $finalAovType,
                'txn_ids'  => $txnIds
            ]
        ];
    }



    public function calculateAccountIncentiveAmount(string $team_registration_id, string $msme_udyam_number, string $msmeId, string $claimTypeId, array $orderDetails, ?string $bppId = null): array
    {

        $transactionCount = count($orderDetails);

        if ($transactionCount === 0) {
            return [
                'success' => false,
                'code'    => 'NO_TRANSACTIONS',
                'message' => 'No valid transactions found for incentive.',
                'data'    => [],
                'errors' => [
                    '__business__' => [
                        'No valid transactions found for incentive.'
                    ]
                ]
            ];
        }

        /*$txnIds = collect($orderDetails)->pluck('network_transaction_id')->filter()->unique()->values()->toArray();
        $invoiceNumbers  = collect($orderDetails )->pluck('order_invoice_number')->filter()->unique()->values()->toArray();
    
        if (!empty($txnIds) || !empty($invoiceNumbers)) {

            $duplicateExists = DB::table('claim_orders')
                ->where(function ($q) use ($txnIds, $invoiceNumbers) {

                    if (!empty($txnIds)) {
                        $q->whereIn('network_transaction_id', $txnIds);
                    }

                    if (!empty($invoiceNumbers)) {
                        $q->orWhereIn('order_invoice_number', $invoiceNumbers);
                    }
                })
                ->exists();

            if ($duplicateExists) {
                return [
                    'success' => false,
                    'code'    => 'DUPLICATE_TRANSACTION_OR_INVOICE',
                    'message' => 'The transaction ID or invoice number already exists.',
                    'data'    => []
                ];
            }
        }*/

        $errors = [];
        $messages = [];

        foreach ($orderDetails as $index => $row) {

            if (!empty($row['network_transaction_id'])) {

                $txnExists = DB::table('claim_orders')
                    ->where('network_transaction_id', $row['network_transaction_id'])
                    ->exists();

                if ($txnExists) {
                    $msg = 'This Network Transaction ID already exists.';

                    $errors["order_details.$index.network_transaction_id"][] = $msg;
                    $messages[] = $msg;
                }
            }

            if (!empty($row['order_invoice_number'])) {

                $invoiceExists = DB::table('claim_orders')
                    ->where('order_invoice_number', $row['order_invoice_number'])
                    ->exists();

                if ($invoiceExists) {
                    $msg = 'This Invoice Number already exists.';

                    $errors["order_details.$index.order_invoice_number"][] = $msg;
                    $messages[] = $msg;
                }
            }
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'code'    => 'DUPLICATE_TRANSACTION_OR_INVOICE',
                'message' => $messages[0],
                'errors'  => $errors
            ];
        }



        $approvedClaims = DB::table('claims as c')
            ->join('team_msme_schemes as msme', 'msme.team_id', '=', 'c.team_registration_id')
            ->join('claim_types as ct', 'ct.id', '=', 'c.claim_type_id')
            ->where('msme.id', $msmeId)
            ->where('ct.slug', $claimTypeId)
            ->where('c.claim_status', BatchStatus::PAYMENT_COMPLETED->value)
            ->orderBy('c.updated_at')
            ->select('c.*')
            ->get();

        $alreadyPaid = (float) $approvedClaims->sum('amount');
        $cycleStartDate = $approvedClaims->first()?->updated_at;


        $now = now();

        /*$lastClaim = DB::table('claims as c')
        ->join('team_msme_schemes as msme', 'msme.team_id', '=', 'c.team_registration_id')
        ->join('claim_types as ct', 'ct.id', '=', 'c.claim_type_id')
        ->where('msme.id', $msmeId)
        ->where('ct.slug', $claimTypeId)
        ->where('c.created_by', authId())
        ->orderByDesc('c.created_at')
        ->select('c.created_at')
        ->first();


        if ($lastClaim) {

            $lastClaimDate = Carbon::parse($lastClaim->created_at);

            $windowStart = $lastClaimDate->copy()->addMonthNoOverflow()->startOfMonth()->day(1);

            $windowEnd = $windowStart->copy()->day(10);

            if ($now->greaterThan($windowEnd)) {
                $windowStart = $now->copy()->startOfMonth()->day(1);
                $windowEnd   = $windowStart->copy()->day(10);
            }

            if (!$now->between($windowStart, $windowEnd)) {

                $message = sprintf(
                    'You can submit the next claim between %s and %s.',
                    $windowStart->format('jS F Y'),
                    $windowEnd->format('jS F Y')
                );

                return [
                    'success' => false,
                    'code'    => 'CLAIM_WINDOW_CLOSED',
                    'message' => $message,
                    'errors'  => [
                        '__business__' => [$message]
                    ]
                ];
            }
        }*/

        $monthlyClaimExists = DB::table('claims as c')
            ->join('team_msme_schemes as msme', 'msme.team_id', '=', 'c.team_registration_id')
            ->join('claim_types as ct', 'ct.id', '=', 'c.claim_type_id')
            ->where('msme.id', $msmeId)
            ->where('ct.slug', $claimTypeId)
            ->whereMonth('c.created_at', $now->month)
            ->whereYear('c.created_at', $now->year)
            ->where('c.created_by', authId())
            ->exists();

        if ($monthlyClaimExists) {
            return [
                'success' => false,
                'code'    => 'MONTHLY_CLAIM_ALREADY_SUBMITTED',
                'message' => 'You have already submitted a claim for this month. You can submit the next claim in the next month.',
                'data'    => [
                    'current_month' => $now->format('F Y'),
                ],
                'errors'  => [
                    '__business__' => [
                        'You have already submitted a claim for this month.'
                    ]
                ]
            ];
        }


        $categoryAovTypes = DB::table('team_msme_schemes as msme')
            ->leftJoin('sub_domains as sd', function ($join) {
                $join->whereRaw(
                    "JSON_CONTAINS(msme.product_category_id, JSON_QUOTE(sd.id))"
                );
            })
            ->where('msme.id', $msmeId)
            ->pluck('sd.aov_grouping_type')
            ->toArray();

        if (empty($categoryAovTypes)) {
            return [
                'success' => false,
                'code'    => 'AOV_NOT_FOUND',
                'message' => 'AOV category not found for MSME.',
                'data'    => []
            ];
        }

        $msmeAovTypes = array_unique($categoryAovTypes);
        $finalAovType = count($msmeAovTypes) > 1 && in_array('High AOV', $msmeAovTypes)
            ? 'High AOV' : $msmeAovTypes[array_key_first($msmeAovTypes)];

        $rules = DB::table('claim_incentive_validations_rules')
            ->where('claim_type', $claimTypeId)
            ->where('aov_grouping_type', $finalAovType)
            ->where('status', 1)
            ->orderBy('min_transactions')
            ->get();

        if ($rules->isEmpty()) {
            return [
                'success' => false,
                'code'    => 'RULES_NOT_FOUND',
                'message' => 'Incentive rules are not configured.',
                'data'    => []
            ];
        }


        $matchedRule = null;

        foreach ($rules as $i => $rule) {
            $min = (int) $rule->min_transactions;
            $nextMin = $rules[$i + 1]->min_transactions ?? null;

            if ($nextMin === null && $transactionCount >= $min) {
                $matchedRule = $rule;
                break;
            }

            if ($transactionCount >= $min && $transactionCount < $nextMin) {
                $matchedRule = $rule;
                break;
            }
        }

        /*if (!$matchedRule) {
            return [
                'success' => false,
                'code'    => 'NOT_ELIGIBLE',
                'message' => 'Minimum transaction requirement not met.',
                'errors'  => [
                     '__business__' => [
                        'Minimum transaction requirement not met.'
                    ]
                ]
            ];
        }*/

        if (!$matchedRule) {
            $firstRule = $rules->first();
            $requiredMin = (int) $firstRule->min_transactions;

            $message = sprintf(
                'You have added %d transaction%s. To be eligible, please add at least %d transaction%s.',
                $transactionCount,
                $transactionCount === 1 ? '' : 's',
                $requiredMin,
                $requiredMin === 1 ? '' : 's'
            );

            return [
                'success' => false,
                'code'    => 'NOT_ELIGIBLE',
                'message' => $message,
                'errors'  => [
                    '__business__' => [
                        $message
                    ]
                ]
            ];
        }



        $capAmount    = (float) $matchedRule->incentive_threshold_amount;
        $cycleDays    = (int)   $matchedRule->max_days;
        $bonusDays    = (int)   $matchedRule->bonus_days;
        $bonusPercent = (float) $matchedRule->bonus_percent;

        if ($cycleStartDate) {
            $daysPassed = Carbon::parse($cycleStartDate)->diffInDays(now());

            if ($alreadyPaid >= $capAmount && $daysPassed < $cycleDays) {
                return [
                    'success' => false,
                    'code'    => 'CAP_EXHAUSTED',
                    'message' => 'Maximum incentive limit already exhausted.',
                    'data'    => [
                        'next_eligible_after_days' => $cycleDays - $daysPassed
                    ],
                    'errors' => [
                        '__business__' => [
                            'Maximum incentive limit already exhausted.'
                        ]
                    ]
                ];
            }

            if ($daysPassed >= $cycleDays) {
                $alreadyPaid = 0;
                $cycleStartDate = null;
            }
        }

        if ($alreadyPaid >= $capAmount && !empty($bppId)) {

            $bppExists = DB::table('claims as c')
                ->join('team_msme_schemes as msme', 'msme.team_id', '=', 'c.team_registration_id')
                ->join('claim_types as ct', 'ct.id', '=', 'c.claim_type_id')
                ->where('msme.id', $msmeId)
                ->where('ct.slug', $claimTypeId)
                ->where('c.bpp_id', $bppId)
                ->where('c.claim_status', BatchStatus::PAYMENT_COMPLETED->value)
                ->exists();

            if ($bppExists) {
                return [
                    'success' => false,
                    'code'    => 'BPP_ID_ALREADY_USED',
                    'message' => 'This BPP ID is already used after incentive cap completion.',
                    'errors'  => [
                        'bpp_id' => ['This BPP ID is already used.']
                    ]
                ];
            }
        }

        $remainingCap  = max(0, $capAmount - $alreadyPaid);
        $payableAmount = min($matchedRule->incentive_amount, $remainingCap);

        $totalAfterThisClaim = $alreadyPaid + $payableAmount;
        $remainingAmount = max(0, $capAmount - $totalAfterThisClaim);

        if ($remainingAmount > 0) {
            $responseCode = 'PARTIAL_INCENTIVE';
            $responseMessage = "You are eligible for ₹{$payableAmount}. Remaining incentive balance is ₹{$remainingAmount}.";
        } else {
            $responseCode = 'INCENTIVE_CAP_COMPLETED';
            $responseMessage = "You have exhausted the maximum incentive limit of ₹{$capAmount}.";
        }

        $bonusAmount = 0;

        if ($cycleStartDate === null) {
            $cycleStartDate = now();
        }
        // 365 days reset cycle will be discuss
        if (($alreadyPaid + $payableAmount) >= $capAmount && $bonusDays > 0 && $bonusPercent > 0) {
            $daysToComplete = Carbon::parse($cycleStartDate)->diffInDays(now());

            if ($daysToComplete <= $bonusDays) {
                $bonusAmount = $capAmount * ($bonusPercent / 100);
            }
        }

        return [
            'success' => true,
            'code'    => $responseCode,
            'message' => $responseMessage,
            'data'    => [
                'aov_type'         => $finalAovType,
                'milestone'        => $matchedRule->milestone_code,
                'amount'   => round($payableAmount, 2),
                'remaining_amount' => round($remainingAmount, 2),
                'bonus_amount'     => round($bonusAmount, 2),
                'cap_amount'       => $capAmount,
                'bpp_id'           => $bppId
            ]
        ];
    }

    public function calculatePackagingIncentiveAmount(string $claimTypeId, array $orderDetails)
    {
        $orderCount = count($orderDetails);

        $rule = DB::table('claim_incentive_validations_rules')
            ->where('claim_type', $claimTypeId)
            ->where('status', 1)
            ->first();

        if (!$rule) {
            return [
                'success' => false,
                'code'    => 'RULE_NOT_FOUND',
                'errors'  => [
                    '__business__' => ['Packaging incentive rule not configured.']
                ]
            ];
        }

        $errors   = [];
        $messages = [];

        foreach ($orderDetails as $rowId => $row) {

            if (!empty($row['network_transaction_id'])) {
                $txnExists = DB::table('claim_orders')
                    ->where('network_transaction_id', $row['network_transaction_id'])
                    ->exists();

                if ($txnExists) {
                    $msg = 'This Network Transaction ID already exists.';
                    $errors["order_details.$rowId.network_transaction_id"][] = $msg;
                    $messages[] = $msg;
                }
            }

            if (!empty($row['order_invoice_number'])) {
                $invoiceExists = DB::table('claim_orders')
                    ->where('order_invoice_number', $row['order_invoice_number'])
                    ->exists();

                if ($invoiceExists) {
                    $msg = 'This Invoice Number already exists.';
                    $errors["order_details.$rowId.order_invoice_number"][] = $msg;
                    $messages[] = $msg;
                }
            }
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'code'    => 'DUPLICATE_TRANSACTION_OR_INVOICE',
                'message' => $messages[0],
                'errors'  => $errors,
            ];
        }

        $maxAllowedOrders = (int) ($rule->max_transactions ?? 0);

        $eligibleOrderCount = $maxAllowedOrders > 0
            ? min($orderCount, $maxAllowedOrders)
            : $orderCount;

        $isCapped = ($maxAllowedOrders > 0 && $orderCount > $maxAllowedOrders);

        $incentivePerOrder = (float) $rule->incentive_amount;
        $amount            = $eligibleOrderCount * $incentivePerOrder;

        $warningMsg = '';
        if ($isCapped) {
            $warningMsg =
                "You have submitted {$orderCount} orders. " .
                "Incentive will be calculated only for first {$maxAllowedOrders} orders.";
        }

        return [
            'success' => true,
            'code'    => 'PACKAGING_INCENTIVE_CALCULATED',
            'message' => $warningMsg,
            'data'    => [
                'total_orders_submitted' => $orderCount,
                'eligible_orders'        => $eligibleOrderCount,
                'incentive_per_order'    => $incentivePerOrder,
                'amount'                 => $amount,
                'is_capped'              => $isCapped
            ],
        ];
    }


    public function execute(ClaimDto $dto)
    {
        //dd($dto);
        return  DB::transaction(function () use ($dto) {
            $incentiveData = $this->calculateIncentiveTax(strtolower(trim($dto->number_of_skus)), strtolower(trim($dto->msme_classification)), strtolower(trim($dto->msme_category)), strtolower(trim($dto->target_audience)));

            $orderDetails = collect($dto->order_details ?? [])
                ->whereNotNull('network_transaction_id')
                ->whereNotNull('order_invoice_number')
                ->values()
                ->toArray();

            if ($dto->claim_type_id == "claim-for-catalogue-creation") {

                $productCatelogueAmount  = (array) $this->processCatalogueCreationClaim($dto);

                if ($productCatelogueAmount['success'] == false) {
                    return [
                        'success' => false,
                        'code'    => 401,
                        'message' => $productCatelogueAmount['message'],
                        'errors'  => $productCatelogueAmount['errors'] ?? []
                    ];
                }
                $finalResponse = $productCatelogueAmount;
            }

            if ($dto->claim_type_id == "claim-for-accounts-management") {

                $accountIncentiveAmount = $this->calculateAccountIncentiveAmount($dto->team_registration_id, $dto->msme_udyam_number, $dto->msme_id, $dto->claim_type_id, $orderDetails, $dto->bpp_id);
                //dd($accountIncentiveAmount);
                if ($accountIncentiveAmount['success'] == false) {
                    return [
                        'success' => false,
                        'code'    => 401,
                        'message' => $accountIncentiveAmount['message'],
                        'errors'  => $accountIncentiveAmount['errors'] ?? []
                    ];
                }
                $finalResponse = $accountIncentiveAmount;
            }

            if ($dto->claim_type_id == "claim-for-packaging") {

                $packagingIncentiveAmount = $this->calculatePackagingIncentiveAmount($dto->claim_type_id, $orderDetails, $dto->number_of_orders);
                // /dd($packagingIncentiveAmount);
                if ($packagingIncentiveAmount['success'] == false) {
                    return [
                        'success' => false,
                        'code'    => 401,
                        'message' => $packagingIncentiveAmount['message'],
                        'errors'  => $packagingIncentiveAmount['errors'] ?? []
                    ];
                }
                $finalResponse = $packagingIncentiveAmount;
            }


            // dd($accountIncentiveAmount);
            /*if ($dto->claim_type_id == "claim-for-accounts-management") {
                $netSalesIncentiveData = $this->calculateIncentiveTaxAccount(
                    trim($dto->net_sales),
                    strtolower(trim($dto->msme_category)),
                    strtolower(trim($dto->target_audience)),
                    trim($dto->no_of_transactions ?? 0)
                );
            }

            if ($dto->claim_type_id == "claim-for-logistics-and-transportation") {
                $logisticsIncentiveData = $this->calculateIncentiveTaxLogistics(
                    intval($dto->number_of_orders),
                    strtolower(trim($dto->target_audience))
                );
            }*/


            //dd($dto);

            $claimData = [
                'id' => $dto->id ? $dto->id : UuidGenerator::uuid7(),
                'snp_id' => $dto->snp_id,
                'team_registration_id' => $dto->team_registration_id,
                'msme_udyam_number' => $dto->msme_udyam_number,
                'msme_classification' => $dto->msme_classification,
                'msme_name' => $dto->msme_name,
                'msme_category' => $dto->msme_category,
                'msme_transaction_type' => $dto->msme_transaction_type,
                //'seller_provider_id' => $dto->seller_provider_id,
                'bpp_id' => $dto->bpp_id,
                'catalogue_type' => $dto->catalogue_type,
                'onboarding_date' => $dto->onboarding_date ? Carbon::parse($dto->onboarding_date)->format('Y-m-d') : null,
                'number_of_orders' => $dto->number_of_orders,
                'minimum_order_value' => $dto->minimum_order_value,
                'no_of_transactions' => $dto->no_of_transactions,
                'number_of_skus' => $dto->number_of_skus,
                'total_gmv' => $dto->total_gmv,
                'net_sales' => $dto->net_sales,
                'total_commission' => $dto->total_commission,
                'declaration_dual_claim' => $dto->declaration_dual_claim,
                'declaration_eligibility' => $dto->declaration_eligibility,
                'declaration_authorization' => $dto->declaration_authorization,
                'udin_number' => $dto->udin_number,
                'remarks' => $dto->remarks,
                'submitted_at' => Carbon::now(),
                'campaign_period' => $dto->campaign_period,
                'campaign_duration' => $dto->campaign_duration,
                'msme_email' => $dto->msme_email,
                'msme_mobile' => $dto->msme_mobile,
                'msme_address' => $dto->msme_address,
                'msme_state' => $dto->msme_state,
                'msme_district' => $dto->msme_district,
                'design_type' => $dto->design_type,
                'date_of_design_request' => $dto->date_of_design_request,
                'date_of_design_delivery' => $dto->date_of_design_delivery,
                'design_cost' => $dto->design_cost,
                'single_use_plastic' => $dto->single_use_plastic,
                'description' => $dto->description,
                'packaging_remarks' => $dto->packaging_remarks,

                'organisation_id_seller_np' => $dto->organisation_id_seller_np,
                'organisation_id_lsp' => $dto->organisation_id_lsp,
                'seller_np_configuration' => $dto->seller_np_configuration,
                'configuration' => $dto->configuration,
                'ondc_seller_network_id' => $dto->ondc_seller_network_id,
                'seller_credential_report' => $dto->seller_credential_report,
                'catalogue_score_report' => $dto->catalogue_score_report,
                'date_of_onboarding' => $dto->date_of_onboarding ? Carbon::parse($dto->date_of_onboarding)->format('Y-m-d') : null,

                'date_of_sku_update' => $dto->date_of_sku_update ? Carbon::parse($dto->date_of_sku_update)->format('Y-m-d') : null,
                'organisation_id_seller_np' => $dto->organisation_id_seller_np,
                'claim_status' => 0
            ];

            if ($dto->claim_type_id == "claim-for-catalogue-creation") {
                $claimData['amount'] = $finalResponse['data']['amount'] ?? null;
            } elseif ($dto->claim_type_id == "claim-for-accounts-management") {
                $claimData['amount'] = $finalResponse['data']['amount'] ?? null;
                $claimData['bonus_amount'] = $finalResponse['data']['bonus_amount'] ?? null;
            } elseif ($dto->claim_type_id == "claim-for-logistics-and-transportation") {
                $claimData['gst_charge'] = $logisticsIncentiveData['gst_charge'] ?? null;
                $claimData['gst_charge_amount'] = $logisticsIncentiveData['gst_charge_amount'] ?? null;
                $claimData['amount'] = $logisticsIncentiveData['totalAmount'] ?? null;
            } elseif ($dto->claim_type_id == "claim-for-packaging") {
                //$claimData['amount']= $this->getSubsidyAmount($dto->design_cost);
                $claimData['amount'] = $finalResponse['data']['amount'] ?? null;
            }

            if ($dto->id) {
                $claimData['updated_at'] = now();
                $claimData['updated_by'] = auth()->user()->id;
                $claimData['is_edited'] = 1;
                //$claimData['status'] = ClaimReviewStatus::DRAFT->value;
                //$claimData['review_status'] = ClaimReviewStatus::DRAFT->value;
                //$claimData['review_status_updated_by'] = auth()->user()->id;
                //$claimData['review_status_updated_at'] = now();
            } else {
                $claimData['status'] = ClaimReviewStatus::DRAFT->value;
                $claimData['claim_type_id'] = $this->getClaimTypeIdBySlug($dto->claim_type_id);
                $claimData['application_number'] = $this->claimService->generateApplicationNumber();
                $claimData['created_at'] = now();
                $claimData['created_by'] = auth()->user()->id;
            }
            /* if($dto->claim_type_id != "claim-for-packaging"){
                $claimOrderData = [];
                $claimOrderData = [
                    [
                        'id' => UuidGenerator::uuid7(),
                        'claim_id' => $claimData['id'],
                        'ondc_order_id' => $dto->ondc_order_id1,
                        'invoice_number' => $dto->ondc_invoice_number1,
                        'invoice_date' => $dto->ondc_invoice_date1 ? Carbon::parse($dto->ondc_invoice_date1)->format('Y-m-d') : null,
                    ],
                    [
                        'id' => UuidGenerator::uuid7(),
                        'claim_id' => $claimData['id'],
                        'ondc_order_id' => $dto->ondc_order_id2,
                        'invoice_number' => $dto->ondc_invoice_number2,
                        'invoice_date' => $dto->ondc_invoice_date2 ? Carbon::parse($dto->ondc_invoice_date2)->format('Y-m-d') : null,
                    ]
                ];

                foreach ($dto->network_transaction_id as $key => $txnId) {

                    $claimOrderData[] = [
                        'id' => UuidGenerator::uuid7(),
                        'claim_id' => $claimData['id'],
                        'network_transaction_id' => $txnId,
                        'network_transaction_date' => !empty($dto->network_transaction_date[$key]) ? Carbon::parse($dto->network_transaction_date[$key])->format('Y-m-d') : null,
                        'transaction_status' => $dto->transaction_status[$key] ?? null,
                        'order_invoice_number' => $dto->order_invoice_number[$key] ?? null,
                    ];
                }
            }*/

            //if ($dto->claim_type_id != "claim-for-packaging") {

            $claimOrderData = [];

            if (!empty($dto->ondc_order_id1) || !empty($dto->ondc_invoice_number1) || !empty($dto->ondc_invoice_date1)) {
                $claimOrderData[] = [
                    'id' => UuidGenerator::uuid7(),
                    'claim_id' => $claimData['id'],
                    'ondc_order_id' => $dto->ondc_order_id1,
                    'invoice_number' => $dto->ondc_invoice_number1,
                    'invoice_date' => $dto->ondc_invoice_date1 ? Carbon::parse($dto->ondc_invoice_date1)->format('Y-m-d') : null,
                    'shipping_cost' => null,
                    'network_transaction_id' => null,
                    'network_transaction_date' => null,
                    'transaction_status' => null,
                    'order_invoice_number' => null,
                ];
            }
            if (!empty($dto->ondc_order_id2) || !empty($dto->ondc_invoice_number2) || !empty($dto->ondc_invoice_date2)) {
                $claimOrderData[] = [
                    'id' => UuidGenerator::uuid7(),
                    'claim_id' => $claimData['id'],
                    'ondc_order_id' => $dto->ondc_order_id2,
                    'invoice_number' => $dto->ondc_invoice_number2,
                    'invoice_date' => $dto->ondc_invoice_date2 ? Carbon::parse($dto->ondc_invoice_date2)->format('Y-m-d') : null,
                    'shipping_cost' => null,
                    'network_transaction_id' => null,
                    'network_transaction_date' => null,
                    'transaction_status' => null,
                    'order_invoice_number' => null,
                ];
            }
            if (!empty($dto->order_details)) {

                foreach ($dto->order_details as $randomId => $row) {

                    $claimOrderData[] = [
                        'id'                        => UuidGenerator::uuid7(),
                        'claim_id'                  => $claimData['id'],
                        'ondc_order_id'             => null,
                        'invoice_number'            => null,
                        'invoice_date'              => null,
                        'shipping_cost'             => null,
                        'network_transaction_id'    => $row['network_transaction_id'] ?? null,
                        'network_transaction_date'  => !empty($row['network_transaction_date']) ? Carbon::parse($row['network_transaction_date'])->format('Y-m-d') : null,
                        'transaction_status'        => $row['transaction_status'] ?? null,
                        'order_invoice_number'      => $row['order_invoice_number'] ?? null,
                    ];
                }
            }
            //}

            $claimDocuments = [];
            if (is_array($dto->claim_documents) && count($dto->claim_documents) > 0) {
                foreach ($dto->claim_documents as $index => $document) {
                    if (strpos($document, '|') !== false) {
                        [$documentCategoryId, $fileUploadId] = explode('|', $document);
                        $claimDocuments[] = [
                            'id' => UuidGenerator::uuid7(),
                            'claim_id' => $claimData['id'],
                            'document_category_id' => $documentCategoryId,
                            'file_upload_id' => $fileUploadId,
                            'uploaded_at' => now(),
                            'uploaded_by' => auth()->id(),
                        ];
                    }
                }
            }
            //dd($claimData,$claimOrderData);
            if ($dto->id) {
                DB::table('claims')->where('id', $dto->id)->update($claimData);
            } else {
                Claim::create($claimData);
            }

            if (!empty($claimOrderData)) {
                DB::table('claim_orders')->where('claim_id', $claimData['id'])->delete(); // Ensure no duplicate entries
                DB::table('claim_orders')->insert($claimOrderData);
            }

            if (!empty($claimDocuments)) {
                DB::table('claim_documents')->where('claim_id', $claimData['id'])->delete();
                DB::table('claim_documents')->insert($claimDocuments);
            }

            return [
                'success' => true,
                //'message' => $accountIncentiveAmount['message']
                'code'    => $finalResponse['code'] ?? 'INCENTIVE_CALCULATED',
                'message' => $finalResponse['message'] ?? '',
                'data'    => $finalResponse['data'] ?? []

            ];

            /*TimelineService::addApprovalDocument(
                serviceId: $claimData['id'],
                subject: $this->createSubject(ClaimReviewStatus::SUBMITTED->value),
                comment: $dto?->comments ?? null,
                status: ClaimReviewStatus::getName(ClaimReviewStatus::SUBMITTED->value)
            );*/
        });
    }

    public function getClaimTypeIdBySlug(string $slug): string
    {
        $claimType = DB::table('claim_types')->where('slug', $slug)->first();
        if (!$claimType) {
            throw new \Exception("Claim type with slug '$slug' does not exist.");
        }
        return $claimType->id;
    }
}
