<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'product_type' => $this->faker->randomElement(['ONE', 'TWO', 'THREE']),
            'cost' => $this->faker->randomNumber(4),
            'phase' => $this->faker->randomElement([1, 2, 3, 4]),
            'breached_at' => $this->faker->randomElement([null, $this->faker->dateTimeThisYear()])
        ];
    }
}
