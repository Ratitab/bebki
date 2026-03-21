<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierUnit extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            'ცალი',
            'გრამი',
            'ლიტრი',
            'ტონა',
            'სანტიმეტრი',
            'მეტრი',
            'კილომეტრი',
            'კვ.სმ',
            'კვ.მ',
            'მ³',
            'მილილიტრი',
            'კგ',
            'შეკვრა',
        ];

        foreach ($units as $unit) {
            \DB::connection('mongodb')->table('supplier_units')->insert([
                'title' => $unit,
            ]);}
    }
}
