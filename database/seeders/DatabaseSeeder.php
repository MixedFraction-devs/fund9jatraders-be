<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory(10)->create();
        \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'raphael@example.com',
            'password' => 'password',
            'phone_number' => '08106924812',
            'role' => 'admin',
            'address_country' => 'California',
            'address_state' => 'United States',
            'code' => '123456',
        ]);


        \App\Models\ProductOne::factory(20)->create();
        \App\Models\ProductTwo::factory(20)->create();
        \App\Models\ProductThree::factory(20)->create();

        \App\Models\Order::factory(20)->create();
        //Withdrawal factory

        \App\Models\WithdrawalRequest::factory(20)->create();
    }
}
