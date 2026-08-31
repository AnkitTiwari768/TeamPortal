<?php

namespace App\Domain\Language;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Language extends Model
{
    use HasUuids;

    protected $table = 'language';
}
