<?php declare(strict_types=1);

namespace App\Core;

class CoreRepository 
{
    protected static function executeRawQuery($sql, $bindings = null)
    {
        $queryResult = \DB::select(\DB::raw($sql)->getValue(\DB::connection()->getQueryGrammar()), $bindings);
        return $queryResult[0] ?? null;
    }

    protected function checkIfIdExists(string $id, string $foreignIdConvention, array $tables, ?string $select = 'id'): bool
    {
        foreach ($tables as $table) 
        {
            $sql = "
                SELECT EXISTS (
                    SELECT {$select} 
                    FROM {$table}
                    WHERE {$foreignIdConvention}=:key 
                ) AS is_key_exists
            ";

            $bindings = ['key' => $id];
            $result = self::executeRawQuery($sql, $bindings);

            if (isset($result->is_key_exists) && $result->is_key_exists) 
            {
                return true;
            } 
        }

        return false;
    }
}