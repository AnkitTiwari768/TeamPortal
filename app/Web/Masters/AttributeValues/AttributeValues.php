<?php 

namespace App\Http\Web\Masters\AttributeValues;
use App\Core\BaseModel;

class PremisesTypes extends BaseModel 
{
   
    protected $table = 'attribute_values';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'attribute_id',         
        'attribute_value',
        'code',
        'status',
        'sort_order',
        'created_at',
        'updated_at',
    ];  
}
?>