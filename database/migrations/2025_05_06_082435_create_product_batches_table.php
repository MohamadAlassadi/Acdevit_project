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
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('batch_number');
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date');
            $table->integer('quantity')->default(0);
            $table->decimal('Price', 10, 2);     // 10 أرقام إجمالاً، منها 2 بعد الفاصلة العشرية
            $table->decimal('totalPrice', 12, 2);
            $table->timestamps();
        
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
