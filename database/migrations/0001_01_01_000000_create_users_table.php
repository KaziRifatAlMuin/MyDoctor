<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('UserID'); // Primary key with custom name
            $table->string('Picture')->nullable(); // Profile picture path
            $table->string('Name');
            $table->date('DateOfBirth')->nullable();
            $table->string('Phone')->nullable();
            $table->string('Email')->unique();
            $table->string('Occupation')->nullable();
            $table->string('BloodGroup')->nullable(); // A+, B+, O+, etc.
            $table->timestamp('CreatedAt')->useCurrent(); // Current timestamp
            
            // Laravel required fields
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            
            // Remove the default timestamps() and use our custom CreatedAt
            // $table->timestamps(); // Comment this out or remove it
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};