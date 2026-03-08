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
        Schema::create('environment_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('environment_id')->constrained()->cascadeOnDelete();
            $table->string('metric_type');
            $table->decimal('value', 12, 4);
            $table->string('unit', 50)->nullable();
            $table->timestamps();

            $table->index(['environment_id', 'metric_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('environment_metrics');
    }
};
