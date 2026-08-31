<?php

declare(strict_types=1);

namespace App\Http\Api\V1\AdminWorkshop;

use App\Utils\UuidGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UploadDocumentAction
{
    public function execute(UploadDocumentRequest $request, UploadDocumentDto $dto): array
    {
        return DB::transaction(function () use ($request, $dto) {
            $uploadedIds = [];

            foreach ($request->file('files') as $file) {

                $fileUploadData = [
                    'id' => UuidGenerator::uuid7(),
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => config('upload.admin_workshop_document'),
                    'file_system_name' => UuidGenerator::uuid7() . '.' . $file->getClientOriginalExtension(),
                    'file_extension' => $file->getClientOriginalExtension(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'created_at' => Carbon::now(),
                    'created_by' => auth()->user()->id
                ];

                $file->storeAs($fileUploadData['file_path'], $fileUploadData['file_system_name']);

                DB::table('file_uploads')->insert($fileUploadData);

                $uploadedIds[] = $fileUploadData['id'];
            }

            return [
                'message' => 'Files uploaded successfully',
                'uploaded_ids' => $uploadedIds,
            ];
        });
    }
}
