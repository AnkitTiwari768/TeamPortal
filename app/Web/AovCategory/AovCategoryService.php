<?php 
declare(strict_types=1);

namespace App\Web\AovCategory;
use App\Traits\DataTable;
use App\Core\BaseService;
use App\Traits\{HasAttribute};
use App\Http\Services\CommonService;
use App\Models\User;
use App\Web\AovCategory\AovCategory;
use App\Contracts\GrantType;
use DB;
use Illuminate\Support\Str;
use App\Services\PHPMailerService;

class AovCategoryService extends BaseService
{
    use DataTable,HasAttribute;

    public function getOndcDomainType($domain_type)
    {
        return DB::table('sub_domains')
            ->select('id', 'name', 'ondc_domain_id')
            ->where('product_domain_type', $domain_type)
            ->get();
    }


    protected array $columns = [
        1 => 'aov_grouping_type',
        2 => 'name',
        3 => 'ondc_domain_id',
    ];

    public function getCategories()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();        
        $status = isset($filters['status']) ? $filters['status'] : null;        
        $search ??= $this->escape_special_characters($search);       
        $query = \DB::table('sub_domains as sb')
        ->select('sb.*');       
        
        if (isset($filters['status']))         {
            $query->where('sb.status', $status);
        }
        

        if ($search) 
        {
            $query->where(function($query) use ($search) {
                $query->where('sb.name','like', "%$search%")
			        ->orWhere('sb.aov_grouping_type', 'like', "%{$search}%")
                    ->orWhere('sb.ondc_domain_id', 'like', "%{$search}%")
                    ->orWhere('sb.status', 'like', "%{$search}%");
            });
        }

        $orderColumn = $this->columns[$order] ?? 'sb.created_at';
        $query->orderBy($orderColumn, $dir); 
        
        if ($page) 
        {
            return $this->getDataTableResult(
                AovCategoryResource::collection($query->paginate($limit))
            );
        }

        return AovCategoryResource::collection($query->get());
    }

    /*public function storeCategory(array $payload, ?string $categoryId = null): bool
    {
        $categoryData = [
           
            'name' => ucwords($payload['name']),
            'aov_grouping_type' => $payload['aov_grouping_type'],
            'minimum_order_value' => $payload['minimum_order_value'],
            'ondc_domain_id' => strtoupper($payload['ondc_domain_id']),
            'code' => slugify($payload['name']),
            'aov_category_id' => $payload['aov_category_id'] ?? null,
            'status' => $payload['status'],
        ];

        if (! $categoryId)
        {
            $categoryData['created_at'] = currentDateTime();
            $categoryData['created_by'] = AuthId();

            $categoryData['updated_at'] = currentDateTime();
            $categoryData['updated_by'] = AuthId();

            return (bool) AovCategory::create($categoryData);
        }

        $categoryData['updated_at'] = currentDateTime();
        $categoryData['updated_by'] = AuthId();

        $category = AovCategory::findOrFail($categoryId);
        
        return (bool) $category->fill($categoryData)->save();
    }*/

    public function storeCategory(array $payload, ?string $categoryId = null): bool
    {
        return DB::transaction(function () use ($payload, $categoryId) {
            if (!$categoryId) {

                $subDomainId = uuid();
                $aovId       = uuid();
                DB::table('sub_domains')->insert([
                    'id' => $subDomainId,
                    'name' => ucwords($payload['name']),
                    'code' => slugify($payload['name']),
                    'ondc_domain_id' => strtoupper($payload['ondc_domain_id']),
                    'aov_grouping_type' => $payload['aov_grouping_type'],
                    'minimum_order_value' => $payload['minimum_order_value'],
                    'aov_category_id' => $aovId,
                    'status' => $payload['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('catalogue_aov_categories')->insert([
                    'id' => $aovId,
                    'sub_domain_id' => $subDomainId,
                    'rate' => $payload['rate'] ?? null,
                    'status' => $payload['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return true;
            }

            $sub = DB::table('sub_domains')->where('id', $categoryId)->first();

            if (!$sub) {
                throw new \Exception('Category not found');
            }

            DB::table('sub_domains')
                ->where('id', $categoryId)
                ->update([
                    'name' => ucwords($payload['name']),
                  //  'code' => slugify($payload['name']),
                    'ondc_domain_id' => strtoupper($payload['ondc_domain_id']),
                    'aov_grouping_type' => $payload['aov_grouping_type'],
                    'minimum_order_value' => $payload['minimum_order_value'],
                    'status' => $payload['status'],
                    'updated_at' => now(),
                ]);
                
            DB::table('catalogue_aov_categories')
                ->where('id', $sub->aov_category_id)
                ->update([
                    'rate' => $payload['rate'] ?? null,
                    'status' => $payload['status'],
                    'updated_at' => now(),
                ]);

            return true;
        });
    }
    

    public function getCategory(string $categoryId)
    {
        return AovCategory::select('id','name','ondc_domain_id','aov_grouping_type','minimum_order_value','status')->find($categoryId);
    }

    public function getOndcDomains()
    {
        return DB::table('attribute_values as av')
            ->join('attributes as a', 'av.attribute_id', '=', 'a.id')
            ->select('av.id', 'av.attribute_value as value')
            ->where('a.code', 'ondc-domain-mapping') 
            ->get();
    }


    
}