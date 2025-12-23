<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 0. Create Roles
        // We use firstOrCreate to avoid errors if run multiple times (though migrate:fresh clears it)
        $adminRole = \App\Models\Role::firstOrCreate(['name' => 'admin']);
        $clientRole = \App\Models\Role::firstOrCreate(['name' => 'client']);

        // 1. Create ADMIN User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'password' => bcrypt('1234'), 
            'role_id' => $adminRole->id, // Assign ID
        ]);

        // 2. Create 9 CLIENT Users
        User::factory(9)->create([
            'password' => bcrypt('1234'),
            'role_id' => $clientRole->id, // Assign ID
        ]);
    }
}
