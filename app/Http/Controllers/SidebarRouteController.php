<?php

namespace App\Http\Controllers;

use App\Services\SidebarRouteService;
use App\Traits\Resp;

class SidebarRouteController extends Controller
{
    use Resp;

    public function __construct(private readonly SidebarRouteService $sidebarRouteService)
    {
    }

    /**
     * Return sidebar + support routes. Public endpoint (no auth).
     */
    public function index()
    {
        $routes = $this->sidebarRouteService->getRoutes();

        return $this->apiResponseSuccess(['data' => $routes]);
    }
}
