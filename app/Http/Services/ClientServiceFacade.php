<?php 

declare(strict_types=1);

namespace App\Http\Services;

use Illuminate\Support\Facades\Facade;

class ClientServiceFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'clientservice';
    }
}