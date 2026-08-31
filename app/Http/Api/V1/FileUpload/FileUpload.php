<?php

namespace App\Http\Api\V1\FileUpload;

use App\Traits\Mutators;
use Illuminate\Database\Eloquent\Model;

class FileUpload extends Model 
{
    use Mutators;

    protected $table = 'file_uploads';
    public $module = 'file_upload';
    public $incrementing = false;
	public $timestamps = false;

    protected $fillable = [
        'id',
        'file_name', 
        'file_path',
		'file_system_name',
		'file_type',
        'file_size',
        'file_extension',
        'created_by',
        'updated_by',
		'created_at',
		'updated_at'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];
}