<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehiclesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = \App\Models\User::whereHas('role', function($q) {
            $q->where('name', 'client');
        })->get();

        if ($users->count() < 7) {
            Vehicle::factory()->count(10)->create();
            return;
        }

        $realVehicles = [
            // Carlos
            ['user_id' => $users[0]->id, 'brand' => 'Volkswagen', 'model' => 'Golf GTI', 'color' => 'Blanco', 'license_plate' => '1234KBC', 'vehicle_type_id' => 1],
            // Laura
            ['user_id' => $users[1]->id, 'brand' => 'Seat', 'model' => 'Ibiza', 'color' => 'Rojo', 'license_plate' => '5678JDF', 'vehicle_type_id' => 1],
            // Javier
            ['user_id' => $users[2]->id, 'brand' => 'BMW', 'model' => 'Serie 3', 'color' => 'Negro', 'license_plate' => '9012LMN', 'vehicle_type_id' => 1],
            // Marta
            ['user_id' => $users[3]->id, 'brand' => 'Audi', 'model' => 'Q5', 'color' => 'Gris', 'license_plate' => '3456MDR', 'vehicle_type_id' => 2], 
            // Pedro Taxista (2 coches)
            ['user_id' => $users[4]->id, 'brand' => 'Toyota', 'model' => 'Prius', 'color' => 'Blanco', 'license_plate' => '7890LPT', 'vehicle_type_id' => 1],
            ['user_id' => $users[4]->id, 'brand' => 'Skoda', 'model' => 'Octavia', 'color' => 'Blanco', 'license_plate' => '4321KZT', 'vehicle_type_id' => 1],
            // Ana
            ['user_id' => $users[5]->id, 'brand' => 'Fiat', 'model' => '500', 'color' => 'Amarillo', 'license_plate' => '8765HYB', 'vehicle_type_id' => 1],
            // Flotas Paco (Furgonetas)
            ['user_id' => $users[6]->id, 'brand' => 'Renault', 'model' => 'Kangoo', 'color' => 'Blanco', 'license_plate' => '1111MBB', 'vehicle_type_id' => 3], 
            ['user_id' => $users[6]->id, 'brand' => 'Ford', 'model' => 'Transit', 'color' => 'Blanco', 'license_plate' => '2222LCC', 'vehicle_type_id' => 3],
        ];

        foreach ($realVehicles as $v) {
            $typeExists = \App\Models\VehicleType::find($v['vehicle_type_id']);
            if (!$typeExists) {
                $v['vehicle_type_id'] = \App\Models\VehicleType::first()->id ?? 1;
            }

            Vehicle::create($v);
        }
    }
}
