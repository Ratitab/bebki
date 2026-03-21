<?php

namespace Database\Seeders;

use App\Models\Navigation\SidebarRoute;
use Illuminate\Database\Seeder;

class SidebarRoutesSeeder extends Seeder
{
    /**
     * Seed the sidebar_routes collection with the default navigation tree.
     * Source: bebki_front src/utils/routes.ts
     */
    public function run(): void
    {
        $sidebarRoutes = [
            [
                'id' => 1,
                'icon' => 'categories',
                'item' => 'კატეგორიები',
                'subRoutes' => [
                    ['id' => 13, 'icon' => 'category', 'item' => 'ტანისამოსი', 'path' => '/categories/apparel'],
                    ['id' => 11, 'icon' => 'category', 'item' => 'სამკაული', 'path' => '/categories/jewelry'],
                    [
                        'id' => 12,
                        'icon' => 'category',
                        'item' => 'აქსესუარი',
                        'subRoutes' => [
                            ['id' => 121, 'icon' => 'category', 'item' => 'ჩანთა', 'path' => '/categories/accessory'],
                            ['id' => 122, 'icon' => 'category', 'item' => 'საფულე', 'path' => '/categories/accessory'],
                            ['id' => 123, 'icon' => 'category', 'item' => 'ქამარი', 'path' => '/categories/accessory'],
                            ['id' => 124, 'icon' => 'category', 'item' => 'იარაღი', 'path' => '/categories/accessory'],
                            ['id' => 125, 'icon' => 'category', 'item' => 'საბარათე', 'path' => '/categories/accessory'],
                            ['id' => 126, 'icon' => 'category', 'item' => 'ქუდი', 'path' => '/categories/accessory'],
                        ],
                    ],
                    ['id' => 14, 'icon' => 'category', 'item' => 'დეკორაცია', 'path' => '/categories/decoration'],
                    ['id' => 15, 'icon' => 'category', 'item' => 'ჭურჭელი', 'path' => '/categories/household'],
                    ['id' => 16, 'icon' => 'category', 'item' => 'იარაღი/აქსესუარები', 'path' => '/categories/weapon-accessory'],
                    ['id' => 17, 'icon' => 'category', 'item' => 'ტანისამოსი', 'path' => '/categories/apparel'],
                    ['id' => 18, 'icon' => 'category', 'item' => 'უნიკალური ნამუშევრები', 'path' => '/categories/collectable-design'],
                    ['id' => 19, 'icon' => 'category', 'item' => 'სათამაშოები', 'path' => '/categories/toys'],
                ],
            ],
            [
                'id' => 2,
                'icon' => 'materials',
                'item' => 'მასალები',
                'subRoutes' => [
                    ['id' => 21, 'icon' => 'material', 'item' => 'ტყავი', 'path' => '/materials/leather'],
                    ['id' => 22, 'icon' => 'material', 'item' => 'ხე', 'path' => '/materials/wood'],
                    ['id' => 23, 'icon' => 'material', 'item' => 'კერამიკა', 'path' => '/materials/ceramics'],
                    ['id' => 24, 'icon' => 'material', 'item' => 'პლასტმასი/პოლიმერი', 'path' => '/materials/plastic'],
                    ['id' => 25, 'icon' => 'material', 'item' => 'ბეტონი', 'path' => '/materials/concrete'],
                    ['id' => 26, 'icon' => 'material', 'item' => 'მეტალი', 'path' => '/materials/metal'],
                    ['id' => 27, 'icon' => 'material', 'item' => 'ქვა', 'path' => '/materials/stone'],
                    ['id' => 28, 'icon' => 'material', 'item' => 'ძრიფასი ქვა', 'path' => '/materials/gems'],
                    ['id' => 29, 'icon' => 'material', 'item' => 'ქსოვილი', 'path' => '/materials/textile'],
                    ['id' => 291, 'icon' => 'material', 'item' => 'სანთელი', 'path' => '/materials/wax'],
                ],
            ],
            [
                'id' => 3,
                'icon' => 'shops',
                'item' => 'ოსტატები',
                'subRoutes' => [
                    ['id' => 31, 'icon' => 'shop', 'item' => 'მარაგში', 'path' => '/shops/in-stock'],
                    ['id' => 32, 'icon' => 'shop', 'item' => 'შეკვეთით', 'path' => '/shops/order'],
                ],
            ],
        ];

        $supportRoutes = [];

        SidebarRoute::updateOrCreate(
            ['key' => 'sidebar'],
            [
                'routes' => $sidebarRoutes,
                'support_routes' => $supportRoutes,
            ]
        );
    }
}
