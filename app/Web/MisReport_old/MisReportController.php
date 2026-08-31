<?php

declare(strict_types=1);

namespace App\Web\MisReport;
use App\Web\MisReport\MisReportService;
use DB;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;
use App\Web\ApplicationWorkflow\ApplicationWorkflowService;
use App\Web\SNP\SNPService;
use App\Web\BNP\BNPService;
use App\Web\Claim\ClaimService;
use Illuminate\Http\JsonResponse;
use App\Domain\NetworkProvider\GetNetworkProviderDetailsAction;
use App\Domain\NetworkProvider\GetNetworkProviderListAction;
use App\Domain\Batch\BatchStatus;
use App\Domain\Claim\ClaimStatus;
use App\Web\MisReport\FinalStatus;
use App\Domain\NetworkProvider\NetworkProviderStatus;
use App\Web\Workshop\{WorkshopService,Workshop};



class MisReportController extends ClientController

{
    private static string $module = 'mis-reports-msme';

    // private static string $module = 'msme-registration';

    public function __construct(
        private MisReportService $service,
        private SNPService $snpService,
        private BNPService $bnpService,
        private ClaimService $claimService,
        private NpService $npService,
        private WorkshopService $workshopService,
    ) {
    }

    public function index(): View
    {
        guard('mis-msme-registration-report-view');

        return view('mis_report.index')
            ->with('title', 'MSME Registartion')
            ->with('lists', (object) $this->service->getDropdownList());
    }

    public function getMsmeList()
    {
        guard('mis-msme-registration-report-view');

        return $this->success($this->service->getMsmeList());
    }

    public function getMsmeDetails(string $id): View
    {
        guard('mis-msme-registration-report-view');
        $title = __('MSME Details');
        $module_url = 'catalogue-creation-report';
        $detail = (array) $this->service->getMsmeDetails($id);
        return view('mis_report.details', compact('detail', 'title', 'module_url'));
    }

    public function registeredSnp(): View
    {
        guard('mis-snp-registration-report-view');
        $title = __('SNP Registration List');

        return view('mis_report.snplist', compact('title'));
    }

    public function getRegisteredList()
    {
        guard('mis-snp-registration-report-view');
        return $this->success($this->snpService->getSnpList());
    }


    public function getSnpDetail(string $id): View
    {
        $networkProviderId = DB::table('team_snp_scheme')->where('id', $id)->value('network_provider_id');
        // guard('mis-snp-registration-report-view');

        

        $title = __('SNP Detail');
        // $snpDetail = (array) $this->snpService->getSnpDetail($id);
        $isReviewed = false;
        if ($networkProviderId) {
            $networkProvider = app(GetNetworkProviderDetailsAction::class)->execute($networkProviderId);
        }
        else {
           $networkProvider = (array) $this->snpService->getSnpDetail($id); 
        }
       
        //$isForwarded = app(ApplicationWorkflowService::class)->isForwarded($snpDetail['id']);
        return view('snp.details', compact('title', 'id', 'networkProvider', 'isReviewed'));
    }


    public function verifiedSnp(): View
    {
        guard('mis-snp-verification-status-view');
        $title = __('Verified SNP List');

        return view('mis_report.verified-snp-list', compact('title'));
    }


    public function registeredBnp(): View
    {
        guard('mis-bnp-registration-report-view');
        $title = __('BNP Registration List');

        return view('mis_report.bnplist', compact('title'));
    }

    public function getRegisteredBnpList()
    {
        guard('mis-bnp-registration-report-view');
        return $this->success($this->bnpService->getBnpList());
    }

    public function getBnpDetail(string $id): View
    {
        guard('mis-bnp-registration-report-view');

        //$networkProviderId = DB::table('team_snp_scheme')->where('id', $id)->value('network_provider_id');
        $isReviewed = false;
   
        $networkProvider = app(GetNetworkProviderDetailsAction::class)->execute($id);

        

        $title = __('BNP Detail');
        return view('bnp.details', compact('title', 'id', 'networkProvider'));
    }


    public function verifiedBnp(): View
    {
        guard('mis-bnp-verification-status-view');
        $title = __('Verified BNP List');

        return view('mis_report.verified-bnp-list', compact('title'));
    }

    public function onboardedMSEs(): View
    {
        guard('mis-msme-snp-mapping-report-view');
        return view('mis_report.onboardedmse')
            ->with('title', 'MSEs- SNP Mapped MSE’s')
            ->with('lists', (object) $this->service->getDropdownList());
    }

    public function onboardedMSEsList()
    {
        guard('mis-msme-snp-mapping-report-view');
        return $this->success($this->service->getMsmeList($onboarded = 1));
    }

    public function transactedLiveMSEs(): View
    {
        guard('mis-msme-onboarding-ondc-report-view');
        return view('mis_report.transactedlivemse')
            ->with('title', 'MSEs Onboarded on ONDC')
            ->with('lists', (object) $this->service->getDropdownList());
    }

    public function transactedLiveMSEsList()
    {
        guard('mis-msme-onboarding-ondc-report-view');
        return $this->success($this->service->getMsmeList($onboarded = 2));
    }

