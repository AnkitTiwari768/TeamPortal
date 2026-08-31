<?php

declare(strict_types=1);

namespace App\Domain\DemandGeneration;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DemandGenerationClaimController
{
    public function __construct(private DemandGenerationClaimService $claimService) {}

    public function create()
    {
        $title = __('Demand Generation Incentive Claim');

        $networkProvider = $this->claimService->getNetworkProviderDetailsByUserID(authId());

        $productCategories = $this->claimService->getProductCategories();

        $declarationContent = $this->claimService->getDeclarationContent('demand-generation-incentive-claim');

        return view('demand-generation.form', compact(
            'title',
            'networkProvider',
            'productCategories',
            'declarationContent'
        ));
    }

    /**
     * Store a new claim
     */
    public function store(DemandGenerationClaimRequest $request, CreateDemandGenerationClaimAction $action): JsonResponse
    {
        $result = $action->execute(
            $request->validated(),
            Authid(),
            $request->file('low_aov_excel'),
            $request->file('high_aov_excel')
        );

        if ($result['status']) {
            return response()->json([
                'status' => true,
                'message' => 'Claim submitted successfully!',
                'data' => $result['data']
            ], 201);
        }

        return response()->json([
            'status' => false,
            'message' => $result['message'],
            'errors' => $result['errors']
        ], 422);
    }

    /**
     * Get active phase for BNP
     */
    // public function getActivePhase(string $networkProviderId): JsonResponse
    // {
    //     $phase = $this->claimService->getActivePhase($networkProviderId);

    //     return response()->json([
    //         'status' => true,
    //         'data' => $phase
    //     ]);
    // }

    /**
 * Download Excel template
 */
    // public function downloadTemplate(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    // {
    //     $path = storage_path('templates/demand_generation_claim_template.xlsx');

    //     return response()->download($path, 'demand_generation_claim_template.xlsx');
    // }
}
