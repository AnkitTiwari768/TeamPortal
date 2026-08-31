<?php 

declare(strict_types=1);

namespace App\Web\Search;

use App\Http\Api\V1\RoleDependency\RoleDependencyService;
use App\Http\Controllers\ClientController;
use App\Http\Api\V1\Search\SearchService;
use Illuminate\Http\Request;

class SearchController extends ClientController
{
    public function __construct(private SearchService $searchService) {}

    public function searchByRole(Request $request)
    {
        $phrase = $request->has('search') ? $request->query('search') : null;

        return $this->success(
            $this->searchService->searchByRole($phrase)
        );
    }

    public function getCustomUserRoles(Request $request)
    {
        $phrase = $request->has('search') ? $request->query('search') : null;

        return $this->success(
            $this->searchService->getCustomUserRoles($phrase)
        );
    }

    public function getRoleDependency($roleId, RoleDependencyService $roleDependencyService)
    {
        return $this->success($roleDependencyService->getDependencyOptionsByRoleId($roleId));
    }
}