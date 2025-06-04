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
        Schema::create('shopping_cart_items', function (Blueprint $table) {
            $table->id('ShoppingCarItem_id'); // المفتاح الأساسي
            $table->unsignedBigInteger('CartID'); // مفتاح أجنبي من جدول carts
            $table->unsignedBigInteger('Product_id'); // مفتاح أجنبي من جدول المنتجات (Products)
            $table->integer('Quantity'); // الكمية
            $table->integer('UnitPrice'); // السعر الفردي
            $table->integer('TotalPrice'); // السعر الإجمالي
            $table->timestamps(); // لتسجيل تاريخ الإنشاء والتحديث
    
            // ربط CartID بـ Cart_id في جدول carts
            $table->foreign('CartID')->references('id')->on('carts');
    
            // ربط Product_id بـ Product_id في جدول المنتجات (افترض أن لديك جدول منتجات)
            $table->foreign('Product_id')->references('id')->on('products');
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopping_cart_items');
    }
};
