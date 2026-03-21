<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CompanyInformationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $fields = [
            ['name' => 'name',            'is_required' => 0],
            ['name' => 'logo',            'is_required' => 0],
            ['name' => 'description',     'is_required' => 0],
            ['name' => 'email',           'is_required' => 0],
            ['name' => 'phone_numbers',   'is_required' => 0],
            ['name' => 'is_vat_payer',    'is_required' => 0],
        ];

        foreach ($fields as $field) {
            DB::table('company_information_types')->insert([
                'name' => $field['name'],
                'is_required' => $field['is_required'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
