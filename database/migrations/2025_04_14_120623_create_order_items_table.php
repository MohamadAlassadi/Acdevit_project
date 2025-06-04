<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id('OrderItem_id'); // المفتاح الأساسي
            $table->unsignedBigInteger('OrderID'); // مفتاح أجنبي من جدول orders
            $table->unsignedBigInteger('Product_id'); // مفتاح أجنبي من جدول المنتجات (Products)
            $table->integer('Quantity'); // الكمية
            $table->decimal('UnitPrice'); // السعر الفردي
            $table->decimal('TotalPrice'); // السعر الإجمالي
            $table->timestamps(); // لتسجيل تاريخ الإنشاء والتحديث
    
            // ربط OrderID بـ Order_id في جدول orders
            $table->foreign('OrderID')->references('id')->on('orders');
    
            // ربط Product_id بـ Product_id في جدول المنتجات
            $table->foreign('Product_id')->references('id')->on('products');
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
