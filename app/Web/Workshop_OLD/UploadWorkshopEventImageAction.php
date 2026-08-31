<?php

declare(strict_types=1);

namespace App\Web\Workshop;

use App\Utils\UuidGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UploadWorkshopEventImageAction
{
    public function execute(UploadWorkshopEventImageRequest $request, UploadWorkshopEventImageDto $dto): array
    {
        return DB::transaction(function () use ($request, $dto) {
            $uploadedIds = [];

            foreach ($request->file('files') as $file) {

                $fileUploadData = [
                    'id' => UuidGenerator::uuid7(),
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => config('upload.event_image_path'),
                    'file_system_name' => UuidGenerator::uuid7() . '.' . $file->getClientOriginalExtension(),
                    'file_extension' => $file->getClientOriginalExtension(),
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'created_at' => Carbon::now(),
                    'created_by' => auth()->user()->id
                ];
                // dd($fileUploadData['file_path'],$fileUploadData['file_system_name']);
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
