<?php

declare(strict_types=1);

namespace App\Traits;

use DB;

trait HasUserAddress
{
    public function fetchUserAddress(string $userId)
    {
        return DB::table('addresses as a')
            ->join('users as u', 'a.id', '=', 'u.address_id')
            ->join('countries as c', 'a.country_id', '=', 'c.id')
            ->where('u.id', $userId)
            ->select('a.address', 'c.name as country', 'a.state_id', 'a.district_id', 'a.postal_code')
            ->first();
    }

    public function fullUserAddress(string $userId)
    {
        $addressData = $this->fetchUserAddress($userId);

        if (! $addressData) return null;

        return $addressData?->address
            . ','
            . $addressData?->district_id
            . ','
            . $addressData?->state_id
            . ','
            . $addressData?->country;
    }
}
