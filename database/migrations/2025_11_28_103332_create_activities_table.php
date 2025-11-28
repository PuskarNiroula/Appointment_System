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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->enum('type',['leave','break','appointment']);
            $table->unsignedBigInteger('officer_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status',['active','cancelled'])->default('active');
            $table->timestamps();
            $table->foreign('officer_id')->references('id')->on('officers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
