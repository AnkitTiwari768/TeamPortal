<?php

declare(strict_types=1);

namespace App\Web\MisReport;

use DB;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ClientController;

use App\Web\MisReport\MisDemandGenerationService;
use App\Web\SNP\SNPService;
use App\Web\BNP\BNPService;
use App\Web\Claim\ClaimService;
use App\Web\NP\NpService;
use App\Web\Workshop\WorkshopService;
use App\Web\MisReport\FinalStatus;

use App\Domain\NetworkProvider\NetworkProviderStatus;

class MisDemandGenerationController extends ClientController
{
    private static string $module = 'mis-reports-msme';

    public function __construct(private MisDemandGenerationService $service,) {
    }

    /* =====================================
       SHOW REPORT PAGE
    ===================================== */
    public function showDemandGenerationReport(): View
    {
        $title = __('MIS Demand Generation Report');
        $status = ['' => 'Select Status'];

        foreach (FinalStatus::cases() as $case) {
            $status[$case->value] = $case->label();
        }
        return view('mis_report.mis_reports_demand_generation', compact('title', 'status'));
    }


    /* =====================================
       DATATABLE LIST
    ===================================== */
    public function getDemandGenerationList(): JsonResponse
    {
        $claim_types = 'claim-for-demand-generation'; 
        $data = $this->service->getDemandGenerationList($claim_types);
        return $this->success($data);
    }

}