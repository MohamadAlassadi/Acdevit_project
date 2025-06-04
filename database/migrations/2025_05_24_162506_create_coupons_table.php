<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id');  // اللي أصدر الكوبون
            $table->unsignedBigInteger('dest_id');    // المستفيد من الكوبون
            $table->string('code')->unique();
            $table->enum('type', ['order', 'delivery', 'invoice'])->default('order');
            $table->integer('discount_percent')->nullable();       // نسبة الخصم (مثلاً 10 لـ 10%)
            $table->string('status')->default('active');           // حالة الكوبون (active, inactive)
            $table->string('payment_status')->nullable();          // حالة الدفع للكوبون (مثلاً paid, unpaid)
            $table->text('content')->nullable();                    // وصف الكوبون أو ملاحظات
            $table->date('expiry_date')->nullable();                // انتهاء صلاحية الكوبون
            $table->timestamp('date_added')->useCurrent();
            $table->unsignedBigInteger('payment_discount_id')->nullable();
            $table->foreign('payment_discount_id')->references('id')->on('payment_discounts');
            $table->timestamps();

            $table->foreign('source_id')->references('Account_id')->on('accounts')->onDelete('cascade');
            $table->foreign('dest_id')->references('Account_id')->on('accounts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
