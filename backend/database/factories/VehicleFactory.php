<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'license_plate' => $this->faker->regexify('[A-Z]{2}[0-9]{5}[A-Z]{3}'),
            'vehicle_type_id' => \App\Models\VehicleType::pluck('id')->random(), // Asumiendo que hay 5 tipos de vehículos
            'user_id' => $this->faker->numberBetween(1, 10), // Asumiendo que hay 10 usuarios
            'color' => $this->faker->safeColorName(),
            'model' => $this->faker->word(),
            'brand' => $this->faker->company(),
        ];
    }
}
