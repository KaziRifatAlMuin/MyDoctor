<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('division_id')->nullable();
            $table->string('division')->nullable();
            $table->string('division_bn')->nullable();
            $table->unsignedSmallInteger('district_id')->nullable();
            $table->string('district')->nullable();
            $table->string('district_bn')->nullable();
            $table->unsignedSmallInteger('upazila_id')->nullable();
            $table->string('upazila')->nullable();
            $table->string('upazila_bn')->nullable();
            $table->string('street')->nullable();
            $table->string('house')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'district']);
            $table->index(['division_id', 'district_id', 'upazila_id']);
        });

        // Backfill a default empty address record for existing users that don't have one yet.
        if (Schema::hasTable('users')) {
            $users = DB::table('users')->select('id')->get();
            $now = now();
            foreach ($users as $user) {
                $exists = DB::table('user_addresses')->where('user_id', $user->id)->exists();
                if (! $exists) {
                    DB::table('user_addresses')->insert([
                        'user_id' => $user->id,
                        'division_id' => null,
                        'division' => null,
                        'division_bn' => null,
                        'district_id' => null,
                        'district' => null,
                        'district_bn' => null,
                        'upazila_id' => null,
                        'upazila' => null,
                        'upazila_bn' => null,
                        'street' => null,
                        'house' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
