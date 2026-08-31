<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Search;

use App\Traits\SearchRole;

class SearchService 
{
    use SearchRole;

    public function getCustomUserRoles(?string $phrase = null)
    {
        $query = \DB::table('roles')
            ->select('id', 'name')
            ->where('status', config('constant.ACTIVE'))
            ->where('is_custom', true);
        
        if($phrase) {
            $query->whereRaw($this->withTrimName, like_both($phrase));
        }

        $roles = $query->get()->toArray();

        return $roles ? search_list($roles) : [];
    }
}