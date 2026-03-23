<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CompanyInformationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $fields = [
            'name',
            'logo',
            'description',
            'email',
            'phone_numbers',
            'is_vat_payer',
            'client_text',
            'social_network',
            'product_images',
            'sell_type',
            'lead_time',
            'primary_craft',
            'city',
            'cover_image',
            'portfolio_images',
            'verification_documents',
            'bio',
            'experience',
            'IBAN',
            'company_id',
        ];

        foreach ($fields as $name) {
            DB::table('company_information_types')->insertOrIgnore([
                'name'       => $name,
                'is_required' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
