<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\FileUpload;

use App\Http\Services\ApiService;
use App\Http\Api\V1\FileUpload\FileUpload as Model;
use Illuminate\Http\Request;
use App\Http\Services\CommonService;
use Ramsey\Uuid\Rfc4122\UuidV4;
//use Intervention\Image\Facades\Image;
use DB;

class FileUploadService extends ApiService 
{
	//protected static $model = Model::class;

    public const FILE_NAME_SEPARATOR = '__';

    private UuidV4 $fileUuid;

    public function getFileOriginalName(object $file): string
    {
        return $file->getClientOriginalName();
    }

    public function getFileSystemName(object $file): string 
    {
        return $this->fileUuid . self::FILE_NAME_SEPARATOR . $this->getFileOriginalName($file);
    }

    public function getFileExtension(object $file): string 
    {
        return $file->getClientOriginalExtension();
    }

    public function createFileUploadPath(string $path, string $filename): string 
    {
        return  config('constant.upload_base_path') . $path . '/' . $filename;
    }

    public function insertFileUpload(array $fileUploadData)
    {
        return Model::create($fileUploadData);
    }
	
    public function uploadImage(object $file, string $path)
    {
        if ($file) 
        {
            $this->fileUuid = $this->uuid();
            $originalFileName = $this->getFileOriginalName(file: $file);
            $systemFileName = $this->getFileSystemName(file: $file);
            $fileUploadPath = $this->createFileUploadPath(path: $path, filename: $systemFileName);
            $fileExtension = $this->getFileExtension(file: $file);

            // save file to the server
            $file->storeAs($path, $systemFileName);

            // current logged-in user id
            $authId = AuthId();

            return $this->insertFileUpload(fileUploadData: [
                'id' =>  $this->fileUuid,
                'file_name' => $originalFileName, 
                'file_path' => $fileUploadPath,
                'file_system_name' => $systemFileName,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'file_extension' => $fileExtension,
                'created_by' => $authId,
                'updated_by' => $authId
            ]);
            
        }
    }

	// public function uploadImage($file)
    // {
    //     if ($file) 
    //     {	$uuid = $this->uuid();
    //         $originalFileName = $file->getClientOriginalName();
    //         $filename = $uuid. '_'. $originalFileName;
    //         $extension = $file->getClientOriginalExtension();
    //         $location = config('constant.user_image_file_path'); 
    //         $uploadBasePath = config('constant.upload_base_path');
    //       /*   $imageFile = Image::make($file)->resize(356, 313.05,  function ($constraint) {
    //             $constraint->aspectRatio();
    //         })->save(storage_path('app/'.$location. '/' .$filename)); */
	// 		$file->storeAs($location, $filename);
            
    //         $filepath = $uploadBasePath . $location . '/' . $filename;
    //         $fileType = $file->getClientMimeType();
    //         $fileSize = $file->getSize();//$imageFile->filesize();

    //         $authId = AuthId();
            
    //         return Model::create([
    //             'id' => $uuid,
    //             'file_name' => $originalFileName, 
    //             'file_path' => $filepath,
    //             'file_system_name' => $filename,
    //             'file_type' => $fileType,
    //             'file_size' => $fileSize,
    //             'file_extension' => $extension,
    //             'created_by' => $authId,
    //             'updated_by' => $authId
    //         ]);
    //     }
    // }
	
}