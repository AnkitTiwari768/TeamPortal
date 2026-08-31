<?php 
declare(strict_types=1);
namespace App\Web\Masters\ClaimTypes;
use App\Traits\DataTable;
use App\Core\BaseService;
use App\Traits\{HasAttribute};
use App\Http\Services\CommonService;
use App\Models\User;
use App\Contracts\GrantType;
use DB;
use Illuminate\Support\Str;
use App\Services\PHPMailerService;


class ClaimTypesService extends BaseService
{
    use DataTable,HasAttribute;

   
    public function getDataClaimTypes()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $status = $filters['status'] ?? null;
        $search ??= $this->escape_special_characters($search);

        $query = ClaimTypes::query();

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
                ClaimTypesResource::collection($query->paginate($limit))
            );
        }

        return ClaimTypesResource::collection($query->get());
    }

    public function storeClaimType($payload, $id = null)
    {
        $data = [
            'name'   => ucwords($payload['name']),
            'short_name' => ucwords($payload['short_name']),
            'status' => $payload['status'],
        ];

        if (!$id) {
            $data['id'] = uuid(); // ensure helper exists
            $data['slug'] = Str::slug($payload['name']); // ✅ only on create

            return ClaimTypes::create($data) ? true : false;
        }

        $claimTypes = ClaimTypes::find($id);

        if (!$claimTypes) {
            return false;
        }

        // ❌ slug yaha update nahi hoga
        return $claimTypes->update($data);
    }
    public function getClaimTypesById(string $id)
    {
        return ClaimTypes::select('id','name','short_name','status')->find($id);
    }


    
}