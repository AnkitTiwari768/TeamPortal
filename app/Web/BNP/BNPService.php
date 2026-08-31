<?php 
declare(strict_types=1);
namespace App\Web\BNP;
use App\Traits\DataTable;
use App\Core\BaseService;
use DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BnpService extends BaseService
{
    protected array $columns = [
        1 => 'first_name',
        2 => 'organization_id',
        3 => 'organization_name',
        4 => 'email',
        5 => 'mobile'
    ];

    public function getBnp($status)
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        //$roleId = isset($filters['role_id']) ? $filters['role_id'] : null;
        $startDate = $filters['from_date'] ?? null;
        $endDate = $filters['to_date'] ?? null;
        
        $search ??= $this->escape_special_characters($search);

        $query = DB::table('team_bnp_scheme as s')
        ->select('s.id','s.bnp_id', 's.status as bnp_status','s.review_status','s.organization_id', 's.organization_name', 's.bank_name', 's.ifsc_code', 's.account_no','u.email', 'u.mobile', 'u.first_name')
        ->join('users as u', 'u.id', '=', 's.user_id', 'left');
        
        $query->where('s.status', $status);

        if ($startDate) {
            $startDate = date('Y-m-d', strtotime($startDate));
            $query->where('s.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $endDate = (new \DateTime($endDate))->modify('+1 days')->format('Y-m-d');
            $query->where('s.created_at', '<=', $endDate);
        }

        if ($search) 
        {
            $query->where(function($query) use ($search) {
                $query->where('u.first_name','like', "%$search%")
                    ->orWhere('s.organization_id','like', "%$search%")
                    ->orWhere('s.organization_name','like', "%$search%")
			        ->orWhere('u.email','like', "%$search%")  
			        ->orWhere('u.mobile','like', "%$search%");    
            });
        }

        $query->orderBy($order, $dir); 
        
        if ($page) 
        {
            return $this->getDataTableResult(
                BNPResource::collection($query->paginate($limit))
            );
        }

        return BNPResource::collection($query->get());
    }
    // public function getBnpList($status = null)
    // {
    //     [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

    //     //$roleId = isset($filters['role_id']) ? $filters['role_id'] : null;
    //     $startDate = $filters['from_date'] ?? null;
    //     $endDate = $filters['to_date'] ?? null;
        
    //     $search ??= $this->escape_special_characters($search);

    //     $query = DB::table('team_bnp_scheme as s')
    //     ->select('s.id','s.bnp_id', 's.status as bnp_status','s.review_status','s.organization_id', 's.organization_name', 's.bank_name', 's.ifsc_code', 's.account_no','u.email', 'u.mobile', 'u.first_name')
    //     ->join('users as u', 'u.id', '=', 's.user_id', 'left');
    //     if ($status)
    //     {
    //         $query->where('s.status', $status);
    //     }
    //     if (!empty($filters['review_status'])) {
    //         if ($filters['review_status'] === 0 || $filters['review_status'] === '0') {
    //             // review_status is zero, so check for NULL or 0
    //             $query->where(function($q) {
    //                 $q->whereNull('s.review_status')
    //                 ->orWhere('s.review_status', 0);
    //             });
    //         } else {
    //             // review_status is some other value, filter by it
    //             $query->where('s.review_status', $filters['review_status']);
    //         }
    //     }

    //     if ($startDate) {
    //         $startDate = date('Y-m-d', strtotime($startDate));
    //         $query->where('s.created_at', '>=', $startDate);
    //     }

    //     if ($endDate) {
    //         $endDate = (new \DateTime($endDate))->modify('+1 days')->format('Y-m-d');
    //         $query->where('s.created_at', '<=', $endDate);
    //     }
    //     if (isset($search) && !empty($search) && $this->escape_special_characters($search)) {

    //         $query->where(function($query) use ($search) {
    //             $query->where('u.first_name','like', "%$search%")
    //                 ->orWhere('s.organization_id','like', "%$search%")
    //                 ->orWhere('s.organization_name','like', "%$search%")
	// 		        ->orWhere('u.email','like', "%$search%")  
	// 		        ->orWhere('u.mobile','like', "%$search%");    
    //         });
    //     }

    //     $query->orderBy($order, $dir); 
        
    //     if ($page) 
    //     {
    //         return $this->getDataTableResult(
    //             BNPResource::collection($query->paginate($limit))
    //         );
    //     }

    //     return BNPResource::collection($query->get());
    // }


    public function getBnpList($status = null)
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $startDate = $filters['from_date'] ?? null;
        $endDate   = $filters['to_date'] ?? null;

        

        $query = DB::table('network_providers as np')
        ->select(
            'np.id',
            'np.np_team_id as bnp_id',
            'np.status as bnp_status',
            'np.review_remarks as review_status',
            'np.organization_id',
            'np.organization_name',
            DB::raw("JSON_UNQUOTE(JSON_EXTRACT(np.bank_details,'$.bank_name')) as bank_name"),
            DB::raw("JSON_UNQUOTE(JSON_EXTRACT(np.bank_details,'$.ifsc_code')) as ifsc_code"),
            DB::raw("JSON_UNQUOTE(JSON_EXTRACT(np.bank_details,'$.account_number')) as account_no"),
            'np.email',
            DB::raw("JSON_UNQUOTE(JSON_EXTRACT(np.authorized_person_details,'$[0].phone')) as mobile"),
            DB::raw("JSON_UNQUOTE(JSON_EXTRACT(np.authorized_person_details,'$[0].name')) as first_name")
        )
        ->where('np.role_names','LIKE','%BNP%');

        if ($status) {
            $query->where('np.status', $status);
        }

        // date filter
        if ($startDate) {
            $query->whereDate('np.created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('np.created_at', '<=', $endDate);
        }

        // search
        if (isset($search) && !empty($search) && $this->escape_special_characters($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('np.organization_name', 'like', "%$search%")
                ->orWhere('np.organization_id', 'like', "%$search%")
                ->orWhere('np.email', 'like', "%$search%")
                ->orWhere('np.np_team_id', 'like', "%$search%")
                ->orWhereRaw("JSON_EXTRACT(np.authorized_person_details,'$[0].name') LIKE ?", ["%$search%"])
                ->orWhereRaw("JSON_EXTRACT(np.authorized_person_details,'$[0].phone') LIKE ?", ["%$search%"]);
            });
        }

        $query->orderBy($order ?? 'np.created_at', $dir ?? 'desc');

        if ($page) {
            return $this->getDataTableResult(
                BNPResource::collection($query->paginate($limit))
            );
        }

        return BNPResource::collection($query->get());
    }

    public function getBnpDetail($id)
    {  

        $detail = DB::table('team_bnp_scheme as bnp')
            ->join('users as u', 'u.id', '=', 'bnp.user_id')
            ->select('bnp.*','u.email','u.mobile')
            ->where('bnp.id', $id)
            ->first();                  
       
        return $detail;
    }

}