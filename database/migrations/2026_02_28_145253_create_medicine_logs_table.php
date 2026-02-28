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
        Schema::create('medicine_logs', function (Blueprint $table) {
            $table->id('LogID');
            $table->unsignedBigInteger('MedicineID');
            $table->unsignedBigInteger('UserID');
            $table->date('Date');
            $table->integer('TotalScheduled')->default(0);
            $table->integer('TotalTaken')->default(0);
            $table->integer('TotalMissed')->default(0);
            
            $table->foreign('MedicineID')->references('MedicineID')->on('medicines')->onDelete('cascade');
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade');
            $table->unique(['MedicineID', 'Date']);
            $table->index(['UserID', 'Date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_logs');
    }
};