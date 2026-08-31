<?php 

namespace App\Web\Page;

use Illuminate\Database\Eloquent\Model;
 
class Page extends Model 
{
     
    protected $table = 'cms_pages';
    public $module = 'page';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'menu_id', 
		'title_en',
		'title_mr',
        'slug_en',
		'slug_mr',
		'description_en',
		'description_mr',
        'is_active',
		'created_by',
		'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
	

	
}