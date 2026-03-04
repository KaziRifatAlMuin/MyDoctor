<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->comment('disease | symptom | metric');
            $table->string('key', 255)->comment('English name / identifier');
            $table->string('value', 500)->comment('Bangla translation');
            $table->timestamps();

            $table->unique(['type', 'key']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
