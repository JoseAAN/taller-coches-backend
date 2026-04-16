<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Database\Seeders\ProductsSeeder;
use Database\Seeders\CategoriesSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = \App\Models\Role::firstOrCreate(['name' => 'admin']);
        $clientRole = \App\Models\Role::firstOrCreate(['name' => 'client']);

        $this->call([
            UserSeeder::class,  
            ItemTypeSeeder::class,
            CategoriesSeeder::class,
            ProductsSeeder::class,
            ServiceSeeder::class,
            CartSeeder::class,
            InvoiceSeeder::class,
            AdminNavigationItemSeeder::class,
            VehicleTypeSeeder::class,
            VehiclesSeeder::class,
            DemoUserSeeder::class,
            ServiceImageSeeder::class,
        ]);
    }
}
