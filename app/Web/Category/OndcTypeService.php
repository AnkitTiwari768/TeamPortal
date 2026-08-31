<?php

declare(strict_types=1);

namespace App\Web\Category;

use App\Core\BaseService;
use App\Traits\HasAttribute;

class OndcTypeService extends BaseService
{
    use HasAttribute;

    public function list($key = null)
    {
        return $this->listOf('types-of-transactions-preferred-on-ondc');
        
    }
}