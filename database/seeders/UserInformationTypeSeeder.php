<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class UserInformationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $types = [
            ['name' => 'first_name',  'is_required' => false],
            ['name' => 'last_name',   'is_required' => false],
            ['name' => 'gender',      'is_required' => false],
            ['name' => 'email',       'is_required' => false],
            ['name' => 'phone',       'is_required' => false],
            ['name' => 'shop_status', 'is_required' => false],
            ['name' => 'address',     'is_required' => false],
            ['name' => 'city',        'is_required' => false],
            ['name' => 'country',     'is_required' => false],
        ];

        foreach ($types as $type) {
            DB::table('user_information_types')->insert([
                'name' => $type['name'],
                'is_required' => $type['is_required'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
