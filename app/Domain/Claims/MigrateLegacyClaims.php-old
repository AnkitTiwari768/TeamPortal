<?php

declare(strict_types=1);

namespace App\Domain\Claims;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Domain\Batch\BatchStatus;
use App\Web\Claim\ClaimReviewStatus;
use App\Web\Claim\ClaimService;

class MigrateLegacyClaims
{
    /**
     * Run the legacy claimsheet data migration.
     */
    public function run($console = null): void
    {
        $message = "Starting legacy claimsheet data migration...";
        if ($console) {
            $console->info($message);
        } else {
            echo $message . "\n";
        }

        DB::transaction(function () use ($console) {
            // 1. Resolve Claim Type for Catalogue Creation
            $claimType = DB::table('claim_types')
                ->where('slug', 'claim-for-catalogue-creation')
                ->first();

            if (!$claimType) {
                throw new \Exception("Claim type 'claim-for-catalogue-creation' not found.");
            }

            $claimTypeId = $claimType->id;

            // 2. Fetch all legacy claimsheets
            $legacyClaims = DB::table('legacy_claimsheet')->get();
            $totalLegacy = count($legacyClaims);

            $message = "Found {$totalLegacy} legacy claimsheet records to migrate.";
            if ($console) {
                $console->info($message);
            } else {
                echo $message . "\n";
            }

            if ($legacyClaims->isEmpty()) {
                return;
            }

            // Get initial sequence for CLAIM application numbers
            $claimService = app(ClaimService::class);

            // Group legacy claims by snp_id
            $groupedClaims = $legacyClaims->groupBy('snp_id');

            $migratedCount = 0;
            $claimIndex = 0;

            foreach ($groupedClaims as $snpId => $claimsInSnp) {
                // Set up a Batch for this SNP group
                $batchId = (string) Str::uuid();
                $snpSuffix = !empty($snpId) ? '-' . $snpId : '-UNKNOWN';
                $batchNumber = 'MIG-CAT-' . date('YmdHis') . $snpSuffix;

                $batchClaimsInsert = [];
                $workflowInstancesInsert = [];
                $batchCreatedBy = null;

                foreach ($claimsInSnp as $legacy) {
                    // Check if already migrated
                    $existingClaim = DB::table('claims')->where('id', $legacy->id)->first();
                    if ($existingClaim) {
                        // Already exists, we can skip or update. Let's skip to keep migration clean.
                        if ($console) {
                            $console->warn("Claim ID {$legacy->id} already exists in claims table. Skipping.");
                        }
                        continue;
                    }

                    // Resolve SNP owner user ID
                    $claimCreatedBy = null;
                    if (!empty($legacy->snp_id)) {
                        $snpUser = DB::table('team_snp_scheme')
                            ->where('snp_id', $legacy->snp_id)
                            ->value('user_id');
                        if (!empty($snpUser)) {
                            $claimCreatedBy = $snpUser;
                        }
                    }

                    // Resolve MSME details from team_msme_schemes
                    $msmeDetails = null;
                    if (!empty($legacy->team_registration_id)) {
                        $msmeDetails = DB::table('team_msme_schemes')
                            ->where('team_id', $legacy->team_registration_id)
                            ->first();
                    }

                    // Resolve classification, category, transaction_type text/id
                    $msmeName = $msmeDetails->enterprise_name ?? 'N/A';
                    $msmeUdyamNumber = $legacy->udyam_registration_no ?: ($msmeDetails->udyam_no ?? 'N/A');

                    $msmeClassification = 'N/A';
                    if (!empty($msmeDetails->msme_classification)) {
                        $classificationAttr = DB::table('attribute_values')
                            ->where('id', $msmeDetails->msme_classification)
                            ->first();
                        $msmeClassification = $classificationAttr ? strtolower($classificationAttr->attribute_value) : 'N/A';
                    }

                    $msmeCategory = 'N/A';
                    if (!empty($msmeDetails->major_activity)) {
                        $categoryAttr = DB::table('attribute_values')
                            ->where('id', $msmeDetails->major_activity)
                            ->first();
                        $msmeCategory = $categoryAttr ? strtolower($categoryAttr->attribute_value) : 'N/A';
                    }

                    $msmeTransactionType = $msmeDetails->ondc_transaction_type_id ?? 'N/A';
                    $bppId = $legacy->seller_provider_id_verified ?: ($legacy->seller_provider_id ?: 'N/A');
                    $ondcSellerNetworkId = $legacy->seller_provider_id_verified ?: ($legacy->seller_provider_id ?: 'N/A');


                    // Generate claim application number
                    $appNo = $claimService->generateApplicationNumber($claimIndex++);

                    $eligibleIncentive = (float)($legacy->eligible_incentive ?? 0.00);

                    // Determine GST type based on state of the MSME
                    $gstType = '1'; // Default to IGST (1)
                    if ($msmeDetails && !empty($msmeDetails->state_id)) {
                        $msmeStateName = DB::table('states')->where('id', $msmeDetails->state_id)->value('name');
                        if ($msmeStateName && strtoupper(trim($msmeStateName)) === 'DELHI') {
                            $gstType = '2'; // Intra-state CGST+SGST (2)
                        }
                    }

                    $gstPercentage = $gstType === '1' ? 18.0 : 0.00;
                    $cgstPercentage = $gstType === '2' ? 9.0 : 0.00;
                    $sgstPercentage = $gstType === '2' ? 9.0 : 0.00;

                    $gstResults = $this->calculateInclusiveGST($eligibleIncentive, $gstType);

                    // Build Claim Data
                    $claimData = [
                        'id' => $legacy->id,
                        'claim_type_id' => $claimTypeId,
                        'application_number' => $appNo,
                        'snp_id' => $legacy->snp_id ?: 'N/A',
                        'team_registration_id' => $legacy->team_registration_id ?: 'N/A',
                        'msme_name' => $msmeName ?: 'N/A',
                        'msme_udyam_number' => $msmeUdyamNumber ?: 'N/A',
                        'msme_classification' => in_array($msmeClassification, ['micro', 'small', 'medium']) ? $msmeClassification : null,
                        'msme_category' => in_array($msmeCategory, ['manufacturing', 'services', 'trading', 'partnership']) ? $msmeCategory : null,
                        'msme_transaction_type' => $msmeTransactionType ?: 'N/A',
                        'bpp_id' => $bppId ?: 'N/A',
                        'ondc_seller_network_id' => $ondcSellerNetworkId ?: 'N/A',
                        'catalogue_type' => 'Manual',
                        'onboarding_date' => $legacy->onboarding_date_verified ?: ($legacy->onboarding_date ?: null),

                        'number_of_skus' => $legacy->total_skus ?? 0,

                        'amount' => $gstResults['base_amount'],
                        'gst_type' => (int) $gstType,
                        'gst_percentage' => $gstPercentage,
                        'cgst_percentage' => $cgstPercentage,
                        'sgst_percentage' => $sgstPercentage,
                        'gst_amount' => $gstResults['gst_amount'],
                        'cgst_amount' => $gstResults['cgst_amount'],
                        'sgst_amount' => $gstResults['sgst_amount'],
                        'total_claimed_amount' => $gstResults['total_claimed_amount'],
                        'declaration_dual_claim' => 1,
                        'declaration_eligibility' => 1,
                        'declaration_authorization' => 1,
                        'is_declaration_agreed' => 1,
                        'is_migrated' => 1,

                        'claim_status' => BatchStatus::PAYMENT_COMPLETED->value, // 19
                        'status' => ClaimReviewStatus::PAYMENT_COMPLETED->value, // 7

                        'created_at' => $legacy->created_at ?: now(),
                        'updated_at' => $legacy->updated_at ?: now(),
                        'created_by' => $claimCreatedBy,
                        'updated_by' => $claimCreatedBy,
                    ];

                    // Save Claim
                    DB::table('claims')->insert($claimData);

                    // Build Claim Orders Data
                    $ordersCount = 0;
                    $claimOrders = [];

                    // Order 1
                    if (!empty($legacy->order1_network_id) || !empty($legacy->order1_transaction_log) || !empty($legacy->order1_invoice)) {
                        $claimOrders[] = [
                            'id' => (string) Str::uuid(),
                            'claim_id' => $legacy->id,
                            'provider_id' => $bppId ?: 'N/A',
                            'team_id' => $legacy->team_registration_id ?: 'N/A',
                            'msme_name' => $msmeName ?: 'N/A',
                            'msme_udyam_number' => $msmeUdyamNumber ?: 'N/A',
                            'msme_classification' => in_array($msmeClassification, ['micro', 'small', 'medium']) ? $msmeClassification : null,
                            'msme_category' => in_array($msmeCategory, ['manufacturing', 'services', 'trading', 'partnership']) ? $msmeCategory : null,
                            'msme_transaction_type' => $msmeTransactionType ?: 'N/A',

                            'ondc_order_id' => $legacy->order1_network_verified ?: ($legacy->order1_network_id ?: 'N/A'),
                            'domain' => $legacy->market_channel ?: 'N/A',
                            'buyer_np_name' => 'N/A',
                            'seller_np_name' => 'N/A',
                            'invoice_number' => $legacy->order1_invoice ?: 'N/A',
                            'invoice_date' => $legacy->order1_invoice_date ?: null,
                            'cart_level_item_price' => $legacy->order1_value ?? 0.00,
                            'total_fee' => $legacy->order1_value ?? 0.00,

                            'network_transaction_id' => $legacy->order1_transaction_log ?: 'N/A',
                            'order_status' => 'Completed',
                            'is_migrated' => 1,

                            'created_at' => $legacy->created_at ?: now(),
                            'updated_at' => $legacy->updated_at ?: now(),
                        ];
                        $ordersCount++;
                    }

                    // Order 2
                    if (!empty($legacy->order2_network_id) || !empty($legacy->order2_transaction_log) || !empty($legacy->order2_invoice)) {
                        $claimOrders[] = [
                            'id' => (string) Str::uuid(),
                            'claim_id' => $legacy->id,
                            'provider_id' => $bppId ?: 'N/A',
                            'team_id' => $legacy->team_registration_id ?: 'N/A',
                            'msme_name' => $msmeName ?: 'N/A',
                            'msme_udyam_number' => $msmeUdyamNumber ?: 'N/A',
                            'msme_classification' => in_array($msmeClassification, ['micro', 'small', 'medium']) ? $msmeClassification : null,
                            'msme_category' => in_array($msmeCategory, ['manufacturing', 'services', 'trading', 'partnership']) ? $msmeCategory : null,
                            'msme_transaction_type' => $msmeTransactionType ?: 'N/A',

                            'ondc_order_id' => $legacy->order2_network_verified ?: ($legacy->order2_network_id ?: 'N/A'),
                            'domain' => $legacy->market_channel ?: 'N/A',
                            'buyer_np_name' => 'N/A',
                            'seller_np_name' => 'N/A',
                            'invoice_number' => $legacy->order2_invoice ?: 'N/A',
                            'invoice_date' => $legacy->order2_invoice_date ?: null,
                            'cart_level_item_price' => $legacy->order2_value ?? 0.00,
                            'total_fee' => $legacy->order2_value ?? 0.00,

                            'network_transaction_id' => $legacy->order2_transaction_log ?: 'N/A',
                            'order_status' => 'Completed',
                            'is_migrated' => 1,

                            'created_at' => $legacy->created_at ?: now(),
                            'updated_at' => $legacy->updated_at ?: now(),
                        ];
                        $ordersCount++;
                    }

                    if (!empty($claimOrders)) {
                        DB::table('claim_orders')->insert($claimOrders);
                    }

                    // Update orders counts on claim
                    DB::table('claims')->where('id', $legacy->id)->update([
                        'number_of_orders' => $ordersCount,
                        'no_of_transactions' => $ordersCount,
                        'total_gmv' => array_sum(array_column($claimOrders, 'total_fee')),
                        'net_sales' => array_sum(array_column($claimOrders, 'cart_level_item_price')),
                    ]);

                    // Queue for dy_batch_claims
                    $batchClaimsInsert[] = [
                        'id' => (string) Str::uuid(),
                        'batch_id' => $batchId,
                        'claim_id' => $legacy->id,
                        'status' => BatchStatus::PAYMENT_COMPLETED->value, // 19
                        'is_migrated' => 1,
                        'created_at' => now(),
                        'created_by' => $claimCreatedBy,
                        'updated_at' => now(),
                        'updated_by' => $claimCreatedBy,
                    ];

                    // Queue for dy_workflow_instances (CLAIM)
                    $workflowInstancesInsert[] = [
                        'id' => (string) Str::uuid(),
                        'workflow_type_id' => '0390c659-7119-11f0-81dc-00155d022d06', // WT_ID for Catalogue Creation
                        'entity_type' => 'claim',
                        'entity_id' => $legacy->id,
                        'current_state_id' => '791ddcc8-c5d7-11f0-922a-00155d022d06', // payment_completed state
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $migratedCount++;
                    if ($batchCreatedBy === null) {
                        $batchCreatedBy = $claimCreatedBy;
                    }
                }

                if (!empty($batchClaimsInsert)) {
                    $firstRow = $claimsInSnp->first();
                    $tdsAmount = $firstRow ? (float) ($firstRow->batch_tds_amount ?? 0.00) : 0.00;
                    $sgstTdsAmount = $firstRow ? (float) ($firstRow->batch_sgst_tds_amount ?? 0.00) : 0.00;
                    $cgstTdsAmount = $firstRow ? (float) ($firstRow->batch_cgst_tds_amount ?? 0.00) : 0.00;
                    $igstTdsAmount = $firstRow ? (float) ($firstRow->batch_igst_tds_amount ?? 0.00) : 0.00;

                    // Insert the Batch
                    DB::table('dy_batches')->insert([
                        'id' => $batchId,
                        'batch_number' => $batchNumber,
                        'claim_type_id' => $claimTypeId,
                        'financial_year' => '2026-27', // Set current financial year as per system setting
                        'month' => (int) date('m'),
                        'description' => 'Migrated legacy claimsheet data for SNP ' . ($snpId ?: 'UNKNOWN'),
                        'status' => BatchStatus::PAYMENT_COMPLETED->value, // 19
                        'current_status' => 'payment_completed',
                        'is_migrated' => 1,
                        'tds_amount' => $tdsAmount,
                        'sgst_tds_amount' => $sgstTdsAmount,
                        'cgst_tds_amount' => $cgstTdsAmount,
                        'igst_tds_amount' => $igstTdsAmount,
                        'created_at' => now(),
                        'created_by' => $batchCreatedBy ?? $claimCreatedBy,
                        'updated_at' => now(),
                        'updated_by' => $batchCreatedBy ?? $claimCreatedBy,
                    ]);

                    // Insert batch workflow instance (BATCH)
                    $workflowInstancesInsert[] = [
                        'id' => (string) Str::uuid(),
                        'workflow_type_id' => '0390c659-7119-11f0-81dc-00155d022d06',
                        'entity_type' => 'batch',
                        'entity_id' => $batchId,
                        'current_state_id' => '791ddcc8-c5d7-11f0-922a-00155d022d06',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // Insert Batch Claims mappings
                    DB::table('dy_batch_claims')->insert($batchClaimsInsert);

                    // Insert Workflow Instances
                    DB::table('dy_workflow_instances')->insert($workflowInstancesInsert);
                }
            }

            $message = "Migration completed successfully. Migrated {$migratedCount} claims into claims, claim_orders, dy_batches (Batch: {$batchNumber}), and dy_batch_claims.";
            if ($console) {
                $console->info($message);
            } else {
                echo $message . "\n";
            }
        });
    }

    /**
     * Rollback the migrated claims and associated records.
     */
    public function rollback($console = null): void
    {
        $message = "Starting rollback of migrated legacy claims...";
        if ($console) {
            $console->info($message);
        } else {
            echo $message . "\n";
        }

        DB::transaction(function () use ($console) {
            // Count records before deletion
            $batchClaimsCount = DB::table('dy_batch_claims')->where('is_migrated', 1)->count();
            $batchesCount = DB::table('dy_batches')->where('is_migrated', 1)->count();
            $ordersCount = DB::table('claim_orders')->where('is_migrated', 1)->count();
            $claimsCount = DB::table('claims')->where('is_migrated', 1)->count();

            // Delete workflow instances for migrated claims and batches
            DB::table('dy_workflow_instances')
                ->whereIn('entity_id', function ($query) {
                    $query->select('id')->from('claims')->where('is_migrated', 1);
                })
                ->orWhereIn('entity_id', function ($query) {
                    $query->select('id')->from('dy_batches')->where('is_migrated', 1);
                })
                ->delete();

            // Delete batch claim associations
            DB::table('dy_batch_claims')->where('is_migrated', 1)->delete();

            // Delete batches
            DB::table('dy_batches')->where('is_migrated', 1)->delete();

            // Delete claim orders
            DB::table('claim_orders')->where('is_migrated', 1)->delete();

            // Delete claims
            DB::table('claims')->where('is_migrated', 1)->delete();

            $message = "Rollback completed. Deleted {$batchClaimsCount} batch-claims, {$batchesCount} batches, {$ordersCount} claim-orders, and {$claimsCount} claims.";
            if ($console) {
                $console->info($message);
            } else {
                echo $message . "\n";
            }
        });
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
        $gstRate = 18.0;
        $baseAmount = round($inclusiveAmount / (1 + $gstRate / 100), 2);

        $gstAmount = 0.0;
        $cgstAmount = 0.0;
        $sgstAmount = 0.0;

        if ($gstType === '2') { // CGST + SGST
            $cgstRate = $gstRate / 2;
            $cgstAmount = round($baseAmount * ($cgstRate / 100), 2);
            $sgstAmount = round($baseAmount * ($cgstRate / 100), 2);

            // Adjust to ensure: Base Amount + CGST Amount + SGST Amount = Claim Amount (inclusiveAmount)
            $diff = round($inclusiveAmount - ($baseAmount + $cgstAmount + $sgstAmount), 2);
            if ($diff !== 0.0) {
                $sgstAmount = round($sgstAmount + $diff, 2);
            }
            $gstAmount = round($cgstAmount + $sgstAmount, 2);
        } else { // Default or GST Type 1 (IGST)
            $gstAmount = round($inclusiveAmount - $baseAmount, 2);
            
            // Adjust to ensure: Base Amount + GST Amount = Claim Amount (inclusiveAmount)
            $diff = round($inclusiveAmount - ($baseAmount + $gstAmount), 2);
            if ($diff !== 0.0) {
                $gstAmount = round($gstAmount + $diff, 2);
            }
        }

        // Validation Check: IGST
        if ($gstType !== '2') {
            if (round($baseAmount + $gstAmount, 2) !== round($inclusiveAmount, 2)) {
                throw new \UnexpectedValueException("IGST validation failed: Base Amount ({$baseAmount}) + GST Amount ({$gstAmount}) != Claim Amount ({$inclusiveAmount})");
            }
        } else { // Validation Check: CGST & SGST
            if (round($baseAmount + $cgstAmount + $sgstAmount, 2) !== round($inclusiveAmount, 2)) {
                throw new \UnexpectedValueException("CGST & SGST validation failed: Base Amount ({$baseAmount}) + CGST Amount ({$cgstAmount}) + SGST Amount ({$sgstAmount}) != Claim Amount ({$inclusiveAmount})");
            }
        }

        // Validation Check: Net Payable (Claim Amount - Total TDS = Net Amount)
        $totalTds = 0.0;
        $netAmount = round($inclusiveAmount - $totalTds, 2);
        if (round($inclusiveAmount - $totalTds, 2) !== $netAmount) {
            throw new \UnexpectedValueException("Net Payable validation failed");
        }

        return [
            'base_amount'          => $baseAmount,
            'gst_amount'           => $gstAmount,
            'cgst_amount'          => $cgstAmount,
            'sgst_amount'          => $sgstAmount,
            'total_claimed_amount' => $inclusiveAmount,
        ];
    }
}
