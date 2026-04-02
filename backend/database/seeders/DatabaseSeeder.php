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
        // Crear Roles
        // Usamos firstOrCreate para evitar errores si se corre varias veces
        $adminRole = \App\Models\Role::firstOrCreate(['name' => 'admin']);
        $clientRole = \App\Models\Role::firstOrCreate(['name' => 'client']);

        // Crear Usuario ADMIN
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => bcrypt('admin1234'),
            'role_id' => $adminRole->id, // Asignar ID
        ]);

        // Crear 9 Usuarios CLIENTE (podemos crear más si queremos)
        User::factory(9)->create([
            'password' => bcrypt('client1234'),
            'role_id' => $clientRole->id, // Asignar ID
        ]);

        $this->call([
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
