<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['prescription', 'report'])->default('prescription');
            $table->string('file_path');
            $table->text('summary')->nullable();
            $table->text('notes')->nullable();
            $table->string('doctor_name')->nullable();
            $table->string('institution')->nullable();
            $table->date('document_date')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uploads');
    }
};
