<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->string("crypto_type")->nullable();
            $table->string("crypto_wallet_address")->nullable();
            $table->string("crypto_network")->nullable();
            $table->integer('amount')->nullable();
            $table->integer("affiliate_amount")->nullable();
            $table->string('status')->default('pending');
            $table->string('reason')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            //admin id on users table
            $table->integer('admin_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
