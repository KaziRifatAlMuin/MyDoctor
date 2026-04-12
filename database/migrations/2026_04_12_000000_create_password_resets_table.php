<?php
// database/migrations/2026_04_12_000000_create_password_resets_table.php
// Laravel 11+ uses password_reset_tokens, but let's ensure it's complete

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Laravel 11+ uses 'password_reset_tokens' table
        // Your existing migration creates this table, but let's verify it has all columns
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};