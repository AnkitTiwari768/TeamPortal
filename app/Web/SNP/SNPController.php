<?php 

declare(strict_types=1);

namespace App\Web\SNP;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;
use App\Web\ApplicationWorkflow\ApplicationWorkflowService;

class SNPController extends ClientController
{
    private static string $module = 'snp.index';

    public function __construct(private SNPService $service){}

    public function unverifiedSnp(): View
    {
        guard('unverified-snp-view');
        $title = __('SNP Awaiting ONDC Validation');
        
        return view('snp.unverified-snp-list', compact('title'));  
    }

    public function getUnverifiedList()
    {     
        guard('unverified-snp-view');
        return $this->success($this->service->getSnp($activeStatus = 0));
    }

    public function verifiedSnp(): View
    {
        guard('verified-snp-view');
        $title = __('Verified SNP List');
        
        return view('snp.verified-snp-list', compact('title'));  
    }

    public function getVerifiedList()
    {   
        guard('verified-snp-view');
        return $this->success($this->service->getSnp($activeStatus = 1));
    }

    public function getSnpDetail(string $id): View
    {     
        $title = __('SNP Detail');    
        $snpDetail = (array) $this->service->getSnpDetail($id);
        $isForwarded = app(ApplicationWorkflowService::class)->isForwarded($snpDetail['id']);
        //dd($snpDetail);
		return view('snp.details', compact('title', 'id' ,'snpDetail','isForwarded')); 
    }

    public function migratedSnp(): View
    {     
        $title = __('Migrated SNP List');

		return view('snp.migrated_snp_list', compact('title')); 
    }

    public function getMigratedSnpList()
    {     
        $title = __('Migrated SNP List');

        return $this->success($this->service->getMigrateSnpList());
    }

    public function migratedSnpView(string $id)
    {
        $title = __('Migrated SNP Details');

        $isReviewed = false;

        $networkProvider = $this->service->getMigrateSnpview($id);
        //dd($networkProvider);

        return view('snp.migrated_snp_details', compact('title', 'networkProvider', 'isReviewed'));
    }

}