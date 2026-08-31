<?php 

declare(strict_types=1);

namespace App\Domain\ClaimType;

use App\Traits\DataTable;

class ListClaimTypeAction
{
    use DataTable;

    protected array $columns = [
        1 => 'name',
        2 => 'slug',
        3 => 'short_name',
        4 => 'status',
    ];

    public function execute()
    {

        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $name= isset($filters['name']) ? $filters['name'] : null;
		$slug= isset($filters['slug']) ? $filters['slug'] : null;
        $short_name = isset($filters['short_name']) ? $filters['short_name'] : null;
        $status = isset($filters['status']) ? $filters['status'] : null;
        
        $search ??= $this->escape_special_characters($search);

        $query = ClaimType::select('name', 'slug', 'short_name', 'status', 'id');
        
        if ($name) 
        {
            $query->where('name', $name);
        }
		if ($slug) 
        {
            $query->where('slug', $slug);
        }

        if ($short_name) 
        {
            $query->where('short_name', $short_name);
        }

        if ($status) 
        {
            $query->where('status', $status);
        }

        if ($search) 
        {
            $query->where(function($query) use ($search) {
                $query->where('name','like', "%$search%")
					  ->orWhere('slug','like', "%$search%")
					  ->orWhere('short_name','like', "%$search%")
			          ->orWhereRaw($this->datatable_status("status"). "LIKE '$search%'");    
            });
        }

        $query->orderBy($order, $dir); 
        
        if ($page) 
        {
            return $this->getDataTableResult(
                ClaimTypeResource::collection($query->paginate($limit))
            );
        }

        return ClaimTypeResource::collection($query->get());
    }
}