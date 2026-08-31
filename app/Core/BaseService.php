<?php 

declare(strict_types=1);

namespace App\Core;

use App\Traits\{DataTable, HasRawQuery};

abstract class BaseService 
{
    use DataTable, HasRawQuery;
}