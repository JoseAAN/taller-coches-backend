<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener los roles para no depender de IDs mágicos
        $adminRole = Role::where('name', 'admin')->first();
        $clientRole = Role::where('name', 'client')->first();

        // Crear Usuario ADMIN si no existe
        User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('1234'),
                'role_id' => $adminRole->id,
            ]
        );

        // Crear 9 Usuarios CLIENTE de prueba
        User::factory(9)->create(['role_id' => $clientRole->id]);
    }
}
