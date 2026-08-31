<?php 

declare(strict_types=1);
namespace App\Http\Api\V1\AuditTrail;
use App\Traits\DataTable;
use DB;

class AuditTrailService 
{
    use DataTable;

    protected array $columns = [ 
        1 => 'module_name',
        2 => 'activity_type',
        3 => 'first_name',
        4 => 'email',
        5 => 'last_login',
        6 => 'created_at',
        7 => 'ip_address',
    ]; 
    public function getLogs()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $module_name= isset($filters['module_name']) ? $filters['module_name'] : null;
        $activity_type = isset($filters['activity_type']) ? $filters['activity_type'] : null;
        
        $search ??= $this->escape_special_characters($search);

        $query = AuditTrail::select('audit_trail.*', 'users.first_name','users.middle_name','users.last_name','users.email')
            ->join('users', 'audit_trail.user_id', '=', 'users.id');
        
        if ($module_name) 
        {
            $query->where('module_name', $module_name);
        }

        if ($activity_type) 
        {
            $query->where('activity_type', $activity_type);
        }

        if ($search) 
        {
           $query->where(function ($query) use ($search) {
                $query->where('module_name','like', '%'.$search.'%')
                    ->orWhere('activity_type','like', '%'.$search.'%')
                    ->orWhere('ip_address','like', '%'.$search.'%')
                    ->orWhere('email','like', '%'.$search.'%')
                    ->orWhere(DB::raw('DATE_FORMAT(`audit_trail`.`created_at`, "%d-%m-%Y %H:%i:%s")'),'like', '%'.$search.'%')
                    ->orWhere(DB::raw('DATE_FORMAT(`last_login`, "%d-%m-%Y %H:%i:%s")'),'like', '%'.$search.'%')
                    ->orwhereRaw("concat(users.first_name, ' ',IFNULL(users.middle_name, ''),'', users.last_name) LIKE '%$search%'");
            });
        }

        $query->orderBy($order, $dir); 
        
        if ($page) 
        {
            return $this->getDataTableResult(
                AuditTrailResource::collection($query->paginate($limit))
            );
        }

        return AuditTrailResource::collection($query->get());
    }
 
}