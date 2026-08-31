<?php 
declare(strict_types=1);
namespace App\Http\Api\V1\UserProfile;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Api\V1\FileUpload\FileUploadService;
use App\Rules\FileName;

final class UserProfileController extends ApiController 
{
    public function __construct(protected UserProfileService $service,FileUploadService $fileUploadService)
    {
        $this->service = $service;
        $this->fileUploadService = $fileUploadService;
    }


    public function index()
    {   
        try 
        {   
            $result = $this->service->findById(AuthId());
            return $this->success($result);
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }

    public function update(Request $request)
    {
        try 
        {
            $validator = UserProfileValidation::validate($request->all(), AuthId());

            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }

            $result = $this->service->save($validator->validated());
            return $this->success($result);

        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }

    public function profilePhoto(Request $request)
    {
        try
        {
            $file=$request->file('images');
            $originalFileName = explode('.',$file->getClientOriginalName());
            
            if(count($originalFileName) > 2) {
                return $this->error([],'Double extention is not allowed');
            }
            
             $validator = Validator::make($request->all(), [
                'images' => [
                    'required','mimes:png,jpg,jpeg','max:200',new FileName
                ]
            ]);
    
            if ($validator->fails()) {
                return $this->error($validator->errors());
            }  
            $path = config('constant.user_image_file_path');
            $response = $this->fileUploadService->uploadImage($request->file('images'), $path);
            return $this->success($response);
        }
        catch (Throwable $exception) 
        {
            return $this->handler($error);
        }
    }


    public function updatePhoto(Request $request) 
    {  
        try 
        {
            $validator = Validator::make($request->all(), [
                'photo_file_upload_id' => [
                    'required','exists:file_uploads,id'
                ]
            ]);            
            if ($validator->fails()) 
                return $this->error($validator->errors()); 

            $this->service->updatePhoto($validator->validated(), AuthId());
            return $this->updated();
        }
        catch (Throwable $exception) 
        {
            return $this->handler($error);
        }
    }
    
}