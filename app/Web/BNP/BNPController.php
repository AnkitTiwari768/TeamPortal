<?php 

declare(strict_types=1);

namespace App\Web\BNP;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Web\ApplicationWorkflow\ApplicationWorkflowService;
use App\Http\Controllers\ClientController;

class BNPController extends ClientController
{
    private static string $module = 'bnp.index';

    public function __construct(private BNPService $service){}

    public function unverifiedBnp(): View
    {
        guard('unverified-bnp-view');
        $title = __('BNP Awaiting ONDC Validation');
        
        return view('bnp.unverified-bnp-list', compact('title'));  
    }

    public function getUnverifiedList()
    {     
        guard('unverified-bnp-view');
        return $this->success($this->service->getBnp($activeStatus = 0));
    }

    public function verifiedBnp(): View
    {
        guard('verified-bnp-view');
        $title = __('Verified BNP List');
        
        return view('bnp.verified-bnp-list', compact('title'));  
    }

    public function getVerifiedList()
    {   
        guard('verified-bnp-view');
        return $this->success($this->service->getBnp($activeStatus = 1));
    }

    public function getBnpDetail(string $id): View
    {     
        $title = __('BNP Detail');    
        $bnpDetail = (array) $this->service->getBnpDetail($id);
        $isForwarded = false;
        $isForwarded = app(ApplicationWorkflowService::class)->isForwarded($bnpDetail['id']);
        //dd($bnpDetail);
		return view('bnp.details', compact('title', 'id' ,'bnpDetail','isForwarded')); 
    }

}