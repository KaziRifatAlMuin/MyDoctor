<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('category', 64)->index();
            $table->string('action', 120)->index();
            $table->text('description')->nullable();
            $table->string('method', 10)->nullable();
            $table->string('route_name', 191)->nullable()->index();
            $table->text('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('subject_type', 191)->nullable()->index();
            $table->unsignedBigInteger('subject_id')->nullable()->index();
            $table->string('event', 40)->nullable()->index();
            $table->json('changes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['category', 'updated_at']);
            $table->index(['user_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
