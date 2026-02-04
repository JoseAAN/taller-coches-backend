<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdminNavigationItem>
 */
class AdminNavigationItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => $this->faker->word(),
            'icon' => 'lucide-box',
            'route' => '/' . $this->faker->slug(),
            'parent_id' => null,
            'order' => $this->faker->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
