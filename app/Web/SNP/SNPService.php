<?php 
declare(strict_types=1);
namespace App\Web\SNP;
use App\Traits\DataTable;
use App\Core\BaseService;
use DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Domain\NetworkProvider\NetworkProviderStatus;

class SnpService extends BaseService
{
    protected array $columns = [
        1 => 'first_name',
        2 => 'organization_id',
        3 => 'organization_name',
        4 => 'email',
        5 => 'mobile'
    ];

    public function getSnp($status)
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        //$roleId = isset($filters['role_id']) ? $filters['role_id'] : null;
        $startDate = $filters['from_date'] ?? null;
        $endDate = $filters['to_date'] ?? null;
        
        $search ??= $this->escape_special_characters($search);

        $query = DB::table('team_snp_scheme as s')
        ->select('s.id','s.snp_id', 's.status as snp_status','s.review_status','s.organization_id', 's.organization_name', 's.bank_name', 's.ifsc_code', 's.account_no','u.email', 'u.mobile', 'u.first_name')
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


        /*if (isset($filters['status']) && $filters['status'] === '1') {
            $query->where('status', $filters['status']);
        }

         if (isset($filters['status']) && $filters['status'] === '0') {
            $query->where('status', $filters['status']);
        }*/

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
                SNPResource::collection($query->paginate($limit))
            );
        }

        return SNPResource::collection($query->get());
    }
    public function getSnpList($status = null)
    {
            [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

            $startDate = $filters['from_date'] ?? null;
            $endDate = $filters['to_date'] ?? null;
        
            //$search = $this->escape_special_characters($search ?? '');

            $query = DB::table('team_snp_scheme as s')
                ->select(
                    's.id',
                    's.snp_id',
                    's.status as snp_status',
                    's.review_status',
                    's.organization_id',
                    's.organization_name',
                    's.bank_name',
                    's.ifsc_code',
                    's.account_no',
                    'u.email',
                    'u.mobile',
                    'u.first_name'
                )
                ->leftJoin('network_providers as np', 's.network_provider_id', '=', 'np.id')
                ->leftJoin('users as u', 'u.id', '=', 's.user_id');

            if($status){
                $query->where('s.status', $status);
            }

            // Date filters
            if ($startDate) {
                $query->where('s.created_at', '>=', date('Y-m-d', strtotime($startDate)));
            }

            if ($endDate) {
                $query->where('s.created_at', '<=', (new \DateTime($endDate))->modify('+1 days')->format('Y-m-d'));
            }

            if (!empty($filters['review_status'])) {
                if ($filters['review_status'] === 0 || $filters['review_status'] === '0') {
                    // review_status is zero, so check for NULL or 0
                    $query->where(function($q) {
                        $q->whereNull('s.review_status')
                        ->orWhere('s.review_status', 0);
                    });
                } 
                else {
                
                    $query->where('s.review_status', $filters['review_status']);
                }
            }
            else {
                    if ($filters['review_status'] === 0 || $filters['review_status'] === '0') {
                    // review_status is zero, so check for NULL or 0
                    $query ->orWhere('s.review_status', null);
                }
                
                    
            }

                // Search filter
            if (isset($search) && !empty($search) && $this->escape_special_characters($search)) {
                    //dd($search);
                    $query->where(function($q) use ($search) {
                        $q->where('u.first_name', 'like', "%$search%")
                        ->orWhere('s.snp_id', 'like', "%{$search}%")
                        ->orWhere('s.organization_id', 'like', "%$search%")
                        ->orWhere('s.organization_name', 'like', "%$search%")
                        ->orWhere('u.email', 'like', "%$search%")
                        ->orWhere('u.mobile', 'like', "%$search%");
                    });
            }
            
            // Sorting
            $query->orderBy($order, $dir);

            // Return result
            if ($page) {
                return $this->getDataTableResult(
                    SNPResource::collection($query->paginate($limit))
                );
            }

            return SNPResource::collection($query->get());
    }

    public function getMigrateSnpList()
    {

       [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        //$roleId = isset($filters['role_id']) ? $filters['role_id'] : null;
        $startDate = $filters['from_date'] ?? null;
        $endDate = $filters['to_date'] ?? null;
        
        $search ??= $this->escape_special_characters($search);

        $query = DB::table('team_snp_scheme as s')
        ->select('s.id','s.snp_id', 's.status as snp_status','s.review_status','s.organization_id', 's.organization_name', 's.bank_name', 's.ifsc_code', 's.account_no','u.email', 'u.mobile', 'u.first_name','u.mobile','s.created_at','r.name as role_name')
        ->join('users as u', 'u.id', '=', 's.user_id', 'left')
        ->join('user_roles as ur', 'ur.user_id', '=', 's.user_id')
        ->join('roles as r', 'r.id', '=', 'ur.role_id');
        
        $query->where('s.status', NetworkProviderStatus::APPROVE->value)->whereNotNull('s.ref_id')->whereNull('is_profile_updated');


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
                MigratedSnpListResource::collection($query->paginate($limit))
            );
        }

        return MigratedSnpListResource::collection($query->get());
    }

    public function getMigrateSnpview(string $id)
    {
        $query = DB::table('team_snp_scheme as s')
            ->select(
                's.id','s.snp_id','s.status as snp_status','s.review_status','s.designation',
                's.organization_id','s.organization_name',
                's.bank_name','s.ifsc_code','s.account_no',
                's.designation','s.created_at',
                's.sub_domain','s.transaction_type','s.state_id',
                'u.email','u.mobile','u.first_name','s.short_description','s.commercial_model','r.name as role_name'
            )
            ->leftJoin('users as u', 'u.id', '=', 's.user_id')
            ->leftJoin('user_roles as ur', 'ur.user_id', '=', 's.user_id')
            ->leftJoin('roles as r', 'r.id', '=', 'ur.role_id')
            ->where('s.status', NetworkProviderStatus::APPROVE->value)
            ->whereNotNull('s.ref_id')
            ->where('s.id', $id);

        $data = $query->first();

        if (!$data) {
            return null;
        }

        $types = json_decode($data->transaction_type, true);
        if (!empty($types)) {
            $data->transaction_type = DB::table('attribute_values')
                ->whereIn('id', $types)
                ->pluck('attribute_value')
                ->implode(', ');
        }

        $sub = json_decode($data->sub_domain, true);
        if (!empty($sub)) {
            $data->sub_domain = DB::table('sub_domains')
                ->whereIn('id', $sub)
                ->pluck('name')
                ->implode(', ');
        }

        $states = !empty($data->state_id) ? json_decode($data->state_id, true) : [];
        if (!empty($states)) {
            $data->state_id = DB::table('states')
                ->whereIn('id', $states)
                ->pluck('name')
                ->implode(', ');
        }

        return $data;
    }

}