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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visitor_id');
            $table->unsignedBigInteger('officer_id');
            $table->enum('status',['active','cancelled','deactivated','completed'])->default('active');
            $table->time("start_time");
            $table->time("end_time");
            $table->timestamps();

            $table->foreign('visitor_id')->references('id')->on('visitors');
            $table->foreign('officer_id')->references('id')->on('officers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
