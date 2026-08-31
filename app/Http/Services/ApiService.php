<?php 

declare(strict_types=1);

namespace App\Http\Services;

use App\Core\BaseService;
use App\Traits\{DataTable, Respond};
use Illuminate\Support\Str;
use Ramsey\Uuid\Rfc4122\UuidV4;
use DB;

class ApiService extends BaseService 
{
    use DataTable, Respond;

    protected function uuid(): UuidV4
    {
        return Str::orderedUuid();
    }

    protected function checkStatusIsDisabled(int $status): bool 
    {
        return $status === 0 ? true : false; 
    }

    protected function checkIfIdExists(string $id, string $foreignIdConvention, array $tables, ?string $select = 'id'): bool
    {
        foreach ($tables as $table) 
        {
            $sql ="
                SELECT EXISTS (
                    SELECT {$select} 
                    FROM {$table}
                    WHERE {$foreignIdConvention}=:key 
                ) AS is_key_exists
            ";

            $bindings = ['key' => $id];
            $result = parent::executeRawQuery($sql, $bindings);

            if (isset($result->is_key_exists) && $result->is_key_exists) 
            {
                return true;
            } 
        }

        return false;
    }

    protected function createHierarichalLevelQuery() 
    {
        return DB::raw('(
            select c3.id 
            from user_roles ur
            inner join roles r on ur.role_id = r.id
            inner join categories c3 on r.category_id = c3.id
            where ur.user_id = roles.created_by
            order by c3.level asc
            limit 1)
        ');
    }
}