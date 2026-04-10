<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_symptoms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('symptom_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('severity_level')->nullable();
            $table->text('note')->nullable();
            $table->dateTime('recorded_at');
            $table->timestamps();

            $table->index(['user_id', 'recorded_at']);
            $table->index('symptom_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_symptoms');
    }
};
