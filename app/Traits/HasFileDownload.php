<?php 

declare(strict_types=1);

namespace App\Traits;

trait HasFileDownload
{
    public function download(string $filePath, string $fileName)
    {
        return response()->download(config('base_path') . '/' . $filePath . '/' . $fileName);
    }
}