<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WithdrawalRequest>
 */
class WithdrawalRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    //  $table->id();
    //  $table->string("crypto_type")->nullable();
    //  $table->string("crypto_wallet_address")->nullable();
    //  $table->string("crypto_network")->nullable();
    //  $table->integer('amount')->nullable();
    //  $table->integer("affiliate_amount")->nullable();
    //  $table->string('status')->default('pending');
    //  $table->string('reason')->nullable();
    //  $table->foreignId('user_id')->constrained()->onDelete('cascade');
    //  //admin id on users table
    //  $table->integer('admin_id')->nullable();
    //  $table->timestamps();
    //  $table->softDeletes();
    public function definition(): array
    {
        return [
            'crypto_type' => $this->faker->randomElement(['BTC', 'ETH', 'LTC']),
            'crypto_wallet_address' => $this->faker->uuid(),
            'crypto_network' => $this->faker->randomElement(['BTC', 'ETH', 'LTC']),
            'amount' => $this->faker->randomNumber(4),
            'affiliate_amount' => $this->faker->randomNumber(4),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'reason' => $this->faker->sentence(),
            'user_id' => \App\Models\User::factory(),
            'admin_id' => 1,
        ];
    }
}
