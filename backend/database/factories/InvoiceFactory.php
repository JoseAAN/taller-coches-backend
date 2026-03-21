<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'invoice_number' => 'INV-' . $this->faker->unique()->numberBetween(10000, 99999),
            'total' => $this->faker->randomFloat(2, 20, 500),
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory()->create(['role_id' => 2])->id,
            'cart_id' => null
        ];
    }

    /**
     * State for cart-based invoices.
     */
    public function fromCart(): static
    {
        return $this->state(fn (array $attributes) => [
            'cart_id' => Cart::factory(),
        ]);
    }

}
