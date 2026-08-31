<?php declare(strict_types=1);

namespace App\Http\Api\V1\ModulePermissionTree;

use App\Http\Services\ApiService;
use App\Http\Api\V1\ModulePermissionTree\ModulePermissionTreeRepository as Repository;

class ModulePermissionTreeService extends ApiService 
{
    private const CHILD_EXISTS = 1;
    private const CHILD_NOT_EXISTS = 0;

    public function __construct(private Repository $repository)
    {
        $this->repository = $repository;
    }

    public function getModulePermissionTree(string $roleId): array
    {
        return $this->buildModulePermissionTree($roleId);
    }

    private function buildModulePermissionTree(string $roleId, ?string $parentId = null): array
    {
        $tree = [];

        if ($modules = $this->repository->getModulesByParentId($parentId)) 
        {
            foreach ($modules as $module) 
            {
                $children = $this->buildModulePermissionTree($roleId, $module->id);

                $permissions = $this->repository->getPermissionsByModuleId($roleId, $module->id);

                if (count($children) === 0 && count($permissions) === 0)
                {
                    continue;
                }

                $moduleName = trim((string) $module->name);
                if (empty($moduleName) && !empty($module->slug)) {
                    $moduleName = ucwords(str_replace('-', ' ', $module->slug));
                }

                $treeData = [
                    'id' => $module->id,
                    'name' => $moduleName,
                    'have_children' => count($children) > 0 ? self::CHILD_EXISTS : self::CHILD_NOT_EXISTS,
                    'children' => $children,
                    'have_permissions' => count($permissions) > 0 ? self::CHILD_EXISTS : self::CHILD_NOT_EXISTS,
                    'permissions' => $permissions
                ];
    
                $tree[] = $treeData;
            }
        }

        return $tree;
    }

    public function getUserPermissionTree(string $userId): array
    {
        return $this->buildUserPermissionTree($userId);
    }

    private function buildUserPermissionTree(string $userId, ?string $parentId = null): array
    {
        $tree = [];

        if ($modules = $this->repository->getModulesByParentId($parentId)) 
        {
            foreach ($modules as $module) 
            {
                $children = $this->buildUserPermissionTree($userId, $module->id);

                $permissions = $this->repository->getPermissionsByUserId($userId, $module->id);

                if (count($children) === 0 && count($permissions) === 0)
                {
                    continue;
                }
                
                $moduleName = trim((string) $module->name);
                if (empty($moduleName) && !empty($module->slug)) {
                    $moduleName = ucwords(str_replace('-', ' ', $module->slug));
                }

                $treeData = [
                    'id' => $module->id,
                    'name' => $moduleName,
                    'have_children' => count($children) > 0 ? self::CHILD_EXISTS : self::CHILD_NOT_EXISTS,
                    'children' => $children,
                    'have_permissions' => count($permissions) > 0 ? self::CHILD_EXISTS : self::CHILD_NOT_EXISTS,
                    'permissions' => $permissions
                ];
    
                $tree[] = $treeData;
            }
        }

        return $tree;
    }

}