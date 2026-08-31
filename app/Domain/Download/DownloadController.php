<?php

declare(strict_types=1);

namespace App\Domain\Download;

use App\Domain\Download\DownloadAction;

class DownloadController
{
    public function download(string $fileId, DownloadAction $action)
    {
        [$absolutePath, $fileName, $fileType] = $action->execute($fileId);

         return response()->download(
            $absolutePath,
            $fileName,
            [
                'Content-Type' => $fileType,
            ]
        );
    }
}
