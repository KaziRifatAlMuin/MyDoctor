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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id('MedicineID');
            $table->unsignedBigInteger('UserID');
            $table->string('MedicineName');
            $table->enum('Type', ['tablet', 'capsule', 'syrup', 'injection', 'drops', 'cream', 'inhaler', 'other'])->default('tablet');
            $table->decimal('ValuePerDose', 8, 2);
            $table->enum('Unit', ['mg', 'ml', 'mcg', 'g', 'IU', 'tablet', 'capsule', 'drop', 'puff'])->default('mg');
            $table->enum('Rule', ['before_food', 'after_food', 'with_food', 'before_sleep', 'anytime'])->default('anytime');
            $table->integer('DoseLimit')->nullable()->comment('Maximum doses per day, NULL for no limit');
            $table->timestamp('CreatedAt')->useCurrent();
            
            $table->foreign('UserID')->references('UserID')->on('users')->onDelete('cascade');
            $table->index('UserID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};