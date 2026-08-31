<?php

declare(strict_types=1);

namespace App\Web\RouteList;

use App\Http\Controllers\ClientController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RouteListController extends ClientController
{
    public function __construct(private readonly RouteListService $routeListService)
    {
    }

    public function index(): View
    {
        return view('route-list', [
            'title' => 'Route List',
            'filterOptions' => $this->routeListService->filterOptions(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        return $this->success($this->routeListService->filter($request->all()));
    }
}
