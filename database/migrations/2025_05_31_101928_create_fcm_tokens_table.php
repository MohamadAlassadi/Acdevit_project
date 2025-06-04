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
    Schema::create('fcm_tokens', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('account_id')->unique();
        $table->string('token');
        $table->timestamps();

        $table->foreign('account_id')->references('Account_id')->on('accounts')->onDelete('cascade');
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcm_tokens');
    }
};
