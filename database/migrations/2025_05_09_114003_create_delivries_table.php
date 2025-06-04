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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id'); // مفتاح أجنبي من جدول accounts
            $table->unsignedBigInteger('client_id'); // مفتاح أجنبي من جدول accounts
            $table->unsignedBigInteger('order_id'); // مفتاح أجنبي من جدول carts
            $table->string('adress'); // المورد أو المستفيد
            $table->string('status'); // المورد أو المستفيد
            $table->integer('expected_hours')->default(24);
            $table->foreign('client_id')->references('Account_id')->on('accounts');
            $table->foreign('driver_id')->references('Account_id')->on('accounts');
            $table->foreign('order_id')->references('id')->on('orders');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivries');
    }
};
