<?php

declare(strict_types=1);

namespace App\Domain\Download;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadAction
{
    public function execute(string $fileId): array
    {
        $file = DB::table('file_uploads')
            ->where('id', $fileId)
            ->orWhere('file_system_name', $fileId)
            ->first();

        if (!$file) {
            abort(404, 'File record not found.');
        }

        // Absolute file path
        $absolutePath = base_path($file->file_path);


        if (!file_exists($absolutePath)) {
            abort(404, 'File not found on server.');
        }

        return [$absolutePath, $file->file_name, $file->file_type];
    }
}
