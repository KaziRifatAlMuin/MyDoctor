<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_setteings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('email_notifications')->default(true);
            $table->boolean('push_notifications')->default(true);
            $table->boolean('show_personal_info')->default(false);
            $table->boolean('show_diseases')->default(false);
            $table->boolean('show_chatbot')->default(true);
            $table->boolean('show_notification_badge')->default(true);
            $table->boolean('show_mail_badge')->default(true);
            $table->timestamps();

            $table->unique('user_id');
        });

        $hasLegacyColumns = Schema::hasColumn('users', 'email_notifications')
            && Schema::hasColumn('users', 'push_notifications')
            && Schema::hasColumn('users', 'show_personal_info')
            && Schema::hasColumn('users', 'show_diseases');

        if ($hasLegacyColumns) {
            DB::statement('INSERT INTO user_setteings (user_id, email_notifications, push_notifications, show_personal_info, show_diseases, show_chatbot, show_notification_badge, show_mail_badge, created_at, updated_at)
                SELECT id, email_notifications, push_notifications, show_personal_info, show_diseases, 1, 1, 1, NOW(), NOW()
                FROM users');

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn([
                    'email_notifications',
                    'push_notifications',
                    'show_personal_info',
                    'show_diseases',
                ]);
            });
        } else {
            $userIds = DB::table('users')->pluck('id');
            $now = now();
            $rows = [];

            foreach ($userIds as $userId) {
                $rows[] = [
                    'user_id' => $userId,
                    'email_notifications' => true,
                    'push_notifications' => true,
                    'show_personal_info' => false,
                    'show_diseases' => false,
                    'show_chatbot' => true,
                    'show_notification_badge' => true,
                    'show_mail_badge' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($rows !== []) {
                DB::table('user_setteings')->insert($rows);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'email_notifications')) {
                $table->boolean('email_notifications')->default(true);
            }
            if (!Schema::hasColumn('users', 'push_notifications')) {
                $table->boolean('push_notifications')->default(true);
            }
            if (!Schema::hasColumn('users', 'show_personal_info')) {
                $table->boolean('show_personal_info')->default(false);
            }
            if (!Schema::hasColumn('users', 'show_diseases')) {
                $table->boolean('show_diseases')->default(false);
            }
        });

        if (Schema::hasTable('user_setteings')) {
            $rows = DB::table('user_setteings')->get();
            foreach ($rows as $row) {
                DB::table('users')
                    ->where('id', $row->user_id)
                    ->update([
                        'email_notifications' => (bool) $row->email_notifications,
                        'push_notifications' => (bool) $row->push_notifications,
                        'show_personal_info' => (bool) $row->show_personal_info,
                        'show_diseases' => (bool) $row->show_diseases,
                    ]);
            }
        }

        Schema::dropIfExists('user_setteings');
    }
};
