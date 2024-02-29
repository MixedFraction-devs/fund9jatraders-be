<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string("phone_number")->nullable();
            $table->string("address_country")->nullable();
            $table->string("address_state")->nullable();
            $table->string("crypto_type")->nullable();
            $table->string("crypto_wallet_address")->nullable();
            $table->string("crypto_network")->nullable();
            $table->foreignId('referrer_id')->nullable()->constrained('users');
            $table->string('password');
            $table->rememberToken();
            $table->string("role")->default("user"); // manager, admin, user
            $table->string("status")->default("active"); // active, inactive, suspended
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
