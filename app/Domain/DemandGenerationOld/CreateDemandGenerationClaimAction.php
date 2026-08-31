<?php

declare(strict_types=1);

namespace App\Domain\DemandGeneration;

use App\Domain\NetworkProvider\NetworkProvider;
use App\Http\Api\V1\FileUpload\FileUpload;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class CreateDemandGenerationClaimAction
{
    public function execute(array $data, string $userId, $lowAovFile = null, $highAovFile = null)
    {
        try {

            DB::beginTransaction();

            $networkProvider = NetworkProvider::where('user_id', $userId)->firstOrFail();

            // Get or create active phase
            $phase = $this->getOrCreateActivePhase(
                $networkProvider->id,
                $data['low_aov_claim_period_start'],
                $data['low_aov_claim_period_end']
            );

            // Process Low AOV claim
            $lowAovClaim = $this->processAovClaim(
                'LOW',
                $data,
                $networkProvider,
                $phase,
                $lowAovFile,
                $userId
            );

            DB::commit();

            return [
                'status' => true,
                'data' => [
                    'claim_id' => $claim->id ?? null,
                    'total_incentive' => $claim->incentive_amount ?? null
                ]
            ];
        } catch (\Exception $e) {

            DB::rollBack();

            return [
                'status' => false,
                'message' => $e->getMessage(),
                'errors' => ['general' => [$e->getMessage()]]
            ];
        }
    }

    /**
     * Create a new claim
     */
    // public function createClaim(array $data, int $userId, $lowAovFile, $highAovFile = null): DemandGenerationClaim
    // {
    //     $networkProvider = NetworkProvider::findOrFail($data['network_provider_id']);

    //     // Get or create active phase
    //     $phase = $this->getOrCreateActivePhase(
    //         $networkProvider->id,
    //         $data['low_aov_claim_period_start'],
    //         $data['low_aov_claim_period_end']
    //     );

    //     // Process Low AOV claim
    //     $lowAovClaim = $this->processAovClaim(
    //         'LOW',
    //         $data,
    //         $networkProvider,
    //         $phase,
    //         $lowAovFile,
    //         $userId
    //     );

    //     // Process High AOV claim if provided
    //     $highAovClaim = null;
    //     if (!empty($data['high_aov_categories']) && $highAovFile) {
    //         $highAovClaim = $this->processAovClaim(
    //             'HIGH',
    //             $data,
    //             $networkProvider,
    //             $phase,
    //             $highAovFile,
    //             $userId
    //         );
    //     }

    //     return $lowAovClaim; // Return main claim
    // }

    /**
     * Process AOV claim with improved error handling
     */
    private function processAovClaim(
        string $aovType,
        array $data,
        NetworkProvider $networkProvider,
        DemandGenerationPhase $phase,
        $file,
        string $userId
    ): DemandGenerationClaim {
        $prefix = strtolower($aovType) . '_aov_';

        try {
            // Upload file
            $document = $this->uploadExcelFile($file, $userId, $aovType . '_AOV');

            // Validate and process Excel data
            $excelData = $this->processExcelFile($file->getPathname(), $aovType);

            // Validate cumulative transactions match Excel count
            $expectedCount = (int) $data[$prefix . 'cumulative_txn'];
            $actualCount = $excelData['transaction_count'];

            if ($expectedCount !== $actualCount) {
                throw new \Exception(
                    "Number of transactions mismatch. " .
                        "Claim form states {$expectedCount} transactions, " .
                        "but Excel file contains {$actualCount} valid completed transactions."
                );
            }

            // Validate unique MSE count
            $expectedMses = (int) $data[$prefix . 'unique_mse'];
            $actualMses = count($excelData['unique_mses']);

            if ($expectedMses !== $actualMses) {
                throw new \Exception(
                    "Number of unique MSEs mismatch. " .
                        "Claim form states {$expectedMses} unique MSEs, " .
                        "but Excel file contains {$actualMses} unique MSEs with MSME TEAM credential."
                );
            }

            // Validate MSE exclusions
            $this->validateMseExclusions(
                $excelData['unique_mses'],
                $networkProvider->id,
                $phase->id
            );

            // Calculate incentive
            $incentive = $this->calculateIncentive(
                $networkProvider->id,
                $aovType,
                (int) $data[$prefix . 'cumulative_txn'],
                (int) $data[$prefix . 'unique_mse'],
                $excelData
            );

            // Get applicable slab
            $slab = $this->getApplicableSlab(
                $aovType,
                (int) $data[$prefix . 'cumulative_txn'],
                (int) $data[$prefix . 'unique_mse']
            );

            if (!$slab) {
                throw new \Exception(
                    "No incentive slab found for {$aovType} AOV with " .
                        "{$data[$prefix . 'cumulative_txn']} transactions and " .
                        "{$data[$prefix . 'unique_mse']} unique MSEs."
                );
            }

            // Create claim
            $claim = DemandGenerationClaim::create([
                'id' => uuid(),
                'network_provider_id' => $networkProvider->id,
                'phase_id' => $phase->id,
                'aov_category_id' => $aovType === 'LOW' ? 1 : 2,
                'unique_mse_count' => $data[$prefix . 'unique_mse'],
                'cumulative_transactions' => $data[$prefix . 'cumulative_txn'],
                'slab_id' => $slab->id,
                'incentive_amount' => $incentive,
                'declaration_accepted' => true,
                'low_aov_excel_document_id' => $aovType === 'LOW' ? $document->id : null,
                'low_aov_excel_document_name' => $aovType === 'LOW' ? $document->file_name : null,
                'high_aov_excel_document_id' => $aovType === 'HIGH' ? $document->id : null,
                'high_aov_excel_document_name' => $aovType === 'HIGH' ? $document->file_name : null,
                'submitted_at' => now(),
                'status' => 1, // Submitted status
            ]);

            // Save transaction details
            $this->saveTransactionDetails($claim, $excelData['transactions']);

            // Add MSEs to exclusions
            $this->addMseExclusions($excelData['unique_mses'], $networkProvider->id, $phase->id);

            // Check if phase should reset (max threshold reached)
            $this->checkPhaseReset($phase, $claim);

            // Log successful claim
            Log::info("Demand generation claim submitted", [
                'claim_id' => $claim->id,
                'network_provider_id' => $networkProvider->id,
                'aov_type' => $aovType,
                'transactions' => $data[$prefix . 'cumulative_txn'],
                'unique_mses' => $data[$prefix . 'unique_mse'],
                'incentive' => $incentive,
            ]);

            return $claim;
        } catch (\Exception $e) {
            // Clean up uploaded file on error
            if (isset($document)) {
                try {
                    Storage::disk('private')->delete($document->file_path);
                    $document->delete();
                } catch (\Exception $deleteError) {
                    Log::error('Failed to clean up file after claim error', [
                        'document_id' => $document->id ?? null,
                        'error' => $deleteError->getMessage(),
                    ]);
                }
            }

            throw new \Exception(
                "Failed to process {$aovType} AOV claim: " . $e->getMessage()
            );
        }
    }


    /**
     * Calculate incentive based on PDF business rules
     * PDF shows FIXED incentive amounts per slab, not per-transaction + bonus
     */
    private function calculateIncentive(string $networkProviderId, string $aovType, int $transactions, int $uniqueMses): float
    {
        // Get applicable slab based on transactions AND unique MSEs
        $slab = $this->getApplicableSlab($aovType, $transactions, $uniqueMses);

        if (!$slab) {
            // According to PDF, no incentive if doesn't meet minimum slab criteria
            // Minimum for LOW AOV: >=10 transactions
            // Minimum for HIGH AOV: >=10 transactions AND >=10 unique MSEs
            return 0;
        }

        $slabIncentive = (float) $slab->incentive_amount;

        // Get total incentive already claimed by this BNP in current phase for this AOV type
        $totalIncentiveTaken = $this->getTotalIncentiveTaken($networkProviderId, $aovType);
        dd($totalIncentiveTaken);
        // Check if BNP has already reached or exceeded this slab's incentive
        if ($totalIncentiveTaken >= $slabIncentive) {
            // Already claimed max for this slab level
            // Check if they qualify for next slab
            $nextSlab = $this->getNextHigherSlab($aovType, $transactions, $uniqueMses);
            if ($nextSlab && $totalIncentiveTaken < (float) $nextSlab->incentive_amount) {
                // Return difference between slabs
                return (float) $nextSlab->incentive_amount - $totalIncentiveTaken;
            }
            return 0;
        }

        // Return the full slab incentive (capped at remaining)
        $remaining = $slabIncentive - $totalIncentiveTaken;
        return min($slabIncentive, $remaining);
    }

    /**
     * Get applicable slab with ALL conditions
     */
    private function getApplicableSlab(string $aovType, int $transactions, int $uniqueMses): ?object
    {
        return DB::table('demand_generation_incentive_slabs as s')
            ->join('demand_generation_aov_categories as c', 's.aov_category_id', '=', 'c.id')
            ->where('c.code', $aovType)
            ->where('s.min_transactions', '<=', $transactions)
            ->where('s.min_unique_mses', '<=', $uniqueMses)
            ->orderBy('s.min_transactions', 'desc')
            ->orderBy('s.min_unique_mses', 'desc')
            ->first(['s.*', 'c.code']);
    }

    /**
     * Get next higher slab
     */
    private function getNextHigherSlab(string $aovType, int $transactions, int $uniqueMses): ?object
    {
        return DB::table('demand_generation_incentive_slabs as s')
            ->join('demand_generation_aov_categories as c', 's.aov_category_id', '=', 'c.id')
            ->where('c.code', $aovType)
            ->where(function ($query) use ($transactions, $uniqueMses) {
                $query->where('s.min_transactions', '>', $transactions)
                    ->orWhere('s.min_unique_mses', '>', $uniqueMses);
            })
            ->orderBy('s.min_transactions', 'asc')
            ->orderBy('s.min_unique_mses', 'asc')
            ->first(['s.*', 'c.code']);
    }

    /**
     * Get total incentive already taken by this BNP in current phase for specific AOV type
     */
    private function getTotalIncentiveTaken(string $networkProviderId, string $aovType): float
    {
        // Get current active phase for this BNP
        $activePhase = DemandGenerationPhase::where('network_provider_id', $networkProviderId)
            ->where('phase_end', '>=', now())
            ->first();

        if (!$activePhase) {
            return 0;
        }

        // Get AOV category ID
        $aovCategoryId = $aovType === 'LOW' ? 1 : 2;

        // Sum all approved/paid incentives for this BNP in current phase for this AOV type
        return DemandGenerationClaim::where('network_provider_id', $networkProviderId)
            ->where('phase_id', $activePhase->id)
            ->where('aov_category_id', $aovCategoryId)
            ->whereIn('status', [2, 4]) // Approved (2) or Paid (4) status
            ->sum('incentive_amount');
    }

    /**
     * Calculate incentive based on rules
     */
    // private function calculateIncentive(string $networkProviderId, string $aovType, int $transactions, int $uniqueMses, array $excelData): float
    // {
    //     // Apply slab-based bonus from matrix
    //     $slabBonus = $this->getSlabBonus($aovType, $transactions, $uniqueMses);

    //     $totalIncentiveTaken = $this->getTotalIncentiveTaken($networkProviderId);

    //     $finalIncentiveAmount = $slabBonus - $totalIncentiveTaken;

    //     return $finalIncentiveAmount;
    // }

    // private function getTotalIncentiveTaken(string $networkProviderId)
    // {
    //     return DB::table('demand_generation_claims')
    //         ->selectRaw('SUM(incentive_amount) as total_incentive_taken')
    //         ->where('network_provider_id', $networkProviderId)
    //         ->value('total_incentive_taken');
    // }

    /**
     * Get slab bonus from matrix
     */
    private function getSlabBonus(string $aovType, int $transactions, int $uniqueMses): float
    {
        $slab = DB::table('demand_generation_incentive_slabs as s')
            ->join('demand_generation_aov_categories as c', 's.aov_category_id', '=', 'c.id')
            ->where('c.code', $aovType)
            ->where('s.min_transactions', '<=', $transactions)
            ->where('s.min_unique_mses', '<=', $uniqueMses)
            ->orderBy('s.min_transactions', 'desc')
            ->orderBy('s.min_unique_mses', 'desc')
            ->first();

        return $slab ? (float) $slab->incentive_amount : 0;
    }

    /**
     * Get applicable slab
     */
    // private function getApplicableSlab(string $aovType, int $transactions, int $uniqueMses)
    // {
    //     return DB::table('demand_generation_incentive_slabs as s')
    //         ->join('demand_generation_aov_categories as c', 's.aov_category_id', '=', 'c.id')
    //         ->where('c.code', $aovType)
    //         ->where('s.min_transactions', '<=', $transactions)
    //         ->where('s.min_unique_mses', '<=', $uniqueMses)
    //         ->orderBy('s.min_transactions', 'desc')
    //         ->orderBy('s.min_unique_mses', 'desc')
    //         ->first();
    // }

    /**
     * Process Excel file
     */
    private function processExcelFile(string $filePath, string $aovType): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $transactions = [];
        $uniqueMsmeTeamCredMsmes = [];
        $totalAmount = 0;
        $rowCount = 0;

        foreach ($worksheet->getRowIterator(2) as $row) {
            $rowCount++;
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowData = [];
            foreach ($cellIterator as $cell) {
                $cellAddress = $cell->getCoordinate();
                $rowData[] = (string)$worksheet->getCell($cellAddress)->getValue();
            }

            // Validate required columns
            if (empty($rowData[0])) {
                continue; // Skip empty rows
            }

            $transaction = [
                'network_transaction_id' => trim($rowData[0]),
                'network_transaction_date' => Date::excelToDateTimeObject($rowData[1])->format('Y-m-d H:i:s'),
                'seller_id' => trim($rowData[2]),
                'order_invoice_number' => trim($rowData[4]),
                'has_msme_team_cred' => (bool) $rowData[5],
                'total_product_cost' => (float) $rowData[6],
                'total_tax_on_product' => (float) $rowData[7],
                'total_discount' => (float) ($rowData[8] ?? 0),
                'offers' => (float) ($rowData[9] ?? 0),
                'logistics_packaging' => (float) ($rowData[10] ?? 0),
                'tax_on_delivery_packaging' => (float) ($rowData[11] ?? 0),
                'misc_charges' => (float) ($rowData[12] ?? 0),
                'transaction_completed' => strtolower(trim($rowData[3])) === 'yes',
            ];


            // Calculate order value (excluding offers/discounts, including taxes/fees)
            $orderValue = $transaction['total_product_cost']
                + $transaction['total_tax_on_product']
                + $transaction['logistics_packaging']
                + $transaction['tax_on_delivery_packaging']
                + $transaction['misc_charges'];

            // Validate minimum order value
            $minOrderValue = ($aovType === 'LOW')
                ? DemandGenerationConstant::LOW_AOV_MINIMUM_ORDER_VALUE
                : DemandGenerationConstant::HIGH_AOV_MINIMUM_ORDER_VALUE; // Adjust as per business rules

            if ($orderValue < $minOrderValue) {
                throw new \Exception("Transaction {$transaction['network_transaction_id']} does not meet minimum order value of {$minOrderValue}");
            }

            // Track unique MSEs with MSME TEAM credential
            if ($transaction['has_msme_team_cred']) {
                $uniqueMsmeTeamCredMsmes[$transaction['seller_id']] = true;
            }

            $transactions[] = $transaction;
            $totalAmount += $orderValue;
        }

        if ($rowCount === 0) {
            throw new \Exception('Excel file contains no valid transaction data');
        }

        return [
            'transactions' => $transactions,
            'unique_mses' => array_keys($uniqueMsmeTeamCredMsmes),
            'total_amount' => $totalAmount,
            'transaction_count' => count($transactions)
        ];
    }

    /**
     * Upload Excel file
     */
    private function uploadExcelFile($file, string $userId, string $type): FileUpload
    {
        $originalName = $file->getClientOriginalName();
        $filename = 'claim_' . $type . '_' . time() . '_' . $originalName;
        $path = $file->storeAs('demand_generation_claims', $filename);

        return FileUpload::create([
            'id' => uuid(),
            'file_name' => $originalName,
            'file_path' => $path,
            'file_system_name' => $filename,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'file_extension' => $file->getExtension(),
            'created_by' => AuthId(),
            'updated_by' => AuthId()
        ]);
    }


    /**
     * Validate MSE exclusions
     */
    private function validateMseExclusions(array $mseIds, string $networkProviderId, string $phaseId): void
    {
        $exclusions = MsePhaseExclusion::where('network_provider_id', $networkProviderId)
            ->where('phase_id', $phaseId)
            ->whereIn('mse_id', $mseIds)
            ->exists();

        if ($exclusions) {
            throw new \Exception('Some MSEs have already been claimed in this phase and cannot be claimed again.');
        }
    }

    /**
     * Add MSE exclusions
     */
    private function addMseExclusions(array $mseIds, string $networkProviderId, string $phaseId): void
    {
        foreach ($mseIds as $mseId) {
            MsePhaseExclusion::create([
                'id' => uuid(),
                'network_provider_id' => $networkProviderId,
                'mse_id' => $mseId,
                'phase_id' => $phaseId,
            ]);
        }
    }

    /**
     * Get or create active phase
     */
    private function getOrCreateActivePhase(string $networkProviderId, string $startDate, string $endDate): DemandGenerationPhase
    {
        $existingPhase = DemandGenerationPhase::where('network_provider_id', $networkProviderId)
            ->where('phase_end', '>=', now())
            ->where('reset_reason', 'TIME_BASED')
            ->first();

        if ($existingPhase) {
            return $existingPhase;
        }

        return DemandGenerationPhase::create([
            'id' => uuid(),
            'network_provider_id' => $networkProviderId,
            'aov_category_id' => 1, // Default to LOW
            'phase_start' => $startDate,
            'phase_end' => $endDate,
            'reset_reason' => 'TIME_BASED',
        ]);
    }

    /**
     * Check if phase should reset
     */
    private function checkPhaseReset(DemandGenerationPhase $phase, DemandGenerationClaim $claim): void
    {
        // Check if max threshold reached (highest slab)
        $maxSlab = DB::table('demand_generation_incentive_slabs')
            ->where('aov_category_id', $claim->aov_category_id)
            ->orderBy('min_transactions', 'desc')
            ->first();

        if ($maxSlab && $claim->cumulative_transactions >= $maxSlab->min_transactions) {
            $phase->update([
                'reset_reason' => 'MAX_THRESHOLD',
                'phase_end' => now(),
            ]);
        }
    }

    /**
     * Save transaction details
     */
    private function saveTransactionDetails(DemandGenerationClaim $claim, array $transactions): void
    {
        foreach ($transactions as $transaction) {
            DB::table('demand_generation_claim_transactions')->insert([
                'id' => uuid(),
                'claim_id' => $claim->id,
                ...$transaction,
                'created_at' => now(),
            ]);
        }
    }

    /**
     * Get active phase for BNP
     */
    public function getActivePhase(string $networkProviderId): ?array
    {
        $phase = DemandGenerationPhase::where('network_provider_id', $networkProviderId)
            ->where('phase_end', '>=', now())
            ->first();

        if (!$phase) {
            return null;
        }

        return [
            'phase_id' => $phase->id,
            'start_date' => $phase->phase_start,
            'end_date' => $phase->phase_end,
            'reset_reason' => $phase->reset_reason,
            'days_remaining' => Carbon::parse($phase->phase_end)->diffInDays(now()),
        ];
    }
}
