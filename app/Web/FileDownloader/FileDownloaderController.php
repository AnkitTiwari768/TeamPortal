<?php 

declare(strict_types=1);

namespace App\Web\FileDownloader;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\{HasFileUpload, HasDraft};
use App\Http\Controllers\ClientController;
use App\Http\Api\V1\FileDownloader\{FileDownloaderService, FileDownloaderRequest};

use App\Http\Services\CommonService;


class FileDownloaderController extends ClientController
{
    use HasFileUpload, HasDraft;
	private static string $module = 'legalofficers.index';

    public function __construct(private FileDownloaderService $filedownloaderservice){}

    public function index(string $file_id): View {
        //guard(config('permissions.role-view'));        
        $title = __('message.role_list'); 
		$fileData = $this->filedownloaderservice->getFileDetails($file_id);
		
		$file_name = $fileData->file_name;
		$file_url = asset($fileData->file_path);
		$context = stream_context_create([
			"ssl" => [
				"verify_peer" => false,
				"verify_peer_name" => false,
			],
		]);
		header('Content-Type: application/octet-stream');
		header("Content-Transfer-Encoding: Binary"); 
		header("Content-disposition: attachment; filename=\"".$file_name."\"");
		//readfile($file_url);
		readfile($file_url, false, $context);
		exit;
    }

}