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
        Schema::create('articles', function (Blueprint $table) {
            $table->id('Article_id');
            $table->string('Title');
            $table->text('Content');
            $table->string('Image')->nullable(); // مسار الصورة
            $table->unsignedBigInteger('CreatedBy')->nullable(); // الكاتب
            $table->timestamps();
    
            $table->foreign('CreatedBy')->references('Account_id')->on('accounts')->onDelete('set null');
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
