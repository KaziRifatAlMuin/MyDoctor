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
            $table->id();
            $table->foreignId('schedule_id')->constrained('medicine_schedules')->cascadeOnDelete();
            $table->dateTime('reminder_at');
            $table->string('status')->default('pending');
            $table->dateTime('taken_at')->nullable();
            $table->timestamps();

            $table->index(['schedule_id', 'reminder_at']);
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
