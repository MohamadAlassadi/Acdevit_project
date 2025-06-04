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
                Schema::create('products', function (Blueprint $table) {
                    $table->id(); // ID المنتج
                    $table->unsignedBigInteger('CreatedBy')->nullable(); // رقم حساب المنشئ
                    $table->foreign('CreatedBy')->references('Account_id')->on('accounts')->onDelete('set null');
                    $table->integer('status')->default(1);
                    $table->string('Name');
                    $table->text('Discription')->nullable(); // وصف المنتج
                    $table->decimal('Price', 10, 2);
                    $table->integer('Stock')->default(0);
                    $table->integer('offer_status')->default(0);
                    $table->integer('offer_price')->nullable();
                    $table->timestamps(); // created_at + updated_at
                });
            }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
