<?php

namespace App\Web\EmailTemplate;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $table = 'email_templates';

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    // ✅ ADD variables here
    protected $fillable = [
        'id',
        'template_key',
        'subject',
        'body',
        'variables',   // 🔥 IMPORTANT
        'is_active',
    ];

    // ✅ JSON cast
    protected $casts = [
        'variables' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = uuid();
            }
        });
    }
}