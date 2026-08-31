<?php

declare(strict_types=1);

namespace App\Web\FundDistribution;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * ClaimTypeMappingService
 *
 * Responsible for fetching, parsing, and validating standard claims mappings.
 * Prevents hardcoded mapping clutter inside the main service logic.
 */
class ClaimTypeMappingService
{
    /**
     * Load valid component definition for specified claim slug.
     *
     * @param string $slug The slug reference for the claim type.
     * @return array Hydrated mapping details.
     * @throws Exception if configuration missing or malformed.
     */
    public function getMapping(string $slug): array
    {
        if (empty($slug)) {
            Log::error("Mapping attempted with empty claim slug.");
            throw new Exception("Claim type slug identifier must not be empty.");
        }

        // Load dynamically from standard Laravel config
        $mapping = config("claim_distribution_mapping.{$slug}");

        if (is_null($mapping)) {
            Log::warning("Claim mapping missing in config for slug: {$slug}");
            throw new Exception("Missing distribution mapping for claim type: {$slug}. Please configure system.");
        }

        // Robust structural validation before releasing to distribution engine
        if (empty($mapping['major_component_id'])) {
            Log::critical("Malformed mapping config for {$slug}: major_component_id missing.");
            throw new Exception("System Mapping Incomplete: major_component_id is required for automated deduction.");
        }

        // Soft handle the sub_component_id, converting typical placeholders
        $subComponent = $mapping['sub_component_id'] ?? null;
        if ($subComponent === 'TODO-REPLACE-WITH-ACTUAL-UUID') {
            $subComponent = null; 
        }

        return [
            'major_component_id' => $mapping['major_component_id'],
            'sub_component_id'   => $subComponent,
        ];
    }
}
