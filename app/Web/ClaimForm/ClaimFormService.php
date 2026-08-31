<?php

declare(strict_types=1);

namespace App\Web\ClaimForm;

use App\Web\Claim\ClaimService;
use Illuminate\Support\Facades\DB;

class ClaimFormService
{
    public function __construct(
        private ClaimService $claimService
    ) {}

    public function getClaimFormDetails(string $slug): array
    {
        $data = [
            'claimSlug' => $slug,
            'claim_types' => $this->getClaimTypes($slug),
            'claimTypeIdValue' => $this->getClaimTypeId($slug),
            'documentCategory' => $this->getDocumentCategoryId(),
            'documentDeclarationCategory' => $this->getDocumentDeclarationCategoryId(),
            'declaration' => $this->claimService->getLowestClaimWorkflowDeclaration($slug),
        ];

        $tabs = $this->claimService->getClaimsDataTableDetails($slug);
        $tabs = collect($tabs)->firstWhere('claim_type_slug', $slug);

        $data['tabs'] = $tabs;

        return $data;
    }

    public function getClaimTypes($claimSlug)
    {
        return DB::table('claim_types')
            ->where('slug', $claimSlug)
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getClaimTypeId($claimSlug)
    {
        return DB::table('claim_types')
            ->where('slug', $claimSlug)
            ->value('id');
    }


    public function getDocumentCategoryId()
    {
        return DB::table('document_categories')->where('slug', 'ca_certificate')->first();
    }

    public function getDocumentDeclarationCategoryId()
    {
        return DB::table('document_categories')->where('slug', 'declaration-ondc')->first();
    }
}
