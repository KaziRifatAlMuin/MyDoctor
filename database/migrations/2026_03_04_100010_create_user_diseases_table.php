<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_diseases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('disease_id')->constrained()->cascadeOnDelete();
            $table->date('diagnosed_at')->nullable();
            $table->enum('status', ['active', 'recovered', 'chronic', 'managed'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'disease_id']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_diseases');
    }
};
