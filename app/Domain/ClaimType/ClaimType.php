<?php

declare(strict_types=1);

namespace App\Domain\ClaimType;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ClaimType extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'name',
        'slug',
        'short_name',
        'status',
        'created_at',
        'updated_at'
    ];
}
