<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\ClientController;
use App\Http\Api\V1\User\UserValidation as Validation;
use Illuminate\Http\Request;
use App\Rules\FileName;
use App\Http\Api\V1\User\UserService as UserService;
use App\Http\Api\V1\UserProfile\{UserProfileService, UserProfileValidation};
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use App\Http\Api\V1\FileUpload\FileUploadService;
use App\Http\Services\CommonService;
use App\Http\Api\V1\CustomUser\{CustomUserService, CustomUserRequest, CustomUserDto};
use Illuminate\Support\Facades\DB;
use Session;

final class ProfileController extends ClientController
{
    protected $module_url = 'users.index';
    protected $service;

    public function __construct(UserService $service, FileUploadService $fileUploadService, CommonService $commonService)
    {
        $this->service = $service;
        $this->fileUploadService = $fileUploadService;
        $this->commonService = $commonService;
    }


    public function index()
    {
        crypto_secrets();
        $id = auth()->user()->id;
        $isProfile = false;
        $isSnp = false;
        $isBnp = false;
        $module_url = $this->module_url;
        if (hasRole('snp')) {
            $row = (array) $this->service->getSnpDetail($id);
            //dd($row);
            $isSnp = true;
            $view = 'users.snpProfile';
        } else if (hasRole('bnp')) {
            $row = (array) $this->service->getBnpDetail($id);
            //dd($row);
            $isBnp = true;
            $view = 'users.bnpProfile';
        } else {
            $row = (array) $this->service->getUser($id);
            $isProfile = true;
            $view = 'users.form';
        }
        //dd($isSnp);


        return view($view, compact('id', 'row', 'isProfile', 'isSnp', 'isBnp', 'module_url'))
            ->with('title', __('message.your_profile'))
            ->with('details', (object) $this->service->getDetails($id))
            ->with('crypto_salt', session('crypto_salt'))
            ->with('crypto_iv', session('crypto_iv'))
            ->with('crypto_key', session('crypto_key'))
            ->with('crypto_key_size', session('crypto_key_size'))
            ->with('crypto_iterations', session('crypto_iterations'));
    }


    public function update(Request $request, $id)
    {
        $parentUserId = DB::table('users')->where('id', $id)->value('parent_user_id');

        try {
            if (empty($parentUserId) && hasRole('snp')) {

                $rules = UserProfileValidation::getSnprules($id);
            } else if (empty($parentUserId) && hasRole('bnp')) {

                $rules = UserProfileValidation::getBnprules($id);
            } else {

                $rules = UserProfileValidation::getRules($id);
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return $this->error($validator->errors());
            }

            // $this->service->updateApplicant($request->all(), $id);
            (new UserProfileService())->updateApplicant($request->all(), $id);
            return $this->updated();
        } catch (Throwable $exception) {
            return $this->handler($error);
        }
    }

    public function updateCustomUserProfile(Request $request, string $userId)
    {
        $validationResult = CustomUserRequest::validateRequest($request, $userId);
        if (! $validationResult->status()) return $this->error($validationResult->errors());

        $result = app(CustomUserService::class)->updateUser(CustomUserDto::fromRequest($validationResult->validated()), $userId);
        return (! $result->status())
            ? $this->error($result->message())
            : $this->updated();
    }

    public function profilePhoto(Request $request)
    {
        try {
            $file = $request->file('images');
            $originalFileName = explode('.', $file->getClientOriginalName());

            if (count($originalFileName) > 2) {
                return $this->error([], 'Double extention is not allowed');
            }

            $validator = Validator::make($request->all(), [
                'images' => [
                    'required',
                    'mimes:png,jpg,jpeg',
                    'max:200',
                    new FileName
                ]
            ]);

            if ($validator->fails()) {
                return $this->error($validator->errors());
            }
            $path = config('constant.user_image_file_path');
            $response = $this->fileUploadService->uploadImage($request->file('images'), $path);
            return $this->success($response);
        } catch (Throwable $exception) {
            return $this->handler($error);
        }
    }

    public function updatePhoto(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'photo_file_upload_id' => [
                    'required',
                    'exists:file_uploads,id'
                ]
            ]);

            if ($validator->fails())
                return $this->error($validator->errors());
            //dd($validator->validated());
            (new UserProfileService())->updatePhoto($validator->validated(), $id);
            //$this->service->updatePhoto($validator->validated(), $id);
            return $this->updated();
        } catch (Throwable $exception) {
            return $this->handler($error);
        }
    }

    public function checkIfExistData($payload)
    {
        if ($payload['mobile']) {
            $result1 = $this->commonService->getExistData('users', 'mobile', $payload['mobile']);
            if ($result1) {
                Session::put('mobile', "1");
                Session::put('mobile_value', $payload['mobile']);
            } else {
                if (Session::get('mobile') != 1) {
                    Session::put('mobile', "");
                    Session::put('mobile_value', "");
                }
            }
        }

        if ($payload['email']) {
            $result2 = $this->commonService->getExistData('users', 'email', $payload['email']);
            if ($result2) {
                Session::put('email', "1");
                Session::put('email_value', $payload['email']);
            } else {
                if (Session::get('email') != 1) {
                    Session::put('email', "");
                    Session::put('email_value', "");
                }
            }
        }
    }
}
