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
        // Check if old medicine_schedules table exists and drop it
        if (Schema::hasTable('medicine_schedules')) {
            Schema::drop('medicine_schedules');
        }

        Schema::create('medicine_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('dosage_period_days')->nullable();
            $table->unsignedInteger('frequency_per_day')->nullable();
            $table->unsignedInteger('interval_hours')->nullable();
            $table->string('dosage_time_binary')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['medicine_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_schedules');
    }
};