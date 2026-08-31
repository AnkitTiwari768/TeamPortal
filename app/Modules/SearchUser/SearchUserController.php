<?php declare(strict_types=1); 

namespace App\Modules\SearchUser; 
use App\Http\Controllers\ClientController;
use App\Exceptions\ValidationException;
use App\Http\Api\V1\Common\SearchUserController as Service;
use Illuminate\Http\Request;

final class SearchUserController extends ClientController 
{
    public function __construct(Service $service)
    {
        $this->service = $service;
    }


    public function getSearchUsers(Request $request)
    {
		if ($request->has('search')) 
		{
			return $this->service->getUsers($request->query('search'));
		}
    }

    
    
}