<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        'name' => $this->faker->words(3, true),
        'price' => $this->faker->randomFloat(2, 5, 1000),
        'description' => $this->faker->paragraph(3),
        'stock' => $this->faker->numberBetween(0, 100)
        ];
    }
}
