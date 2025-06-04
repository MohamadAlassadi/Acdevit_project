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
    Schema::create('orders', function (Blueprint $table) {
        $table->id(); // مفتاح أساسي للطلب
        $table->unsignedBigInteger('Client_id'); // مفتاح أجنبي من جدول accounts
        $table->unsignedBigInteger('Cart_id'); // مفتاح أجنبي من جدول carts
        $table->date('Date_added'); // تاريخ إضافة الطلب
        $table->string('Order_status')->default('تم استلام طلبك'); // حالة الطلب
        $table->integer('IsCheckedOut'); // حالة الدفع (مثلاً: 0 = لم يتم الدفع، 1 = تم الدفع)
        $table->timestamps(); // لتسجيل تاريخ الإنشاء والتحديث

        // ربط Client_id بحقل Account_id في جدول accounts
        $table->foreign('Client_id')->references('Account_id')->on('accounts');

        // ربط Cart_id بحقل Cart_id في جدول carts
        $table->foreign('Cart_id')->references('id')->on('carts');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
