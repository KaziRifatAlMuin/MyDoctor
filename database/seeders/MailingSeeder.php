<?php

namespace Database\Seeders;

use App\Models\Mailing;
use App\Models\User;
use Illuminate\Database\Seeder;

class MailingSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('id')->all();

        if (count($userIds) < 2) {
            return;
        }

        // Create sample Gmail-like mailbox data between existing users.
        for ($i = 0; $i < 45; $i++) {
            $senderId = $userIds[array_rand($userIds)];
            $receiverId = $userIds[array_rand($userIds)];

            if ($senderId === $receiverId) {
                $i--;
                continue;
            }

            $createdAt = now()->subDays(rand(0, 30))->subMinutes(rand(0, 1440));
            $status = fake()->randomElement(['unread', 'read', 'archived', 'sent']);
            $isRead = in_array($status, ['read', 'archived', 'sent'], true);
            $isStarred = fake()->boolean(20);

            Mailing::create([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'title' => fake()->sentence(6),
                'message' => fake()->paragraphs(rand(1, 3), true),
                'status' => $status,
                'is_read' => $isRead,
                'is_starred' => $isStarred,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        // Create sample drafts for random users.
        for ($i = 0; $i < 12; $i++) {
            $senderId = $userIds[array_rand($userIds)];
            $createdAt = now()->subDays(rand(0, 20))->subMinutes(rand(0, 1440));

            Mailing::create([
                'sender_id' => $senderId,
                'receiver_id' => null,
                'title' => fake()->sentence(5),
                'message' => fake()->paragraphs(rand(1, 2), true),
                'status' => 'draft',
                'is_read' => false,
                'is_starred' => fake()->boolean(10),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
