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
        Schema::create('complains', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id');  // اللي أصدر الكوبون
            $table->unsignedBigInteger('dest_id');  
            $table->string('content');
            $table->timestamp('date_added')->useCurrent();
                        $table->foreign('source_id')->references('Account_id')->on('accounts')->onDelete('cascade');
            $table->foreign('dest_id')->references('Account_id')->on('accounts')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complains');
    }
};
