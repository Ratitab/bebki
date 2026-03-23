<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // type_id 1 = product types, type_id 0 = materials
        $categories = [
            // Product types (type_id = 1)
            ['title' => 'Apparel',     'type_id' => 1, 'order_id' => 1],
            ['title' => 'Accessories', 'type_id' => 1, 'order_id' => 1],
            ['title' => 'Decoration',  'type_id' => 1, 'order_id' => 1],
            ['title' => 'Tableware',   'type_id' => 1, 'order_id' => 1],
            ['title' => 'Kids',        'type_id' => 1, 'order_id' => 1],
            ['title' => 'Jewellary',   'type_id' => 1, 'order_id' => 1],
            ['title' => 'Other',       'type_id' => 1, 'order_id' => 2],

            // Materials (type_id = 0)
            ['title' => 'Ceramics',    'type_id' => 0, 'order_id' => 1],
            ['title' => 'Textiles',    'type_id' => 0, 'order_id' => 1],
            ['title' => 'Woodwork',    'type_id' => 0, 'order_id' => 1],
            ['title' => 'Leather',     'type_id' => 0, 'order_id' => 1],
            ['title' => 'Concrete',    'type_id' => 0, 'order_id' => 1],
            ['title' => 'Metalwork',   'type_id' => 0, 'order_id' => 1],
            ['title' => 'Other',       'type_id' => 0, 'order_id' => 2],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->insertOrIgnore([
                'title'      => $cat['title'],
                'type_id'    => $cat['type_id'],
                'order_id'   => $cat['order_id'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
