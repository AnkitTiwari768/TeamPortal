<?php 
declare(strict_types=1);
namespace App\Web\Language;
use App\Traits\DataTable;
use App\Core\BaseService;
use App\Traits\{HasAttribute};
use App\Http\Services\CommonService;
use App\Models\User;
use App\Contracts\GrantType;
use DB;
use Illuminate\Support\Str;
use App\Services\PHPMailerService;


class LanguageService extends BaseService
{
    use DataTable,HasAttribute;

    public function getLanguageList()
    {
        return DB::table('language')->select('*')->get();
    }
    public function getDataLanguage()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $status = $filters['status'] ?? null;
        $search ??= $this->escape_special_characters($search);

        $query = Language::query();

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
                LanguageResource::collection($query->paginate($limit))
            );
        }

        return LanguageResource::collection($query->get());
    }

    public function storeLanguage($payload, $id = null)
    {
        $data = [
            'name'   => ucwords($payload['name']),
            'slug'   => Str::slug($payload['name']),
            'status' => $payload['status'],
        ];

        if (!$id) {
            $data['id'] = uuid(); 
            return Language::create($data) ? true : false;
        }

        $language = Language::find($id);

        if (!$language) {
            return false;
        }

        return $language->update($data);
    }
    public function getLanguageById(string $id)
    {
        return Language::select('id','name','status')->find($id);
    }
    
}