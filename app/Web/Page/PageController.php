<?php 

namespace App\Web\Page;

use App\Http\Controllers\ClientController;
use App\Web\Page\PageService as Service;
use App\Web\Page\PageValidation as Validation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageController extends ClientController 
{
	protected $module_url='pages.index';
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function index()
    {
		guard('page-view');
        return view('page.index')
            ->with('title', __('page.page_list'));
    }

    public function datalist()
    {
        $response = $this->service->getDataTableList(); 
        return $this->success($response);
    }

    public function create()
    {
		guard('page-edit');
		$module_url=$this->module_url;
        $helper = new \App\Web\Menu\MenuHelperService();
        $parent_backened_menu = $helper->getParentBackendMenu();
        $buildTree = $helper->buildTree($parent_backened_menu);
        return view('page.form',compact('module_url','parent_backened_menu','buildTree'))
            ->with('title', __('page.add_page'));
    }

    public function store(Request $request) 
    {
		guard('page-edit');
        try 
        {
            $validator = Validator::make(
                $request->all(), 
                Validation::getRules($request->all()),
				 Validation::CustomMessages()
            );

            if ($validator->fails()) 
                return $this->error($validator->errors());

            $response = $this->service->save($request->all());
            return $this->created($response);
        }
        catch (Throwable $exception) 
        {
            return $this->handler($error);
        }
    }

    public function edit($id)
    {
		guard('page-edit');
		$module_url=$this->module_url;
        $row = $this->service->findById($id);
        $helper = new \App\Web\Menu\MenuHelperService();
        $parent_backened_menu = $helper->getParentBackendMenu();
        $buildTree = $helper->buildTree($parent_backened_menu);
      

        return view('page.form', compact('id', 'row','module_url','parent_backened_menu','buildTree'))
            ->with('title', __('page.edit_page'));
    }

    public function update(Request $request, $id) 
    {
		guard('page-edit');
        try 
        {
            $validator = Validator::make(
                $request->all(), 
                 Validation::getRules($request->all(),(string) $id),
				 Validation::CustomMessages($id)
            );

            if ($validator->fails()) 
                return $this->error($validator->errors());

            $this->service->save($request->all(), $id);
            return $this->updated();
        }
        catch (Throwable $exception) 
        {
            return $this->handler($error);
        }
    }
}