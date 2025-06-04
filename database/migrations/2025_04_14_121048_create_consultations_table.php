<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsultationsTable extends Migration
{
    public function up()
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('Doctor_id');
            $table->unsignedBigInteger('Client_id');
            $table->date('Consulation_date');
            $table->string('type');
            $table->integer('age');
            $table->integer('weight');
            $table->text('description')->nullable();
            $table->text('prev_illness')->nullable();
            $table->string('image')->nullable();
            $table->date('Follow_update')->nullable();
            $table->text('doctor_replay')->nullable();
            $table->timestamps();

                 // ربط Doctor_id بـ Account_id في جدول الحسابات (accounts)
                 $table->foreign('Doctor_id')->references('Account_id')->on('accounts');
    
                 // ربط Client_id بـ Account_id في جدول الحسابات (accounts)
                 $table->foreign('Client_id')->references('Account_id')->on('accounts');
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultations');
    }
}
