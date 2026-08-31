<?php 
declare (strict_types = 1);

namespace App\Web\Files;

use App\Core\BaseService;
use Illuminate\Support\Str;
use DB;


class FilesService extends BaseService 
{

    public function getDataTableList()
    {
        
    }
	
	public function UploadFile($file,$folder_path)
    {
		$originalFileName = $file->getClientOriginalName();
		$filename = time().'_'.$originalFileName;
		return $file->storeAs("uploads/".$folder_path,$filename);
    }
	
	public function deleteUploadFile($payload)
    {
		if(!empty($payload['path'])){
           return  unlink($payload['path']);
		}else{
			return array();
		}
       
    }

}