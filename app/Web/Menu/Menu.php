<?php 

namespace App\Web\Menu;

use Illuminate\Database\Eloquent\Model;
//use App\Traits\Scopes;

class Menu extends Model 
{
    //use Scopes;
    
    protected $table = 'cms_menus';
    public $module = 'menu';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'parent_id', 
		'title_en',
		'title_mr',
        'slug_en',
		'slug_hi',
		'show_mr',
		'sort_order',
		'sub_menu',
		'template',
		'fmenu_category',
        'is_active',
		'created_by',
		'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
	


}