<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cart>
 */
class CartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::inRandomOrder()->first()?->id,
            //IMPORTANTE: Aquí le puse un precio random solamente para hacer las pruebas, en realidad tendrá una lógica detrás
            'price' => $this->faker->randomFloat(2, 10, 500),
        ];
    }
}
