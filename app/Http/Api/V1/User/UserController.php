<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\User;

use App\Http\Controllers\ApiController;

use App\Http\Api\V1\User\Contracts\UserServiceInterface;

use Illuminate\Http\Request;

final class UserController extends ApiController 
{
    public function __construct(protected UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        try 
        {  
            return $this->success($this->userService->getUsers());
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }

    public function store(Request $request, ?string $id = null)
    {
        try 
        {
            $validator = UserValidation::validate($request->all(), $id);

            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }

            $userDTO = UserDTO::create($validator->validated(), $id);
            $result = $this->userService->save($userDTO, $id);
            
            return ($id) ? $this->updated($result) : $this->created($result);
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }

    public function getUser(string $id)
    {
        try 
        {
            return $this->success($this->userService->getUser($id));
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }
	
	
}