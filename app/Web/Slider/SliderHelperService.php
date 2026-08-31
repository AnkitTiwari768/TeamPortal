<?php

namespace App\Web\Slider;
use Illuminate\Support\Facades\DB;

class SliderHelperService
{
    public function slider_type($slider_type = null): array
    {
        $list = [
            1 => __('slider.slider'),
            2 => __('slider.banner') 
        ];
        return $slider_type ? $list[$slider_type] : $list;
    } 

    public function top_slider_sort_order($order=null){
        $menu_orders = $this->get_existed_top_slider_order();
        $sort_orders = array_column($menu_orders, 'sort_order');
        $range = range(1,15);
            foreach ($sort_orders as $sort_order) {
                unset($range[array_search($sort_order, $range)]);
                
            }
            if($order){
                array_push($range,$order);
                sort($range);
                $final = array_combine($range,$range);
                $range = $final;
                return $range;
            }else{
                sort($range);
                $final = array_combine($range,$range);
                $range = $final;
                return $range;
            }
    }
 
   public function get_existed_top_slider_order($slider_id='') {
    $result=DB::table('cms_sliders')->select('sort_order');
      if(!empty($slider_id)){
        $result->where("id",$slider_id);
      }
       $result->where('is_active',config('constant.ACTIVE'));
       $result->where('type',1);
       $query=$result->get()->toArray();
      
      return json_decode(json_encode($query), true);
    } 

 
    public function bottom_slider_sort_order($order=null){
        $menu_orders = $this->get_existed_bottom_slider_order();
        $sort_orders = array_column($menu_orders, 'sort_order');
        $range = range(1,15);
            foreach ($sort_orders as $sort_order) {
                unset($range[array_search($sort_order, $range)]);
                
            }
            if($order){
                array_push($range,$order);
                sort($range);
                $final = array_combine($range,$range);
                $range = $final;
                return $range;
            }else{
                sort($range);
                $final = array_combine($range,$range);
                $range = $final;
                return $range;
            }
    } 
  
  
   
    public function get_existed_bottom_slider_order($slider_id='') {
    $result=DB::table('cms_sliders')->select('sort_order');
      if(!empty($slider_id)){
        $result->where("id",$slider_id);
      }
       $result->where('is_active',config('constant.ACTIVE'));
       $result->where('type',2);
       $query=$result->get()->toArray();
      
      return json_decode(json_encode($query), true);
    } 

   static public function hindi_slug($string) 
     { 
        $string = trim($string);$string=strtolower($string);
        $string =preg_replace("/[^a-z0-9_ोौेैा्ीिीूुंःअआइईउऊएऐओऔकखगघचछजझञटठडढतथदधनपफबभमयरलवसशषहश्रक्षटठडढङणनऋड़\s-]/u", "", $string);
        $string = preg_replace("/[\s-]+/", " ", $string);
        $string = preg_replace("/[\s]/", '-', $string);
        return $string ;
    }
    

}
