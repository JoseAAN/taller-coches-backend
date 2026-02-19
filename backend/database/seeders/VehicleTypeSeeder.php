<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\VehicleType;
class VehicleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicleTypes = ['Turismo', 'SUV', 'Camioneta', 'Motocicleta', 'Furgoneta'];

        foreach ($vehicleTypes as $type) {
            VehicleType::firstOrCreate(['name' => $type, 'dimensions' => fake()->randomElement(['Pequeño', 'Mediano', 'Grande'])]);
        }

        VehicleType::factory()->count(5)->create();
    }
}
