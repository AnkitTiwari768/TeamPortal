<?php 
declare(strict_types=1);
namespace App\Web\Masters\Attributes;
use App\Traits\DataTable;
use App\Core\BaseService;
use App\Traits\{HasAttribute};
use App\Http\Services\CommonService;
use App\Models\User;
use App\Contracts\GrantType;
use DB;
use Illuminate\Support\Str;
use App\Services\PHPMailerService;


class AttributesService extends BaseService
{
    use DataTable,HasAttribute;

  
    public function getDataAttributes()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $status = $filters['status'] ?? null;
        $search ??= $this->escape_special_characters($search);

        $query = Attributes::query();

        // Status filter
        if ($status !== null) {
            $query->where('status', $status);
        }

        // Search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                ->orWhere('status', 'like', "%$search%");
            });
        }

        // Order
        $orderColumn = $this->columns[$order] ?? 'created_at';
        $query->orderBy($orderColumn, $dir);

        // Pagination
        if ($page) {
            return $this->getDataTableResult(
                AttributesResource::collection($query->paginate($limit))
            );
        }

        return AttributesResource::collection($query->get());
    }

    public function storeAttributes($payload, $id = null)
    {
        $data = [
            'name'   => ucwords($payload['name']),
            'status' => $payload['status'],
        ];

        if (!$id) {
            $data['id'] = uuid(); // ensure helper exists
            $data['code'] = Str::slug($payload['name']); // ✅ only on create

            return Attributes::create($data) ? true : false;
        }

        $attributes = Attributes::find($id);

        if (!$attributes) {
            return false;
        }

        return $attributes->update($data);
    }
    public function getAttributesById(string $id)
    {
        return Attributes::select('id','name','code','status')->find($id);
    }


    
}