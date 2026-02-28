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
        Schema::create('medicine_reminders', function (Blueprint $table) {
            $table->id('ReminderID');
            $table->unsignedBigInteger('ScheduleID');
            $table->dateTime('ReminderDateTime');
            $table->enum('Status', ['pending', 'taken', 'missed', 'skipped'])->default('pending');
            $table->dateTime('TakenAt')->nullable();
            
            $table->foreign('ScheduleID')->references('ScheduleID')->on('medicine_schedules')->onDelete('cascade');
            $table->index('ScheduleID');
            $table->index('ReminderDateTime');
            $table->index('Status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_reminders');
    }
};