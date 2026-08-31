<?php 

declare(strict_types=1);

namespace App\Traits\Mutators;

use Illuminate\Support\Facades\Hash;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait Hashable 
{
    protected function password(): Attribute 
    {
        return Attribute::make(
            set: fn (string $value) => Hash::make($value)
        );
    }
}