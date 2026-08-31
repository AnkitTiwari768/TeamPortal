<?php

declare(strict_types=1);

namespace App\Domain\Upload;

use App\Traits\HasRecursivePermission;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UploadAction
{
    use HasRecursivePermission;

    public function execute(UploadRequest $request): array
    {
        return DB::transaction(function () use ($request) {
            $file = $request->file('file');
            $directoryPath =  storage_path("app/uploads/") . request()->input('document_category_id');
            $uploadPath =  'uploads/' . request()->input('document_category_id');
            // dd($uploadPath, $directoryPath);

            $fileUploadData = [
                'id' => uuid(),
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $uploadPath,
                'file_system_name' => uuid() . '.' . $file->getClientOriginalExtension(),
                'file_extension' => $file->getClientOriginalExtension(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_at' => Carbon::now(),
                'uploaded_by' => auth()->user()?->id ?? null
            ];

            // $this->recursiveZipPermission($directoryPath);

            $file->storeAs($uploadPath, $fileUploadData['file_system_name']);

            DB::table('file_uploads')->insert($fileUploadData);

            return [
                'file_id' => $fileUploadData['id'],
                'file_name' => $fileUploadData['file_system_name'],
            ];
        });
    }
}
