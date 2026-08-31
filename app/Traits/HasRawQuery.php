<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HasRawQuery
{
    public static function executeRawQuery($sql, $bindings = null)
    {
        $queryResult = DB::select(DB::raw($sql)->getValue(DB::connection()->getQueryGrammar()), $bindings);
        return $queryResult[0] ?? null;
    }
}
