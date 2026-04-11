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
        Schema::create('user_health', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('health_metric_id')->constrained('health_metrics')->cascadeOnDelete();
            $table->json('value');
            $table->dateTime('recorded_at');
            $table->timestamps();

            $table->index(['user_id', 'health_metric_id', 'recorded_at'], 'user_health_lookup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_health');
    }
};
