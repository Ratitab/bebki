<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

//        User::factory()->create([
//            'name' => 'Test User',
//            'email' => 'test@example.com',
//        ]);
        $this->call(UserInformationTypeSeeder::class);
        $this->call(CompanyInformationTypeSeeder::class);
        $this->call(SupplierUnit::class);
        $this->call(TaxRate::class);
        $this->call(CountriesSeeder::class);
        $this->call(CitiesSeeder::class);
        $this->call(SidebarRoutesSeeder::class);
    }
}
