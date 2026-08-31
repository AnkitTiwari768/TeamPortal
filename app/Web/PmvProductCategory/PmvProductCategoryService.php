<?php 
declare(strict_types=1);

namespace App\Web\PmvProductCategory;
use App\Traits\DataTable;
use App\Core\BaseService;
use App\Traits\{HasAttribute};
use App\Http\Services\CommonService;
use App\Models\User;
use App\Web\PmvProductCategory\PmvProductCategory;
use App\Contracts\GrantType;
use DB;
use Illuminate\Support\Str;

class PmvProductCategoryService extends BaseService
{
    use DataTable,HasAttribute;

    public function getAllSubDomainName()
    {
        return DB::table('sub_domains')->select('id','name')->where('status', 1)->get();
    }

    protected array $columns = [
        1 => 'name',
        2 => 'subdomain_id',
    ];

    public function getCategories()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $status = isset($filters['status']) ? $filters['status'] : null;
        $search ??= $this->escape_special_characters($search);

        $query = \DB::table('pm_vishwakarma_categories as pmv')
            ->select('pmv.*', 'sub_domains.name as subDomain_name') // Select subdomain name
            ->leftJoin('sub_domains', 'sub_domains.id', '=', 'pmv.subdomain_id'); // Join with subdomains table

        // Apply status filter
        if (isset($filters['status'])) {
            $query->where('pmv.status', $status);
        }
        
        // Apply search filter
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('pmv.name', 'like', "%$search%")
                    ->orWhere('pmv.subdomain_id', 'like', "%{$search}%")
                    ->orWhere('pmv.status', 'like', "%{$search}%");
            });
        }

        // Determine the order column
        $orderColumn = $this->columns[$order] ?? 'pmv.created_at';
        $query->orderBy($orderColumn, $dir);

        // If pagination is needed, return paginated results
        if ($page) {
            return $this->getDataTableResult(
                PmvProductCategoryResource::collection($query->paginate($limit))
            );
        }

        // Otherwise, return all results
        return PmvProductCategoryResource::collection($query->get());
    }

   
    public function storeCategory($payload, $categoryId = null)
    {
        $uuid = uuid();
        
        // Prepare the category data
        $categoryData = [
            'id' => $uuid,
            'name' => ucwords($payload['name']),
            'slug' => Str::slug($payload['name']),
            'subdomain_id' => $payload['subdomain_id'],
            'status' => $payload['status'],
            'updated_at' => now(),
            'updated_by' => AuthId(),
        ];

        // Check if a categoryId is provided for updating or if it's a new category
        if (!$categoryId) {
            // If no categoryId, it's a new category, so set created_at and created_by
            $categoryData['created_at'] = now();
            $categoryData['created_by'] = AuthId();

            // Perform the insert and return true or false based on success
            $inserted = DB::table('pm_vishwakarma_categories')->insert($categoryData);
            return $inserted ? true : false;  // Return true if inserted, false otherwise
        }

        // If categoryId exists, perform an update
        $updated = DB::table('pm_vishwakarma_categories')
            ->where('id', $categoryId)
            ->update($categoryData);

        // Return true if at least one row was updated, false otherwise
        return $updated > 0;  // Return true if at least one row was updated, false otherwise
    }    

    public function getCategory(string $categoryId)
    {
        return PmvProductCategory::select('id','name','subdomain_id','status')->find($categoryId);
    }


    
}