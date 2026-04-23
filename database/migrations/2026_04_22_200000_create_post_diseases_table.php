<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_diseases', function (Blueprint $table) {
            $table->id();

            // FK → posts.id
            $table->foreignId('post_id')
                  ->constrained('posts')
                  ->cascadeOnDelete();

            // FK → diseases.id
            $table->foreignId('disease_id')
                  ->constrained('diseases')
                  ->cascadeOnDelete();

            $table->timestamps();

            // prevent duplicate pairs
            $table->unique(['post_id', 'disease_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_diseases');
    }
};