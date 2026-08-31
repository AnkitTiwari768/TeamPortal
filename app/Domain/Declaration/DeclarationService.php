<?php

declare(strict_types=1);

namespace App\Domain\Declaration;

use Illuminate\Support\Facades\DB;

class DeclarationService
{
    public function getDeclarationContent(string $key): ?string
    {
        return DB::table('declaration_types')
            ->where('status', true)
            ->where('declaration_key', $key)
            ->value('declaration_content');
    }
}
