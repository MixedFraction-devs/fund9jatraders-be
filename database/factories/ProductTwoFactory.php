<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductTwo>
 */
class ProductTwoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_number' => $this->faker->randomDigitNotZero() * 100000000,
            'traders_password' => $this->faker->word(),
            'server' => 'Exness-trial-10',
            'leverage' => '1:1000',
            'mode' => $this->faker->randomElement(['demo', 'real', 'fresh']),
            'purchased_at' => $this->faker->randomElement([$this->faker->dateTimeBetween('-1 years', 'now'), null]),
        ];
    }
}
