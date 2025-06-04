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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id('Account_id'); // Primary Key
            $table->string('User_Name', 50)->unique()->nullable();
            $table->string('Email', 50)->unique();
            $table->string('Password', 255); // لازم يكفي للباسورد المشفر
            $table->string('Address', 50)->nullable();
            $table->string('Phone_Number', 20)->nullable();
            $table->tinyInteger('Ststus')->default(1);
            $table->string('First_Name', 50)->nullable();
            $table->string('Last_Name', 50)->nullable();
            $table->integer('D_Experince_years')->nullable();
            $table->string('D_Partial_certificate', 50)->nullable();
            $table->date('Birth_date')->nullable();
            $table->integer('Role_id')->nullable();
            $table->integer('CreatedBy')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
