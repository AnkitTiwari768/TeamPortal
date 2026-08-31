<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Common\Contracts;

interface SearchRepositoryInterface 
{
    public function getSearchList(string $module, ?string $phrase = null);
}