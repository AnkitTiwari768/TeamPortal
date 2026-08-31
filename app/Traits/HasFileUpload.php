<?php

declare(strict_types=1);

namespace App\Traits;

use App\Http\Api\V1\FileUpload\FileUpload;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

trait HasFileUpload
{
    public function uploadFile($file, $uploadPath)
    {
        $originalFileName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileType = $file->getClientMimeType();
        $fileSize = $file->getSize();

        $systemFilename = uuid() . '.' . $extension;
        $file->storeAs($uploadPath, $systemFilename);

        $filepath = config('upload.base_path') . '/' . $uploadPath . '/' . $systemFilename;

        return FileUpload::create([
            'id' => uuid(),
            'file_name' => $originalFileName,
            'file_path' => $filepath,
            'file_system_name' => $systemFilename,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'file_extension' => $extension,
            'created_by' => AuthId(),
            'updated_by' => AuthId()
        ]);
    }


    public function uploadFiles(Request $request, string $fileKey, string $filePath)
    {
        $data = [];

        foreach ($request->only($fileKey) as $files) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    $originalFileName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $fileType = $file->getClientMimeType();
                    $fileSize = $file->getSize();

                    $systemFilename = uuid() . '.' . $extension;
                    $file->storeAs($uploadPath, $systemFilename);

                    $filepath = config('upload.base_path') . '/' . $uploadPath . '/' . $systemFilename;
                    $data[] = [
                        'id' => uuid(),
                        'file_name' => $originalFileName,
                        'file_path' => $filepath,
                        'file_system_name' => $systemFilename,
                        'file_type' => $fileType,
                        'file_size' => $fileSize,
                        'file_extension' => $extension,
                        'created_by' => AuthId(),
                        'updated_by' => AuthId()
                    ];
                }
            }
        }

        FileUpload::insert($data);
        return $data;
    }

    public function uploadFileWithValidation(Request $request, string $fileKey, string $filePath, array $rules, ?array $messages = [])
    {

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return $this->error($validator->errors());
        }

        return $this->success(
            $this->uploadFile($request->file($fileKey), $filePath)
        );
    }

    public function uploadFileWithValidationPlain(Request $request, string $fileKey, string $filePath, array $rules, ?array $messages = [])
    {

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return [
                'status' => false,
                'errors' => $validator->errors()
            ];
        }

        return [
            'status' => true,
            'file' => $this->uploadFile($request->file($fileKey), $filePath)
        ];
    }

    public function uploadMultipleFileWithValidation(Request $request, string $fileKey, string $filePath, array $rules)
    {
        $validator = Validator::make($request->all(), $rules, ["{$fileKey}.*" => "files"]);

        if ($validator->fails()) {
            return $this->error($validator->errors());
        }

        return $this->success(
            $this->uploadFiles($request, $fileKey, $filePath)
        );
    }

    public function deleteFile(string $filePath, string $fileName)
    {   //dd("$filePath/$fileName");
        if (file_exists(storage_path("$filePath/$fileName"))) {
            //dd(storage_path("$filePath/$fileName"));
            unlink(storage_path("$filePath/$fileName"));
        }
        return (bool) FileUpload::where('file_system_name', $fileName)->delete();
    }

    public function getUploadedFileOriginalName(string $filename): string|null
    {
        $fileUpload = FileUpload::select('file_name')
            ->where('file_system_name', $filename)
            ->first();

        return $fileUpload?->file_name;
    }

    public static function originalName(string $filename): string|null
    {
        $fileUpload = FileUpload::select('file_name')
            ->where('file_system_name', $filename)
            ->first();

        return $fileUpload?->file_name;
    }

    public static function getFileUploadIdBySystemName(string $fileSystemName)
    {
        return FileUpload::where('file_system_name', $fileSystemName)->value('id');
    }
}
