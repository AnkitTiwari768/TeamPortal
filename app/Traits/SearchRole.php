<?php 

declare(strict_types=1);

namespace App\Traits;

trait SearchRole 
{
    protected string $withTrimName = "TRIM(name) LIKE ?";
    
    public function searchByRole(?string $phrase = null): array
    {
        $query = \DB::table('roles')
            ->select('id', 'name')
            ->where('status', config('constant.ACTIVE'));
        
        if($phrase)
        {
            $query->whereRaw($this->withTrimName, like_both($phrase));
        }

        $roles = $query->get()->toArray();

        return $roles ? search_list($roles) : [];
    }
}