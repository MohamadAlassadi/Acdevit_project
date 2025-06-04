<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('createdBy');  // اللي أصدر الكوبون
            $table->string('title'); // اسم العرض
            $table->text('description')->nullable(); // وصف العرض
            $table->enum('type', ['order', 'delivery', 'invoice'])->default('order');
            $table->integer('discount_percent'); // نسبة الخصم
            $table->integer('valid_days')->default(30); // عدد الأيام التي يبقى فيها الكود صالحًا بعد الشراء
            $table->decimal('price', 10, 2); // السعر الذي يدفعه المستخدم للحصول على هذا العرض
            $table->enum('status', ['active', 'inactive'])->default('active'); // لتفعيل/تعطيل العرض
            $table->foreign('createdBy')->references('Account_id')->on('accounts')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_discounts');
    }
};
