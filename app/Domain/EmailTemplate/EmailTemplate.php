<?php

namespace App\Domain\EmailTemplate;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasUuids;

    protected $table = 'email_templates';

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];
}
