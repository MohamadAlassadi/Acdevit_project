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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id'); //   
            $table->unsignedBigInteger('driver_id'); // 
            $table->unsignedBigInteger('order_id'); // 
            $table->decimal('orderPrice');  
            $table->decimal('deliveryPrice'); //  
            $table->decimal('totalPrice'); // 
            $table->integer('discount_status')->default(0);
            $table->decimal('discount_amount')->nullable();
            $table->string('discount_type')->nullable();
            $table->foreign('client_id')->references('Account_id')->on('accounts');
            $table->foreign('driver_id')->references('Account_id')->on('accounts');
            $table->foreign('order_id')->references('id')->on('orders');

            $table->string('coupon_status')->default('no'); //   
            $table->string('payment_status'); //   
            $table->string('payment_method'); //   

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
