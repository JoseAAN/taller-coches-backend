<?php

namespace Database\Factories;

use App\Models\Cart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductInvoice>
 */
class ProductInvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_number' => 'PINV-' . $this->faker->unique()->numerify('#####'),
            'total' => $this->faker->randomFloat(2, 10, 1000),
            'cart_id' => Cart::factory(),
        ];
    }
}
