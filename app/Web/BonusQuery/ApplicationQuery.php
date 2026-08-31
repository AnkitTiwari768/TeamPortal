<?php

namespace App\Web\BonusQuery;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ApplicationQuery extends Model
{
    use HasUuids;

    protected $table = 'bonus_queries';

    protected $guarded = [];
}
