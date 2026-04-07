<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks and truncate for a clean slate
        DB::statement('TRUNCATE TABLE categories RESTART IDENTITY CASCADE');

        $categories = [
            // ── Product types (type_id = 1) ──────────────────────────────
            ['id' => 1,  'title' => 'apparel',     'type_id' => 1, 'order_id' => 1, 'parent_id' => 0, 'icon' => null],
            ['id' => 2,  'title' => 'accessories', 'type_id' => 1, 'order_id' => 1, 'parent_id' => 0, 'icon' => null],
            ['id' => 3,  'title' => 'decoration',  'type_id' => 1, 'order_id' => 1, 'parent_id' => 0, 'icon' => null],
            ['id' => 4,  'title' => 'tableware',   'type_id' => 1, 'order_id' => 1, 'parent_id' => 0, 'icon' => null],
            ['id' => 5,  'title' => 'kids',        'type_id' => 1, 'order_id' => 1, 'parent_id' => 0, 'icon' => null],
            ['id' => 6,  'title' => 'jewellary',   'type_id' => 1, 'order_id' => 1, 'parent_id' => 0, 'icon' => null],
            ['id' => 7,  'title' => 'other',       'type_id' => 1, 'order_id' => 2, 'parent_id' => 0, 'icon' => '1'],

            // ── Materials (type_id = 0) ───────────────────────────────────
            ['id' => 8,  'title' => 'ceramics',   'type_id' => 0, 'order_id' => 1, 'parent_id' => 0, 'icon' => null],
            ['id' => 9,  'title' => 'textiles',   'type_id' => 0, 'order_id' => 1, 'parent_id' => 0, 'icon' => null],
            ['id' => 10, 'title' => 'woodwork',   'type_id' => 0, 'order_id' => 1, 'parent_id' => 0, 'icon' => null],
            ['id' => 11, 'title' => 'leather',    'type_id' => 0, 'order_id' => 1, 'parent_id' => 0, 'icon' => null],
            ['id' => 12, 'title' => 'concrete',   'type_id' => 0, 'order_id' => 1, 'parent_id' => 0, 'icon' => null],
            ['id' => 13, 'title' => 'metalwork',  'type_id' => 0, 'order_id' => 1, 'parent_id' => 0, 'icon' => null],
            ['id' => 14, 'title' => 'other',      'type_id' => 0, 'order_id' => 2, 'parent_id' => 0, 'icon' => '1'],

            // ── Subcategories of apparel (parent_id = 1) ─────────────────
            ['id' => 15, 'title' => 'shoes',  'type_id' => 1, 'order_id' => 1, 'parent_id' => 1, 'icon' => null],
            ['id' => 16, 'title' => 'Coat',   'type_id' => 1, 'order_id' => 1, 'parent_id' => 1, 'icon' => null],
            ['id' => 18, 'title' => 'kids',   'type_id' => 1, 'order_id' => 1, 'parent_id' => 1, 'icon' => null],
            ['id' => 19, 'title' => 'rings',  'type_id' => 1, 'order_id' => 1, 'parent_id' => 1, 'icon' => null],
            ['id' => 25, 'title' => 'Sports', 'type_id' => 1, 'order_id' => 1, 'parent_id' => 1, 'icon' => null],

            // ── Subcategories of textiles (parent_id = 9) ────────────────
            ['id' => 17, 'title' => 'cotton', 'type_id' => 0, 'order_id' => 1, 'parent_id' => 9, 'icon' => null],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->insert([
                'id'         => $cat['id'],
                'title'      => $cat['title'],
                'type_id'    => $cat['type_id'],
                'order_id'   => $cat['order_id'],
                'parent_id'  => $cat['parent_id'],
                'icon'       => $cat['icon'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Reset the sequence so the next auto-generated id starts after 25
        DB::statement("SELECT setval('categories_id_seq', (SELECT MAX(id) FROM categories))");
    }
}
