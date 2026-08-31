<?php

namespace App\Web\FlowManagement;

use Illuminate\Database\Eloquent\Model;
//use App\Traits\Scopes;

class FlowManagement extends Model
{
    //use Scopes;

    protected $table = 'workflows';
    public $module = 'Flow Management';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'workflow_type_id',
        'level',
        'role_id',
        'user_id',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
