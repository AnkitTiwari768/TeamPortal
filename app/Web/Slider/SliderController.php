<?php 

namespace App\Web\Slider;

use App\Http\Controllers\ClientController;
use App\Web\Slider\SliderService as Service;
use App\Web\Slider\SliderValidation as Validation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SliderController extends ClientController 
{
	protected $module_url='sliders.index';
    protected $service;

    public function __construct(Service $service,SliderHelperService $helper)
    {
        $this->service = $service;
        $this->helper = $helper;
    }

    public function index()
    {
		guard('slider-view');
        return view('slider.index')
            ->with('title', __('message.slider_list'));
    }

    public function datalist()
    {
        $response = $this->service->getDataTableList(); 
        return $this->success($response);
    }

    public function create()
    {
		guard('slider-edit');
		$module_url=$this->module_url;
        $slider_type  = $this->helper->slider_type();
        $top_slider_sort_order  = $this->helper->top_slider_sort_order();
        $bottom_slider_sort_order  = $this->helper->bottom_slider_sort_order();
        return view('slider.form',compact('module_url','slider_type','top_slider_sort_order','bottom_slider_sort_order'))
            ->with('title', __('message.add_slider'));
    }

    public function store(Request $request) 
    {
		guard('slider-edit');
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
		guard('slider-edit');
		$module_url=$this->module_url;
        $row = $this->service->findById($id);
        $slider_type  = $this->helper->slider_type();
        $top_slider_sort_order  = $this->helper->top_slider_sort_order($row['sort_order'] ?? null);
        $bottom_slider_sort_order  = $this->helper->bottom_slider_sort_order($row['sort_order'] ?? null);
        return view('slider.form', compact('id', 'row','module_url','slider_type','top_slider_sort_order','bottom_slider_sort_order'))
            ->with('title', __('message.edit_slider'));
    }

    public function update(Request $request, $id) 
    {
		guard('slider-edit');
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
}