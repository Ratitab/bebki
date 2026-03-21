<?php

namespace App\Services;

use App\Repositories\SidebarRouteRepository;

class SidebarRouteService
{
    public function __construct(
        private readonly SidebarRouteRepository $sidebarRouteRepository,
    ) {
    }

    /**
     * Get all sidebar + support routes for the frontend.
     *
     * @return array<int, array{id: int, icon: string, item: string, path?: string, subRoutes?: array}>
     */
    public function getRoutes(): array
    {
        return $this->sidebarRouteRepository->findAll('sidebar');
    }
}
