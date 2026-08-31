<?php

declare(strict_types=1);

namespace App\Web\ClaimForm;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\ClientController;
use App\Web\Claim\ClaimService;
use DB;

class LogisticTransportClaimController extends ClientController
{
    public function __construct(private ClaimService $service)
    {
    }

    public function logisticClaim(): View
    {
        $claimSlug = 'claim-for-transportation-and-logistic';
        $claim_types = $this->getClaimTypes($claimSlug);
        $claimTypeIdValue = $this->getClaimTypeId($claimSlug);

        $tabs = $this->service->getClaimsDataTableDetails($claimSlug);
        $tabs = collect($tabs)->firstWhere('claim_type_slug', $claimSlug);

        $declaration = $this->service->getLowestClaimWorkflowDeclaration($claimSlug);
        $documentDeclarationCategory = $this->getDocumentDeclarationCategoryId();

        // Changed view to the new logistic-claim.index
        // return view('logistic-claim.index', compact('claimSlug', 'claim_types', 'claimTypeIdValue', 'tabs', 'declaration', 'documentDeclarationCategory'))
        //     ->with('title', 'Claim for Transportation and Logistic');
        return view('claim-form.index', compact('claimSlug', 'claim_types', 'claimTypeIdValue', 'tabs', 'declaration', 'documentDeclarationCategory'))
            ->with('title', 'Claim for Transportation and Logistic');
    }

    private function getClaimTypes($claimSlug)
    {
        return DB::table('claim_types')
            ->where('slug', $claimSlug)
            ->pluck('name', 'id')
            ->toArray();
    }

    private function getClaimTypeId($claimSlug)
    {
        return DB::table('claim_types')
            ->where('slug', $claimSlug)
            ->value('id');
    }

    private function getDocumentDeclarationCategoryId()
    {
        return DB::table('document_categories')->where('slug', 'declaration-ondc')->first();
    }
}
