<?php

declare(strict_types=1);

namespace App\Traits;

trait HasRecursivePermission
{
    protected function recursiveZipPermission(string $path, $permission = 0777)
    {
        if (!file_exists($path)) {
            mkdir($path, $permission, true);
        }

        $dir = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file) {
            @chmod($file->getPathname(), $permission);
        }

        // Finally set folder itself
        @chmod($path, $permission);

        return true;
    }
}
