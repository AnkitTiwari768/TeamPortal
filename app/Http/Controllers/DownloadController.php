<?php 

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DownloadController extends ClientController 
{
    public function __invoke(string $filePath, string $fileName)
    {
        return $this->download($request->filePath, $request->fileName);
    }
}