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
        Schema::create('medicine_schedules', function (Blueprint $table) {
            $table->id('ScheduleID');
            $table->unsignedBigInteger('MedicineID');
            $table->integer('DosagePeriodDays')->comment('1=daily, 7=weekly, 30=monthly, 0=as needed');
            $table->integer('FrequencyPerDay')->comment('Number of times per day');
            $table->integer('IntervalHours')->nullable()->comment('Hours between doses');
            $table->string('DosageTimeBinary', 48)->comment('Binary representation of times (48 bits = 24 hours * 2 for half-hour)');
            $table->date('StartDate');
            $table->date('EndDate')->nullable();
            $table->boolean('IsActive')->default(true);
            
            $table->foreign('MedicineID')->references('MedicineID')->on('medicines')->onDelete('cascade');
            $table->index('MedicineID');
            $table->index('IsActive');
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