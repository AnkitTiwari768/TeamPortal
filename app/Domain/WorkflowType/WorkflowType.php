<?php

declare(strict_types=1);

namespace App\Domain\WorkflowType;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WorkflowType extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'name',
        'slug',
        'status',
        'created_at',
        'updated_at'
    ];
}
