<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\BaseService;

class StatusService extends BaseService
{
    public function list($key = null)
    {
        //return $this->listOf('types-of-transactions-preferred-on-ondc');
        $status = [
            config('constant.ACTIVE') => __('Active'),
            config('constant.INACTIVE') => __('In Active')
        ];

        return $key ? $status[$key] : $status;
    }
}