<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HasCommon
{
    public function documentCategoryIsExists(string $documentCategoryId): bool
    {
        return DB::table('document_categories')
            ->where('id', $documentCategoryId)
            ->exists();
    }

    public function fileUploadIsExists(string $fileUploadId): bool
    {
        return DB::table('file_uploads')
            ->where('id', $fileUploadId)
            ->exists();
    }
}
