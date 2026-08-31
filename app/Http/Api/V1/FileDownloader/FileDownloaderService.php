<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\FileDownloader;
use App\Http\Api\V1\User\UserController;
use App\Http\Services\ApiService;
use App\Traits\DataTable;
use Illuminate\Http\Request;

class FileDownloaderService 
{
    use DataTable;   
    
	
	public function getFileDetails($file_id){
		$fileData = \DB::table('file_uploads')
            ->select('*')
			->where('id', $file_id)
			->first();

        return $fileData;
	}
	public function getFileDetailsByName($file_name){
		
		$fileData = \DB::table('file_uploads')
            ->select('*')
			->where('file_system_name', $file_name)
			->first();
		
        return $fileData;
	}
}