<?php 
declare(strict_types=1);
namespace App\Web\Category;
use App\Traits\DataTable;
use App\Core\BaseService;

class CategoryService extends BaseService
{
    protected array $columns = [
        1 => 'ondc_type',
        2 => 'name',
        3 => 'status'
    ];

    public function getCategories()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $roleId = isset($filters['role_id']) ? $filters['role_id'] : null;
        $status = isset($filters['status']) ? $filters['status'] : null;
        
        $search ??= $this->escape_special_characters($search);

       
        $query = \DB::table('categories as c')
        ->select('c.*', 'at.attribute_value as ondc_type')
        ->join('attribute_values as at', 'at.id', '=', 'c.ondc_type_id', 'left');
        
        if ($roleId) 
        {
            $query->where('id', $roleId);
        }

        if (isset($filters['c.status'])) 
        {
            $query->where('c.status', $status);
        }
        else 
        {
            $query->where('c.status', config('constant.ACTIVE'));
        }

        if ($search) 
        {
            $query->where(function($query) use ($search) {
                $query->where('c.name','like', "%$search%")
			        ->orWhereRaw($this->datatable_status("c.status"). "LIKE '$search%'");    
            });
        }

        $query->orderBy($order, $dir); 
        
        if ($page) 
        {
            return $this->getDataTableResult(
                CategoryResource::collection($query->paginate($limit))
            );
        }

        return CategoryResource::collection($query->get());
    }

    public function storeCategory(array $payload, ?string $categoryId = null): bool
    {
        $categoryData = [
            'ondc_type_id' => $payload['ondc_type_id'],
            'name' => $payload['name'],
            'code' => slugify($payload['name']),
            'description' => $payload['description'] ?? null,
            'status' => $payload['status'],
        ];

        if (! $categoryId)
        {
            $categoryData['created_at'] = currentDateTime();
            $categoryData['created_by'] = AuthId();

            $categoryData['updated_at'] = currentDateTime();
            $categoryData['updated_by'] = AuthId();

            return (bool) Category::create($categoryData);
        }

        $categoryData['updated_at'] = currentDateTime();
        $categoryData['updated_by'] = AuthId();

        $category = Category::findOrFail($categoryId);
        
        return (bool) $category->fill($categoryData)->save();
    }

    

    public function getCategory(string $categoryId)
    {
        return Category::select('id','ondc_type_id' ,'name', 'status')->find($categoryId);
    }
}