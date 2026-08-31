<?php

declare(strict_types=1);

namespace App\Modules\User;

use App\Http\Api\V1\User\{SaveUserAction, UserService, UserDTO, UserValidation};
use App\Http\Api\V1\Common\{SearchService, SearchRepository};
use App\Http\Api\V1\UserPermission\UserPermissionService;
use App\Http\Controllers\ClientController;
use App\Http\Api\V1\User\UserValidation as Validation;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Services\VerificationService;
use App\Http\Services\CommonService;
use Session;
use Illuminate\Support\Facades\Validator;


final class UserController extends ClientController
{
    private static $module = 'users.index';
    protected $commonService;

    public function __construct(
        private UserService $service,
        CommonService $commonService,
        private UserPermissionService $userPermissionService
    )
    {
        $this->service = $service;
        $this->commonService = $commonService;
    }

    public function index(): View
    {
        guard(config('permissions.user-view'));
        return view('users.index')
            ->with('title', __('message.user_list'))
            ->with('details', (object) $this->service->getDetails());
    }

    public function datalist(): mixed
    {
        guard(config('permissions.user-view'));
        try {
            $result = $this->service->getUsers();
            return $this->success($result);
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function create(): View
    {
        guard(config('permissions.user-create'));
        crypto_secrets();
        return view('users.form')
            ->with('title', __('message.add_user'))
            ->with('module_url', self::$module)
            ->with('details', (object) $this->service->getDetails())
            ->with('crypto_salt', session('crypto_salt'))
            ->with('crypto_iv', session('crypto_iv'))
            ->with('crypto_key', session('crypto_key'))
            ->with('crypto_key_size', session('crypto_key_size'))
            ->with('crypto_iterations', session('crypto_iterations'));
    }


    public function store(Request $request, SaveUserAction $action)
    {
        guard(config('permissions.user-create'));

        $hasNetworkPartcipantRole = (hasRole('snp') || hasRole('bnp') || hasRole('lsp') || hasRole('nsic') || hasRole('nsic-finance') || hasRole('ondc-admin'));

        if (! $hasNetworkPartcipantRole) {
            $request->merge([
                'password' => crypto_decrypt($request->password),
                'password_confirmation' => crypto_decrypt($request->password_confirmation),
            ]);
        }

        $rules = $hasNetworkPartcipantRole
            ? UserValidation::getNetworkParticipantSubUserRules()
            : UserValidation::getRules();


        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->error($validator->errors());
        }

        $result = $action->execute(UserDTO::create($validator->validated()));

        return $this->created($result);
    }

    public function update(Request $request, SaveUserAction $action, string $id)
    {
        guard(config('permissions.user-update'));

        $hasNetworkPartcipantRole = (hasRole('snp') || hasRole('bnp') || hasRole('lsp'));

        $rules = $hasNetworkPartcipantRole
            ? UserValidation::getNetworkParticipantSubUserRules($id)
            : UserValidation::getRules($id);


        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->error($validator->errors());
        }

        $result = $action->execute(UserDTO::create($validator->validated($id)), $id);

        return $this->created($result);
    }

    // public function store(Request $request) 
    // {   
    //     //print_r($request->all());exit;
    // 	guard(config('permissions.user-create'));
    //     try{        

    // 		$request->merge([
    //             'password' => crypto_decrypt($request->password),
    //             'password_confirmation' => crypto_decrypt($request->password_confirmation),
    //         ]);

    //         $validator = Validation::validate($request->all());

    //         if ($validator->fails()) 
    //         {
    //             return $this->error($validator->errors());
    //         }


    // 		 $data=$validator->validated();
    // 		 $data['is_role_mapped']=1;

    // 		 $userDTO = UserDTO::create($data);
    //          //dd($userDTO);
    //          $result = $this->service->save($userDTO);


    //         return $this->created($result);
    //     }catch(\Throwable $e){
    //         return $this->handleException($e);
    //     } 
    // }

    public function edit(string $id): View
    {
        guard(config('permissions.user-update'));
        //dd($this->service->getUser($id));
        return view('users.form')
            ->with('title', __('message.user_state'))
            ->with('module_url', self::$module)
            ->with('id', $id)
            ->with('row', (array)$this->service->getUser($id))
            ->with('details', (object)$this->service->getDetails($id))
            ->with('title', __('message.edit_user'))
            ->with('crypto_salt', session('crypto_salt'))
            ->with('crypto_iv', session('crypto_iv'))
            ->with('crypto_key', session('crypto_key'))
            ->with('crypto_key_size', session('crypto_key_size'))
            ->with('crypto_iterations', session('crypto_iterations'));
    }

    public function show($id)
    {
        guard(config('permissions.user-view'));
        try {
            $module_url = self::$module;
            $row = (array) $this->service->getUser($id);
            $assignedPermissions = $this->userPermissionService->getAssignedPermissions($id);
            //echo "<pre/>";print_r($row);exit;
            return view('users.details', compact('id', 'row', 'module_url', 'assignedPermissions'))
                ->with('title', __('message.user_details'));
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    // public function update(Request $request, string $id): mixed
    // {
    //     guard(config('permissions.user-update'));
    //     try {

    //         $validator = Validation::validate($request->all(), $id);

    //         if ($validator->fails()) {
    //             return $this->error($validator->errors());
    //         }

    //         $data = $validator->validated();
    //         $data['is_role_mapped'] = 1;
    //         $userDTO = UserDTO::create($data, $id);
    //         $result = $this->service->save($userDTO, $id);

    //         return $this->updated();
    //     } catch (\Throwable $e) {
    //         return $this->handleException($e);
    //     }
    // }


    public function getSearchRoles(Request $request)
    {
        if ($request->has('search')) {
            $searchService = new SearchService(new SearchRepository());
            return $searchService->getSearchList('roles', $request->query('search'));
        }
    }
}
