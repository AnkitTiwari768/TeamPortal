<?php 

declare(strict_types=1);

namespace App\Domain\WorkflowType;

use App\Traits\DataTable;

class ListWorkflowTypeAction
{
    use DataTable;

    protected array $columns = [
        1 => 'name',
        2 => 'slug',
        3 => 'status',
    ];

    public function execute()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $name = isset($filters['name']) ? $filters['name'] : null;
        $slug = isset($filters['slug']) ? $filters['slug'] : null;
        $status = isset($filters['status']) ? $filters['status'] : null;
        
        $search ??= $this->escape_special_characters($search);

        $query = WorkflowType::select('name', 'slug', 'status', 'id');
        
        if ($name) 
        {
            $query->where('name', $name);
        }
        if ($slug) 
        {
            $query->where('slug', $slug);
        }

        if ($status) 
        {
            $query->where('status', $status);
        }

        if ($search) 
        {
            $query->where(function($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                      ->orWhere('slug', 'like', "%$search%")
                      ->orWhereRaw($this->datatable_status("status"). " LIKE '$search%'");    
            });
        }

        $query->orderBy($order, $dir); 
        
        if ($page) 
        {
            return $this->getDataTableResult(
                WorkflowTypeResource::collection($query->paginate($limit))
            );
        }

        return WorkflowTypeResource::collection($query->get());
    }
}
