<?php

declare(strict_types=1);

namespace App\Web\Claim;

use App\Utils\UuidGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UploadClaimAction
{
    public function execute(UploadClaimRequest $request, UploadClaimDto $dto): array
    {
        return DB::transaction(function () use ($request, $dto) {
            $file = $request->file('file');

            if (!$file) {
                throw new \InvalidArgumentException('File is required.');
            }

            $fileUploadData = [
                'id' => UuidGenerator::uuid7(),
                'file_name' => $file->getClientOriginalName(),
                'file_path' => config('upload.claim_document_path'),
                'file_system_name' => UuidGenerator::uuid7() . '.' . $file->getClientOriginalExtension(),
                'file_extension' => $file->getClientOriginalExtension(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'created_at' => Carbon::now(),
                'created_by' => auth()->user()->id
            ];

            $file->storeAs($fileUploadData['file_path'], $fileUploadData['file_system_name']);

            DB::table('file_uploads')->insert($fileUploadData);

            return [
                'claim_document' => $dto->document_category_id . '|' . $fileUploadData['id'],
            ];
        });
    }
}
