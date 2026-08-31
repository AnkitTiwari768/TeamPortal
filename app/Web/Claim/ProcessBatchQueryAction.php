<?php

declare(strict_types=1);

namespace App\Web\Claim;
use Illuminate\Support\Facades\DB;

class ProcessBatchQueryAction
{
    public function execute(array $data)
    {
		DB::transaction(function () use ($data) {
		   DB::table('dy_attachments')
			->where('entity_id', $data['batch_id'])  
			->where('attachment_type_id', $data['document_category_id']) 
			->update([
				'file_path'          => $data['file_upload_id'],
				'uploaded_by'        => authId(),
				'updated_at'         => now(),
			]);
			
			DB::table('dy_batches')
			->where('id',$data['batch_id'])  
			->update([
				'updated_at' => now(),
				'is_query' => 2,
			]);
			
			return true;
		});
    }
}
