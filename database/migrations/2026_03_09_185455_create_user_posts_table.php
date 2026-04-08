<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_posts')) {
            Schema::create('user_posts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('post_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamps();
                $table->boolean('is_starred')->default(false);

                $table->unique(['post_id', 'user_id']);
                $table->index(['user_id', 'is_starred']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_posts')) {
            Schema::drop('user_posts');
        }
    }
};