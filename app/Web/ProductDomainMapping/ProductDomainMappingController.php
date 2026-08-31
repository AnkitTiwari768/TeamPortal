<?php 

declare(strict_types=1);

namespace App\Web\ProductDomainMapping;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Observers\AuditTrailLog;
use App\Http\Controllers\ClientController;
use App\Core\BaseRequest;

class ProductDomainMappingController extends ClientController
{
    
	public function __construct(private ProductDomainMappingService $service)
    {
        $this->service = $service;
    }
	
    public function getProductDomainOndcID(Request $request)
    {
        //dd($request->all());
        $domain_type = $request->id ?? [];

        $data = $this->service->getDomainOndcId($domain_type);
        //dd($data);
        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }

}