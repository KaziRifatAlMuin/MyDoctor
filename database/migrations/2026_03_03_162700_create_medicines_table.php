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
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('medicine_name');
            $table->string('type')->nullable();
            $table->decimal('value_per_dose', 10, 2)->nullable();
            $table->string('unit', 50)->nullable();
            $table->string('rule')->nullable();
            $table->unsignedInteger('dose_limit')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'medicine_name']);
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
