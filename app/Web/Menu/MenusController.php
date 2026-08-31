<?php 

namespace App\Web\Menu;

use App\Http\Controllers\ClientController;
use App\Web\Menu\MenuService as Service;
use App\Web\Menu\MenuValidation as Validation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenusController extends ClientController 
{
	protected $module_url='menus.index';
    protected $service;

    public function __construct(Service $service,MenuHelperService $helper)
    {
        $this->service = $service;
        $this->helper = $helper;
    }

    public function index()
    {
		guard('menu-view');
        return view('menu.index')
            ->with('title', __('menu.menu_list'));
    }

    public function datalist()
    {
        $response = $this->service->getDataTableList(); 
        return $this->success($response);
    }

    public function create()
    {
		guard('menu-edit');
		$module_url=$this->module_url;
        
        $flatMenus = $this->helper->getParentBackendMenu();
        $tree  = $this->helper->buildTree($flatMenus);
        $subMenuList= $this->helper->getSubMenuOptions();
        $sortOrderList=  $this->helper->getMenuSortOrder($menu->sort_order ?? null);
        $templateList= $this->helper->getTemplateOptions();
        $fmenuCategoryList= $this->helper->getFMenuCategories();
        return view('menu.form',compact('module_url','flatMenus','tree','subMenuList','sortOrderList','templateList','fmenuCategoryList'))
            ->with('title', __('menu.add_menu'));
    }

    public function store(Request $request) 
    {
		guard('menu-edit');
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
		guard('menu-edit');
		$module_url=$this->module_url;
        $row = $this->service->findById($id);
        $flatMenus = $this->helper->getParentBackendMenu();
        $tree  = $this->helper->buildTree($flatMenus);
        $subMenuList= $this->helper->getSubMenuOptions();
        $sortOrderList=  $this->helper->getMenuSortOrder($row['sort_order'] ?? null);
        $templateList= $this->helper->getTemplateOptions();
        $fmenuCategoryList= $this->helper->getFMenuCategories();

        return view('menu.form', compact('id', 'row','module_url','flatMenus','tree','subMenuList','sortOrderList','templateList','fmenuCategoryList'))
            ->with('title', __('menu.edit_menu'));
    }

    public function update(Request $request, $id) 
    {
		guard('menu-edit');
        try 
        {
            $validator = Validator::make(
                $request->all(), 
                Validation::getRules($request->all(),(string) $id),
				 Validation::CustomMessages()
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
	
	public function get_existed_menu_order($menu_id) {
        $menu_orders =$this->helper->getExistingMenuOrder($menu_id); 
        //get_existed_menu_order($menu_id);
        $sort_orders = array_column($menu_orders, 'sort_order');
        $range = range(1,50);
        foreach ($sort_orders as $sort_order) {
            unset($range[array_search($sort_order, $range)]);
        }
        echo json_encode(array('data' => $range));
        exit;
    }
}