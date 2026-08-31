<?php 

declare(strict_types=1);

namespace App\Web\Masters\AttributeValues;
use App\Web\Masters\AttributeValues\AttributeValues as Model;
use DB;
use App\Core\BaseService;

class AttributeValuesService  extends BaseService
{
  
    protected array $columns = [ 
        1 => 'id',
        2 => 'attribute_id',
        3 => 'attribute_value',
        4 => 'code',
        5 => 'status',
    ];

    public function getAttributeIdBySlug(?string $slug = null): ?string
    {
        if ($slug) {
            $normalizedSlug = str_replace('-', '_', $slug);
            $attribute = DB::table('attributes')
                ->where(function ($query) use ($slug, $normalizedSlug) {
                    $query->where('code', $slug)
                        ->orWhere('code', $normalizedSlug);
                })
                ->first();

            if ($attribute) {
                return $attribute->id;
            }
        }
        return null;
    }

    public function getAll(?string $dynamicSlug = null)
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $attributeId = $this->getAttributeIdBySlug($dynamicSlug);

        $query = DB::table('attribute_values')
            ->select(
                'attribute_values.id',
                'attribute_values.attribute_id',
                'attribute_values.attribute_value',
                'attribute_values.code',
                'attribute_values.parent_id',
                'attribute_values.sort_order',
                'attribute_values.status',
                'attribute_values.created_at',
                'attribute_values.updated_at'
            )
            ->where('attribute_values.attribute_id', $attributeId);

        if (!empty($filters['status'])) {
            $query->where('attribute_values.status', $filters['status']);
        }

        if (!empty($search) && $this->escape_special_characters($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('attribute_values.attribute_value', 'LIKE', "%$search%")
                  ->orWhere('attribute_values.code', 'LIKE', "%$search%")
                  ->orWhereRaw($this->datatable_status("attribute_values.status") . " LIKE '%$search%'");
            });
        }

        $query->orderBy('attribute_values.' . $order, $dir);

        if (!empty($page)) {
            return $this->getDataTableResult(
                AttributeValuesResource::collection($query->paginate($limit))
            );
        }

        return AttributeValuesResource::collection($query->get());
    }

    public function create(array $payload, ?string $dynamicSlug = null): bool
    {
        $attributeId = $this->getAttributeIdBySlug($dynamicSlug);

        $data = [
            'id'              => \Illuminate\Support\Str::uuid(),
            'attribute_id'    => $payload['attribute_id'], 
            'attribute_value' => $payload['attribute_value'],
            'parent_id'       => $payload['parent_id'] ?: null,
            'sort_order'      => $payload['sort_order'] ?? 0,
            'code'            => \Illuminate\Support\Str::slug($payload['attribute_value']),
            'status'          => $payload['status'],
            'created_at'      => currentDateTime(),
            'updated_at'      => currentDateTime(),
        ];

        return (bool) DB::table('attribute_values')->insert($data);
    }

    public function update(array $payload, string $id): bool
    {
       
        $data = [
            'attribute_value' => $payload['attribute_value'],
            'attribute_id'    => $payload['attribute_id'],
           // 'parent_id'       => $payload['parent_id'] ?: null,
            'status'          => $payload['status'],
            'sort_order'      => $payload['sort_order'] ?? 0,
            'updated_at'      => currentDateTime(),
        ];

        return (bool) DB::table('attribute_values')->where('id', $id)->update($data);
    }

    public function findById(string $id)
    {
        return DB::table('attribute_values')->where('id', $id)->first();
    }

    public function getAllDetails(?string $dynamicSlug = null)
    {
        $attributeId = $this->getAttributeIdBySlug($dynamicSlug);

        return DB::table('attribute_values')
            ->where('attribute_id', $attributeId)
            ->where('status', 1)
            ->select('id', 'attribute_id', 'attribute_value', 'code', 'status', 'created_at', 'updated_at')
            ->get();
    }

    // public function getByAttributeId(string $attributeId)
    // {
    //     return DB::table('attribute_values')
    //         ->where('attribute_id', $attributeId)
    //         ->select('id', 'attribute_id', 'attribute_value', 'code', 'status', 'created_at', 'updated_at')
    //         ->get();
    // }
}
