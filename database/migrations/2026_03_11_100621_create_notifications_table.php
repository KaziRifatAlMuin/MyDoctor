<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('from_user_id')->index();
            $table->string('type'); // 'like', 'comment', 'mention'
            $table->string('notifiable_type'); // 'App\Models\Post', 'App\Models\Comment'
            $table->unsignedBigInteger('notifiable_id');
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('from_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'type', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};