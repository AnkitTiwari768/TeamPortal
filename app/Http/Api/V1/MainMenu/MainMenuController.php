<?php 

declare(strict_types=1);
namespace App\Http\Api\V1\MainMenu;
use App\Http\Controllers\ApiController;
use App\Http\Services\CommonService;
use Illuminate\Http\Request;

class MainMenuController extends ApiController 
{
    public function __construct(private CommonService $service) 
    {
        $this->CommonService = $service;
    }

    public function index()
    {
        try 
        {
            $result = $this->CommonService->getModuleTree(); 
            return $this->success($result);
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }


    public function getModulesByUserId(string $userId= null)
    {
        $query = \DB::table('user_permissions AS up')
            ->select('m.url')
            ->join('permissions AS p', 'up.permission_id', '=', 'p.id')
            ->join('modules AS m', 'p.module_id', '=', 'm.id')
            ->where('up.user_id', $userId)
            ->whereRaw('p.slug LIKE ?', ['%view%']);

        $userRoles = \DB::table('user_roles')->select('role_id')->where('user_id', $userId)->get()->toArray();

        if ($userRoles) {
            $userRoles = array_column($userRoles, 'role_id');
        }

        $queryRolePermissions = \DB::table('role_permissions AS up')
        ->select('m.url')
        ->join('permissions AS p', 'up.permission_id', '=', 'p.id')
        ->join('modules AS m', 'p.module_id', '=', 'm.id')
        ->whereIn('up.role_id', $userRoles)
        ->whereRaw('p.slug LIKE ?', ['%view%']);


        $userPermissions = $query->get()->toArray();
        $rolePermissions = $queryRolePermissions->get()->toArray();

        return [...$userPermissions, ...$rolePermissions];
    }

    public function getAuthUserAccessableModules(): array
    {   
        return (new MenuRepository())->getUserNavigationMenus(userId: AuthId());

    }
 
}