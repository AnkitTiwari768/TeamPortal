<?php 

declare(strict_types=1);

namespace App\Modules\FileUpload;

use App\Http\Controllers\ClientController;
use App\Http\Api\V1\FileUpload\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator; 
use App\Rules\FileName;

final class FileUploadController extends ClientController
{
    public function __construct(private FileUploadService $service)
    {
        $this->service = $service;
    }
	
	public function uploadImage(Request $request)
    {
        try
        {
            $validator = Validator::make($request->all(), [
                'file' => [
                    'required',
                    'mimes:png,jpg,jpeg',
                    'max:100',
                    new FileName
                ]
            ]);
    
            if ($validator->fails()) {
                return $this->error($validator->errors());
            }
            $response = $this->service->uploadImage($request->file('file'));
            return $this->success($response);
        }
        catch (Throwable $exception) 
        {
            return $this->handler($error);
        }
    }
}