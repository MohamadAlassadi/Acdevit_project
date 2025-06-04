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
        Schema::create('payment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('bank_name')->nullable();
            $table->string('credit_number')->nullable();
            $table->string('cvv')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('syriatel_cash')->nullable();
            $table->decimal('balance', 10, 2);
            $table->string('method'); // مثال: 'card', 'syriatel'
            $table->timestamps();

            $table->foreign('user_id')->references('Account_id')->on('accounts');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment');
    }
};
