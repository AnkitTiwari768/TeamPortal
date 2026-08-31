<?php

declare(strict_types=1);

namespace App\Web\FundDistribution;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;

/**
 * ClaimDistributionService
 *
 * Public API Gateway for automated claim distribution workflows.
 * Functions as the high-level orchestration layer tying mapping, 
 * discovery, duplication defense, and physical processing together
 * within an all-or-nothing database transactional boundary.
 */
class ClaimDistributionService
{
    protected ClaimTypeMappingService $mappingService;
    protected ClaimPoolResolver $poolResolver;
    protected ClaimDistributionProcessor $processor;

    public function __construct(
        ClaimTypeMappingService $mappingService,
        ClaimPoolResolver $poolResolver,
        ClaimDistributionProcessor $processor
    ) {
        $this->mappingService = $mappingService;
        $this->poolResolver = $poolResolver;
        $this->processor = $processor;
    }

    /**
     * Safely triggers the automated routing and deduction logic for an external entity.
     *
     * Input payload schema:
     * [
     *   'source_id'         => string (Required - external record ID),
     *   'source_type'       => string (Defaults to 'CLAIM'),
     *   'claim_type_slug'   => string (Required - matching config map),
     *   'financial_year'    => string (Required - '2024-2025'),
     *   'amount'            => float  (Required),
     *   'tds_percentage'    => float  (Optional),
     *   'duration_id'       => uuid   (Optional - forces unique pool lookup),
     *   'sub_duration_id'   => uuid   (Optional),
     *   'claim_number'      => string (Optional - maps to reference_number),
     *   'user_id'           => string (Optional)
     * ]
     *
     * @param array $payload
     * @return array Operation success packet with IDs.
     * @throws Exception On valid failure scenarios requiring upper-level mitigation.
     */
    public function createDistributionForClaim(array $payload): array
    {
        $sourceId = $payload['source_id'] ?? null;
        $sourceType = $payload['source_type'] ?? 'CLAIM';

        if (empty($sourceId)) {
            throw new Exception("Gateway validation error: unique source_id required to facilitate claim distribution.");
        }

        try {
            // Begin top-level ACID transaction wrapping everything.
            return DB::transaction(function () use ($payload, $sourceType, $sourceId) {
                
                // 1. Reusable Duplicate Guard Clause
                if ($this->isIdempotentViolation($sourceType, $sourceId)) {
                    Log::warning("Duplicate deduction request neutralized.", [
                        'src' => $sourceType, 
                        'id'  => $sourceId
                    ]);
                    return [
                        'status'  => 'neutralized',
                        'code'    => 'DUPLICATE_ORIGIN',
                        'payload' => []
                    ];
                }

                // 2. Resolve Config Logic Components
                $claimSlug = $payload['claim_type_slug'] ?? '';
                $mappedComponents = $this->mappingService->getMapping($claimSlug);

                // 3. Construct Search Payload
                $criteria = [
                    'financial_year'     => $payload['financial_year'] ?? null,
                    'major_component_id' => $mappedComponents['major_component_id'],
                    'sub_component_id'   => $mappedComponents['sub_component_id'] ?? null,
                    'duration_id'        => $payload['duration_id'] ?? null,
                    'sub_duration_id'    => $payload['sub_duration_id'] ?? null,
                ];

                if (empty($criteria['financial_year'])) {
                    throw new Exception("Critical Metadata Missing: financial_year is required for pool scoping.");
                }

                // 4. Pool Discovery Phase
                $discoveredPools = $this->poolResolver->resolve($criteria);

                // 5. Physical Execution Phase
                $transferMetadata = [
                    'amount'           => (float) ($payload['amount'] ?? 0),
                    'tds_percentage'   => (float) ($payload['tds_percentage'] ?? 0),
                    'source_type'      => $sourceType,
                    'source_id'        => $sourceId,
                    'reference_number' => $payload['claim_number'] ?? null,
                    'remarks'          => "Auto-Dist via {$claimSlug}",
                    'user_id'          => $payload['user_id'] ?? null,
                    // Transfer period mapping down to ledger factory level
                    'duration_id'      => $payload['duration_id'] ?? null,
                    'sub_duration_id'  => $payload['sub_duration_id'] ?? null,
                ];

                $completedUuids = $this->processor->executeTransactionalDeductions($discoveredPools, $transferMetadata);

                Log::info("Automation complete for {$sourceType}#{$sourceId}");

                return [
                    'status'           => 'success',
                    'code'             => 'DISTRIBUTION_COMMITTED',
                    'distribution_ids' => $completedUuids
                ];
            });

        } catch (Throwable $e) {
            // Comprehensive cleanup logging ensures traceability in Enterprise context
            Log::critical("Automated Claim Distribution Failure Event", [
                'message' => $e->getMessage(),
                'source'  => "{$sourceType}:{$sourceId}",
                'trace'   => $e->getTraceAsString()
            ]);
            // Re-throw to allow upstream application handler decision making.
            throw $e; 
        }
    }

    /**
     * Universal duplication audit helper. 
     * Scans distribution ledger for pre-existing matches ignoring soft-deleted artifacts.
     *
     * @param string $sourceType Origin indicator (e.g. 'CLAIM')
     * @param string $sourceId External identity uuid
     * @return bool Returns TRUE if record pre-exists.
     */
    public function isIdempotentViolation(string $sourceType, string $sourceId): bool
    {
        return DB::table('fund_distributions')
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereNull('deleted_at')
            ->exists();
    }
}
