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
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Actor who performed the activity; null means system action.');
            $table->string('category', 40)
                ->index()
                ->comment('Feature area: auth, admin, community, health, medicine, etc.');
            $table->string('action', 80)
                ->index()
                ->comment('Action keyword: login, logout, model_updated, request_post, etc.');
            $table->string('description', 255)
                ->nullable()
                ->comment('Human-readable sentence shown in the activity feed.');
            $table->string('subject_type', 120)
                ->nullable()
                ->index()
                ->comment('Related model class for deep links, e.g. App\\Models\\Post.');
            $table->unsignedBigInteger('subject_id')
                ->nullable()
                ->index()
                ->comment('Primary key of related subject model.');
            $table->json('context')
                ->nullable()
                ->comment('Small structured metadata: changed_fields, route_name, status, etc.');
            $table->timestamp('created_at')
                ->useCurrent()
                ->index()
                ->comment('When the activity happened.');

            $table->index(['category', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
