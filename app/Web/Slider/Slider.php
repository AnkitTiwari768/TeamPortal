<?php 

namespace App\Web\Slider;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model 
{
    
    protected $table = 'cms_sliders';
    public $module = 'slider';
    public $incrementing = false;

    protected $fillable = [
        'id',
		'title_en',
		'title_mr',
        'slug_en',
		'slug_mr',
		'type',
		'url',
		'images',
		'sort_order',
        'is_active',
		'created_by',
		'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

}