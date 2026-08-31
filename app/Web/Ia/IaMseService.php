<?php 
declare(strict_types=1);
namespace App\Web\Ia;
use App\Traits\DataTable;
use App\Core\BaseService;
use DB;
use App\Web\Msme\MsmeResource;
use App\Http\Services\CommonService;
use App\Traits\HasAttribute;

class IaMseService extends BaseService
{
    use DataTable,HasAttribute;
    protected array $columns = [
        1 => 'team_id',
        2 => 'udyam_no',
        3 => 'mobile',
		4 => 'email',
		5 => 'entrepreneur_name',
		6 => 'enterprise_name',
		7=> 'organisation_type',
		8=>'created_at'
    ];


    public function getRegisteredMsmeList()
    {
        
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
        
        $startDate = $filters['from_date'] ?? null;
        $endDate = $filters['to_date'] ?? null;
        $search ??= $this->escape_special_characters($search);

        $query = DB::table('team_msme_schemes as ms')
        ->select('ms.id','ms.team_id', 'ms.udyam_no', 'ms.mobile', 'ms.email', 'ms.entrepreneur_name', 'ms.enterprise_name','ms.organisation_type','ms.msme_classification','ms.social_category','ms.created_at','ms.select_snp','s.name as state_name')
        ->leftjoin('states as s','s.id','=','ms.state_id')
		->where('ms.select_snp',0)
		->where('ms.created_by',AuthId());
        
       
        if ($startDate) {
            $startDate = date('Y-m-d', strtotime($startDate));
            $query->where('ms.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $endDate = (new \DateTime($endDate))->modify('+1 days')->format('Y-m-d');
            $query->where('ms.created_at', '<=', $endDate);
        }
		
		if (isset($filters['ondc_transaction_type_id']) && !empty($filters['ondc_transaction_type_id'])) {
            $query->where('ondc_transaction_type_id', $filters['ondc_transaction_type_id']);
        }
		
		if (!empty($filters['product_category_id']) && is_array($filters['product_category_id'])) {
			foreach ($filters['product_category_id'] as $categoryId) {
				$query->orWhereJsonContains('product_category_id', $categoryId);
			}
		}

        if ($search) 
        {
            $query->where(function($query) use ($search) {
                $query->where('ms.team_id','like', "%$search%")
                    ->orwhere('ms.udyam_no','like', "%$search%")
                    ->orWhere('ms.mobile','like', "%$search%")
					->orWhere('ms.email','like', "%$search%")
					->orWhere('ms.entrepreneur_name','like', "%$search%")
					->orWhere('ms.enterprise_name','like', "%$search%")
                    ->orWhere('ms.organisation_type','like', "%$search%")
					->orWhere('ms.created_at','like', "%$search%");
            });
        }
     

            if ($search) {
                $query->where(function($query) use ($search) {
                    $query->where('ms.team_id','like', "%$search%")
                        ->orWhere('ms.udyam_no','like', "%$search%")
                        ->orWhere('ms.mobile','like', "%$search%")
                        ->orWhere('ms.email','like', "%$search%")
                        ->orWhere('ms.entrepreneur_name','like', "%$search%")
                        ->orWhere('ms.enterprise_name','like', "%$search%")
                        ->orWhere('ms.organisation_type','like', "%$search%")
                        ->orWhere('ms.created_at','like', "%$search%");
                });
            }

        
		
        //$query->orderBy($order, $dir); 
        $query->orderBy('ms.created_at', 'desc'); 
        
        if ($page) 
        {
            return $this->getDataTableResult(
                MsmeResource::collection($query->paginate($limit))
            );
        }
        return $query->get();
        //return MsmeResource::collection($query->get());
    }


    public function getDropdownList()
    {
        $commonService = new CommonService();
        return [
		 'ondc_types'=>$this->listOf('types-of-transactions-preferred-on-ondc'),
		 'sub_domains' => $commonService->getDropdownNewList('sub_domains','status','ASC','name',array('id','name')),
        ];
    }

}