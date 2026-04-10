<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mailings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->longText('message');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->enum('status', ['draft', 'sent', 'unread', 'read', 'archived'])->default('draft');
            $table->timestamps();

            $table->index(['receiver_id', 'status']);
            $table->index(['sender_id', 'status']);
            $table->index(['receiver_id', 'is_starred']);
            $table->index(['sender_id', 'is_starred']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mailings');
    }
};
