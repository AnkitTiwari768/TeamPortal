<?php 

namespace App\Web\Files;

use App\Http\Controllers\ClientController;
use App\Web\Files\FilesService as Service;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class FilesController extends ClientController 
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function index()
    {
		guard('media-view');
		$files=$this->getDirContents('storage/app/uploads/pages');
        return view('files.index',compact('files'))
            ->with('title', __('media.file_list'));
    }

    public function datalist()
    {
        $response = $this->service->getDataTableList(); 
        return $this->success($response);
    }

    public function create()
    {
		guard('media-edit');
		$folders=$this->getNestedFolder('storage/app/uploads/pages');
		$module_url='media';
        return view('files.form',compact('module_url','folders'))
            ->with('title', __('media.add_file'));
    }

    public function store(Request $request) 
    {
		guard('media-edit');
        try 
        {
			$crequest=$request->all();
			$folder_explodes=explode("/",$crequest['select_folders']);
			
			if(!preg_match("/^([a-zA-Z_-]+)$/", $crequest['folder_name'])) {
				return $this->error([],'The folder name field is required and allow alphabet, underscore and dash.');
			}

			if($folder_explodes[0] !='pages'){
				return $this->error([],'Please select folder.');
			}
			
			if(!empty($crequest['select_folders']) && !empty($crequest['folder_name']) && $folder_explodes[0] =='pages') {
				$dir="storage/app/uploads/".$crequest['select_folders']."/".$crequest['folder_name'];
				if(!is_dir($dir)){
					 File::makeDirectory($dir, 0777, true, true);
					 return $this->success([],'Folder created successfully.');
				}else{
					return $this->error([],'This folder name already existed.Please enter another folder name');
				}
			}else{
				return $this->error([],'Folder not created successfully.');
			}
        }
        catch (Throwable $exception) 
        {
            return $this->handler($error);
        }
    }
	
	public function UploadFile(Request $request)
    {
        try
        {
			$file=$request->file('images');
			$originalFileName = explode('.',$file->getClientOriginalName());
	
			
			if(count($originalFileName) > 2) {
				return $this->error([],'Double extention is not allowed');
			}
			
			
             $validator = Validator::make($request->all(), [
                'images' => 'required|mimes:png,jpg,jpeg,pdf,doc,docx,xls,xlsx|max:1024'
            ]);
    
            if ($validator->fails()) {
                return $this->error($validator->errors());
            } 
    
            $response = $this->service->UploadFile($request->file('images'),$request->select_folder);
            return $this->success($response);
        }
        catch (Throwable $exception) 
        {
            return $this->handler($error);
        }
    }
	
	
	public function deleteUploadFile(Request $request)
    {
        try
        {
            $response = $this->service->deleteUploadFile($request->all());
            return $this->success($response);
        }
        catch (Throwable $exception) 
        {
            return $this->handler($error);
        }
    }
	
	public function getDirContents($dir, &$results = array()) {
		$files = scandir($dir);
		foreach ($files as $key => $value) {
			$path = $dir . DIRECTORY_SEPARATOR . $value;
			$ordering=date('Y-m-d H:i:s', filemtime($path));
			if (!is_dir($path)) {
				$results[$ordering] = $path;
			} else if ($value != "." && $value != "..") {
				$this->getDirContents($path, $results);
			}
		}
		krsort($results);
		return $results;
   }
   
   public function getNestedFolder($dir, &$results = array()) {
		$files = scandir($dir);
		foreach ($files as $key => $value) {
			$path = $dir . DIRECTORY_SEPARATOR . $value;
			if (!is_dir($path)) {
				$results[] = $path;
			} else if ($value != "." && $value != "..") {
				$this->getNestedFolder($path, $results);
				$results[] = $path;
			}else if (is_dir($path)) {
				$results[] = $path;
			}
		}
		krsort($results);
		return $results;
   }
}