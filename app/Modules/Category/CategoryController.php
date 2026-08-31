<?php 
declare(strict_types=1);
namespace App\Modules\Category;
use App\Http\Controllers\ClientController;
use App\Http\Api\V1\Category\CategoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Api\V1\Category\CategoryValidation as Validation;

final class CategoryController extends ClientController
{
    private static $module = 'categories.index';
    
    public function __construct(private CategoryService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
		guard(config('permissions.category-view'));
        return view('category.index')
            ->with('title', __('message.category_list'));
    }

    public function datalist(): mixed
    {
		guard(config('permissions.category-view'));
        try 
        {
            $result = $this->service->getCategories();    
            return $this->success($result);
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }

    public function create(): View
    {
		guard(config('permissions.category-create'));
        return view('category.form')
            ->with('title', __('message.add_category'))
            ->with('module_url', self::$module)
			->with('details', (object) $this->service->getDetails());
    }

    public function store(Request $request): mixed 
    {   
		guard(config('permissions.category-create'));
        try 
        {
			$validator = Validation::getRules($request->all());
            
            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }
			
            $result = $this->service->create($validator->validated());
            return $this->created($result);
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }

    public function edit(string $id): View
    {
		guard(config('permissions.category-update'));
        return view('category.form')
            ->with('title', __('message.edit_category'))
            ->with('module_url', self::$module)
            ->with('id', $id)
            ->with('row', $this->service->findById($id))
			->with('details', (object) $this->service->getDetails());
    }

    public function update(Request $request, string $id): mixed
    {
		guard(config('permissions.category-update'));
        try 
        {
			$validator = Validation::getRules($request->all(), $id);
            
            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }
			
            return $this->service->update($validator->validated(), $id);
            //$this->updated();
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
        
    }
	
	public function destroy(string $id)
    {
        guard(config('permissions.category-delete'));
        try 
        {
            $this->service->deleteById($id);
            return $this->deleted();
        }
        catch (Throwable $exception) 
        {
            return $this->handler($error);
        }
    }
}