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
        Schema::create('product_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id');
            $table->enum('type', ['IN', 'OUT']);
            $table->integer('quantity');
            $table->dateTime('transaction_date');
            $table->string('party')->nullable(); // المورد أو المستفيد
            $table->text('notes')->nullable();
            $table->decimal('price'); //  
            $table->decimal('totalPrice'); // 
            $table->enum('offer_status', ['yes', 'no'])->default('no');
            $table->timestamps();
            $table->foreign('batch_id')->references('id')->on('product_batches')->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_transactions');
    }
};
