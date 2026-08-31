<?php 

declare(strict_types=1);

namespace App\Web\Masters\AttributeValues;
use App\Http\Controllers\ClientController;
use App\Web\Masters\AttributeValues\AttributeValuesValidation as Validation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AttributeValuesController extends ClientController 
{
    public function __construct(private AttributeValuesService $service) 
    {
        $this->service = $service;
    }

    public function index(?string $dynamicSlug = null)
    {
        if (request()->has('draw')) {
            try {
                $result = $this->service->getAll($dynamicSlug);
                return $this->success($result);
            } catch (\Throwable $e) {
                return $this->handleException($e);
            }
        }

        // Title Format
        $title = ucwords(str_replace(['-', '_'], ' ', $dynamicSlug));
        // URL Slug Format
        $dynamicSlug = strtolower(str_replace([' ', '_'], '-', $dynamicSlug));
        return view('masters.attribute_values.index', compact('dynamicSlug', 'title'));
    }

    public function create(string $dynamicSlug)
    {
        $dynamicSlug = strtolower(str_replace('_', '-', $dynamicSlug)); 
        $title = ucwords(str_replace('-', ' ', $dynamicSlug));
        $slug1 = $dynamicSlug;
        $slug2 = str_replace('-', '_', $dynamicSlug);
        
        $attribute = DB::table('attributes')
            ->where('code', $slug1)
            ->orWhere('code', $slug2)
            ->first();

        return view('masters.attribute_values.form', [
                'title' => $title,
                'attribute' => $attribute,
                'row' => (object)[
                        'attribute_id' => $attribute->id,
                ],
                'dynamicSlug' => $dynamicSlug,
        ]);
    }

    public function edit(string $dynamicSlug, string $id)
    {
        $dynamicSlug = strtolower(str_replace('_', '-', $dynamicSlug)); 
        $title = ucwords(str_replace('-', ' ', $dynamicSlug));
        $slug1 = $dynamicSlug;
        $slug2 = str_replace('-', '_', $dynamicSlug);

        $attribute = DB::table('attributes')
            ->where('code', $slug1)
            ->orWhere('code', $slug2)
            ->first();

        try {
            $row = $this->service->findById($id);
            return view('masters.attribute_values.form', [
                        'row'         => $row,
                        'id'          => $id, 
                        'attribute'   => $attribute,
                        'dynamicSlug' => $dynamicSlug,
                        'module_url'  => 'attribute_values.index', 
                        'title'       => $title,
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Record not found');
        }
    }

    public function store(Request $request, string $dynamicSlug) 
    {
        try {
            $attributeId = $this->service->getAttributeIdBySlug($dynamicSlug);
            $validator   = Validation::getRules($request->all(), null, $attributeId);

            if ($validator->fails()) {
                return $this->error($validator->errors());
            }

            $response = $this->service->create($validator->validated(), $dynamicSlug);
            return $this->created($response);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function update(Request $request, string $dynamicSlug, string $id) 
    {
        try {
            $attributeId = $this->service->getAttributeIdBySlug($dynamicSlug);
            $validator   = Validation::getRules($request->all(), $id, $attributeId);

            if ($validator->fails()) {
                return $this->error($validator->errors());
            }

            $this->service->update($validator->validated(), $id);
            return $this->updated();
        } catch (\Throwable $exception) {
            return $this->handleException($exception);
        }
    }

    public function findById(string $id)
    {
        try {
            $response = $this->service->findById($id);
            return $this->success($response);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function getDetails(?string $dynamicSlug = null)
    {
        try {
            $response = $this->service->getAllDetails($dynamicSlug);
            return $this->success($response);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function getByAttributeId($attributeId)
    {
        try {
            $all = DB::table('attribute_values')
                ->where('attribute_id', $attributeId)
                ->where('status', 1)
                ->select('id', 'attribute_value', 'parent_id')
                ->get();

            // Group by parent_id
            $grouped = $all->groupBy('parent_id');

            // Root parents (parent_id = null)
            $parents = $grouped->get(null, collect());

            // Attach children
            $parents = $parents->map(function ($parent) use ($grouped) {
                $parent->children = $grouped->get($parent->id, collect());
                return $parent;
            });

            return response()->json([
                'status' => true,
                'data'   => $parents
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }
 
}