    public function catalogueReadyMSEs(): View
    {
        guard('mis-catalogue-creation-report-view');
        return view('mis_report.catalogueready')
            ->with('title', 'Catalogued MSEs')
            ->with('lists', (object) $this->service->getDropdownList());
    }

    public function catalogueReadyMSEsList()
    {
        guard('mis-catalogue-creation-report-view');
        return $this->success($this->service->getMsmeList($onboarded = 3));
    }


    public function logisticClaim(): View
    {
        // dd('testing');
        $module_url = 'transportation-logistics-claim-report';
        $status = ['' => 'Select'];
        foreach (FinalStatus::cases() as $case) {
            $status[$case->value] = $case->label();
        }
        $claimSlug = 'claim-for-transportation-and-logistic';
        return view('mis_report.packaging_report', compact('claimSlug','status','module_url'))->with('title', 'Claim for Logistics and Transportation Report');
    }

    public function packagingClaim(): View
    {
        // dd('testing');
        $module_url = 'packaging-claim-report';
        $status = ['' => 'Select'];
        foreach (FinalStatus::cases() as $case) {
            $status[$case->value] = $case->label();
        }
        $claimSlug = 'claim-for-packaging';
        return view('mis_report.packaging_report', compact('claimSlug','status','module_url'))->with('title', 'Claim for Packaging Support Report');
    }

    public function catlogueReport(): View
    {

        $claimSlug = 'claim-for-catalogue-creation';
        $module_url = static::$module;
        $status = ['' => 'Select'];
        foreach (FinalStatus::cases() as $case) {
            $status[$case->value] = $case->label();
        }

        return view('mis_report.incentive_report', compact('claimSlug','status','module_url'))->with('title', 'Claim for Catalogue Creation Report');
    }

    public function accountsClaim(): View
    {
        $claimSlug = 'claim-for-accounts-management';
        $module_url = 'account-management-support-claim-report';
        $status = ['' => 'Select'];
        foreach (FinalStatus::cases() as $case) {
            $status[$case->value] = $case->label();
        }

        return view('mis_report.incentive_report', compact('claimSlug','status','module_url'))->with('title', 'Claim for Accounts Management');
    }

    public function getClaimTypeId($claimSlug)
    {
        return DB::table('claim_types')
            ->where('slug', $claimSlug)
            ->value('id');
    }

    public function claimList($claim_types): JsonResponse
    {
        //dd($claim_types);
        if($claim_types == 'claim-for-transportation-and-logistic'){
            return $this->success($this->service->getClaimReportLogistics($claim_types));
        }else{
            return $this->success($this->service->getClaimReport($claim_types));
        }
        
    }

    public function npRegisterReport()
    {

        $title = __('NP Register Report');
        $roles = role_list();
        $roles = array_intersect($roles, [
            "Buyer Network Participant (BNP)",
            "Seller Network Participant (SNP)",
            "Logistics Service Provider (LSP)"
        ]);
        $status =  [
                '' => 'Select',
                NetworkProviderStatus::PENDING->value => 'Pending',
                NetworkProviderStatus::APPROVE->value => 'Approved',
                NetworkProviderStatus::REJECT->value  => 'Rejected',
                NetworkProviderStatus::REVERT->value  => 'Reverted',
            ];
        return view('mis_report.np-list', compact('title','roles','status'));
    }


    public function getNpList()
    {
        $data = $this->npService->execute();
        return $this->success(data: $data);
    }

    public function npShow(string $id)
    {
        $title = __('Network Provider Details');

        $isReviewed = false;
        $module_url = 'np-register-report';
        $networkProvider = app(GetNetworkProviderDetailsAction::class)->execute($id);

        return view('mis_report.np-details', compact('title', 'networkProvider', 'isReviewed','module_url'));
    }

    public function workshopReport()
    {
        $title = __('Workshop Creation Report');
        $module_url = 'workshop-creation-report';

        return view('mis_report.workshop_report', compact('title','module_url'));
    }

    public function workshopView($id)
    {
        $title = __('View Event');
        $module_url = 'workshop-creation-report';
        $event = Workshop::find($id);
        $roleIds = is_string($event->event_for) ? json_decode($event->event_for, true) : $event->event_for;
        $event_for = \DB::table('roles')->whereIn('id', $roleIds)->pluck('name')->implode(', ');
        $uploadedImage = $this->workshopService->getUploadImageData($id);
        $scheduleData = $this->workshopService->getScheduleSummary($event->schedules);
        if (!$event) {
            return redirect()->back()->with('error', 'Event not found.');
        }
        /*if(hasRole('msme')){
            return view('workshop.card_detail_view', compact('event','title','uploadedImage','event_for','scheduleData'));
        }else{
            return view('workshop.show', compact('event','title','uploadedImage','event_for','module_url'));
        }*/

        return view('workshop.show', compact('event','title','uploadedImage','event_for','module_url'));
        
    }

    /*public function getWorkshopReportData()
    {
        return $this->success($this->workshopService->getList());
    }*/






    
}