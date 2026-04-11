<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_starred_diseases')) {
            Schema::create('user_starred_diseases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('disease_id')->constrained()->onDelete('cascade');
                $table->timestamps();

                $table->unique(['user_id', 'disease_id']);
                $table->index(['disease_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_starred_diseases')) {
            Schema::drop('user_starred_diseases');
        }
    }
};
