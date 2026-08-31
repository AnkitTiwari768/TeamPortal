<?php

declare(strict_types=1);

namespace App\Domain\DocumentCategory;

use Illuminate\Support\Facades\DB;

class DocumentCategoryService
{
    public function getDocumentCategoriesBySlugs(array $slugs): array
    {
        return DB::table('document_categories')
            ->whereIn('slug', $slugs)
            ->pluck('id', 'slug')
            ->toArray();
    }
}
