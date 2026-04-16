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

        $realClients = [
            ['name' => 'Carlos López', 'email' => 'carlos@ejemplo.com', 'password' => bcrypt('client1234')],
            ['name' => 'Laura Martínez', 'email' => 'laura@ejemplo.com', 'password' => bcrypt('client1234')],
            ['name' => 'Javier Sánchez', 'email' => 'javier.sanchez@ejemplo.com', 'password' => bcrypt('client1234')],
            ['name' => 'Marta Gómez', 'email' => 'marta.detailing@ejemplo.com', 'password' => bcrypt('client1234')],
            ['name' => 'Pedro (Taxista)', 'email' => 'pedro.taxi@ejemplo.com', 'password' => bcrypt('client1234')],
            ['name' => 'Ana Ruiz', 'email' => 'ana.ruiz@ejemplo.com', 'password' => bcrypt('client1234')],
            ['name' => 'Flotas Paco S.L.', 'email' => 'info@flotaspaco.com', 'password' => bcrypt('1234')],
        ];

        foreach ($realClients as $client) {
            User::firstOrCreate(
                ['email' => $client['email']],
                [
                    'name' => $client['name'],
                    'password' => $client['password'],
                    'role_id' => $clientRole->id,
                ]
            );
        }
    }
}
