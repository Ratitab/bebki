<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaxRate extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxRates = [
            'დაუბეგრავი',
            'ნულოვანი',
            'ჩვეულებრივი',
        ];

        foreach ($taxRates as $taxRate) {
            \DB::connection('mongodb')->table('tax_rates')->insert([
                'title' => $taxRate,
            ]);}
    }
}
