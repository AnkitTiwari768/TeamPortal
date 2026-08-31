<?php

declare(strict_types=1);

namespace App\Domain\UserProfile;

use Illuminate\Support\Facades\DB;

class GetUserProfileDetailsAction
{
    public function execute(string $userId)
    {
        return DB::table('users AS u')
            ->selectRaw('
                u.id,
                u.first_name,
                u.middle_name,
                u.last_name,
                u.username,
                u.email,
                u.email_verified,
                u.alternate_email,
                u.alternate_email_verified,
                u.mobile,
                u.mobile_verified,
                u.alternate_mobile,
                u.alternate_mobile_verified,
                u.status,
                u.department_id,
                u.designation_id,
                u.photo_file_upload_id,
                u.profile_picture,
                a.country_id,
                a.state_id,
                a.district_id,
                d.name AS department_name,
                ds.name AS designation_name,
                c.name AS country_name,
                s.name AS state_name,
                l.name AS district_name,
                a.address,
                a.postal_code,
                (
                    SELECT JSON_ARRAYAGG(r.name)
                    FROM roles AS r
                    INNER JOIN user_roles AS ur ON ur.role_id = r.id
                    WHERE ur.user_id = u.id 
                ) AS roles
            ')
            ->leftJoin('departments AS d', 'd.id', '=', 'u.department_id')
            ->leftJoin('designations AS ds', 'ds.id', '=', 'u.designation_id')
            ->leftJoin('addresses AS a', 'a.id', '=', 'u.address_id')
            ->leftJoin('countries AS c', 'c.id', '=', 'a.country_id')
            ->leftJoin('states AS s', 's.id', '=', 'a.state_id')
            ->leftJoin('locations AS l', 'l.id', '=', 'a.district_id')
            ->where('u.id', $userId)
            ->first();
    }
}
