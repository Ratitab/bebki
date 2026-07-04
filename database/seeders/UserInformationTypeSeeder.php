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
            'first_name',
            'last_name',
            'gender',
            'email',
            'phone',
            'shop_status',
            'address',
            'building',
            'entrance',
            'floor',
            'apartment',
            'city',
            'country',
        ];

        foreach ($types as $name) {
            DB::table('user_information_types')->updateOrInsert(
                ['name' => $name],
                ['is_required' => false, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
