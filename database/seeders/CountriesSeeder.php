<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountriesSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('countries')->where('id', 1)->exists()) {
            return;
        }

        DB::table('countries')->insert([
            'id' => 1,
            'name' => 'საქართველო',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
