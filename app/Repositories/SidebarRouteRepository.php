<?php

namespace App\Repositories;

use App\Models\Navigation\SidebarRoute;

class SidebarRouteRepository
{
    public function __construct(
        private readonly SidebarRoute $sidebarRouteModel,
    ) {
    }

    /**
     * Get sidebar routes document by key. Returns merged routes (sidebar + support) or empty array.
     */
    public function findAll(string $key = 'sidebar'): array
    {
        $doc = $this->sidebarRouteModel->where('key', $key)->first();

        if (!$doc) {
            return [];
        }

        $sidebar = $doc->routes ?? [];
        $support = $doc->support_routes ?? [];

        return array_merge(
            is_array($sidebar) ? $sidebar : [],
            is_array($support) ? $support : []
        );
    }

    /**
     * Update or create the routes document.
     */
    public function upsert(string $key, array $routes, array $supportRoutes = []): SidebarRoute
    {
        return $this->sidebarRouteModel->updateOrCreate(
            ['key' => $key],
            ['routes' => $routes, 'support_routes' => $supportRoutes]
        );
    }
}
